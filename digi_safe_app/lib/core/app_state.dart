import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:app_links/app_links.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/digisafe_models.dart';
import 'api_client.dart';
import 'strings.dart';

class AppState extends ChangeNotifier {
  static const _tokenKey = 'digisafe_api_token';
  static const _userKey = 'digisafe_user_json';
  static const _localeKey = 'digisafe_locale';
  static const _themeKey = 'digisafe_theme';
  static const _onboardingDonePrefix = 'digisafe_onboarding_done_';

  static const MethodChannel _oauthChannel = MethodChannel('id.web.digisafepurwakarta/oauth');

  final ApiClient api = ApiClient();
  final AppLinks _appLinks = AppLinks();
  StreamSubscription<Uri>? _linkSub;
  Timer? _unreadTimer;

  Completer<Uri?>? _pendingOAuthCompleter;

  bool initialized = false;
  int currentTabIndex = 0; // 0: Beranda, 1: Edukasi, 2: Minigame, 3: Forum, 4: Profil
  int contentVersion = 0;

  String locale = 'id';
  String theme = 'light';
  UserProfile? user;
  String? oauthStatusMessage;

  // Notifikasi chat & Bot Digika
  int unreadChatBadge = 0;
  Map<String, dynamic>? latestChatAlert;
  int _lastSeenAlertMsgId = -1;

  // Warm-loaded cache untuk transisi Splash -> Beranda instan
  Map<String, dynamic>? warmBerandaJson;

  bool get isDark => theme == 'dark';
  bool get isEn => locale == 'en';
  bool get isAuthenticated => user != null && (api.token?.isNotEmpty ?? false);

  String tr(String key, [Map<String, String> params = const {}]) => S.t(key, isEn, params);

  void setTab(int idx) {
    if (idx == currentTabIndex) {
      bumpContent();
      return;
    }
    currentTabIndex = idx;
    notifyListeners();
  }

  void bumpContent() {
    contentVersion++;
    notifyListeners();
  }

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    locale = prefs.getString(_localeKey) ?? 'id';
    theme = prefs.getString(_themeKey) ?? 'light';
    final savedToken = prefs.getString(_tokenKey);
    final savedUserJson = prefs.getString(_userKey);

    api.locale = locale;
    api.token = savedToken;

    if (savedUserJson != null && savedToken != null) {
      try {
        user = UserProfile.fromJson(jsonDecode(savedUserJson) as Map<String, dynamic>);
      } catch (_) {
        user = null;
      }
    }

    initialized = true;
    notifyListeners();

    unawaited(_warmPreload());
    if (savedToken != null && savedToken.isNotEmpty) {
      unawaited(refreshMe());
      unawaited(refreshUnreadCount());
    }

    _setupOAuthListeners();
    _startUnreadPolling();
  }

  Future<void> _warmPreload() async {
    try {
      warmBerandaJson = await api.get('/beranda');
      notifyListeners();
    } catch (_) {}
  }

  void _setupOAuthListeners() {
    if (kIsWeb) return;

    _oauthChannel.setMethodCallHandler((call) async {
      if (call.method == 'onOAuthCallback' && call.arguments is String) {
        final uri = Uri.tryParse(call.arguments as String);
        if (uri != null) {
          _deliverOAuthCallback(uri);
        }
      }
    });

    try {
      _oauthChannel.invokeMethod<String>('consumeInitialCallback').then((raw) {
        if (raw != null && raw.isNotEmpty) {
          final uri = Uri.tryParse(raw);
          if (uri != null) {
            _deliverOAuthCallback(uri);
          }
        }
      }).catchError((_) {});
    } catch (_) {}

    _appLinks.getInitialLink().then((uri) {
      if (uri != null) {
        _deliverOAuthCallback(uri);
      }
    }).catchError((_) {});

    _linkSub = _appLinks.uriLinkStream.listen(_deliverOAuthCallback, onError: (_) {});
  }

  void _deliverOAuthCallback(Uri uri) {
    if (uri.scheme != 'digisafe') return;

    if (_pendingOAuthCompleter != null && !_pendingOAuthCompleter!.isCompleted) {
      _pendingOAuthCompleter!.complete(uri);
    } else {
      unawaited(_processOAuthCallbackUri(uri));
    }
  }

  void _startUnreadPolling() {
    _unreadTimer?.cancel();
    _unreadTimer = Timer.periodic(const Duration(seconds: 12), (_) {
      if (isAuthenticated) {
        unawaited(refreshUnreadCount());
      }
    });
  }

  Future<void> refreshUnreadCount() async {
    if (!isAuthenticated) {
      unreadChatBadge = 0;
      latestChatAlert = null;
      return;
    }
    try {
      final res = await api.getOptional('/chat/unread-count');
      if (res == null) return;
      final total = (res['total_badge'] as num?)?.toInt() ?? 0;
      final alert = res['latest_alert'] is Map<String, dynamic>
          ? Map<String, dynamic>.from(res['latest_alert'] as Map)
          : null;

      bool changed = total != unreadChatBadge;
      if (alert != null) {
        final msgId = (alert['message_id'] as num?)?.toInt() ?? 0;
        if (msgId > 0 && msgId != _lastSeenAlertMsgId) {
          _lastSeenAlertMsgId = msgId;
          latestChatAlert = alert;
          changed = true;
        }
      }
      unreadChatBadge = total;
      if (changed) notifyListeners();
    } catch (_) {}
  }

  void consumeLatestAlert() {
    latestChatAlert = null;
  }

  Future<({bool ok, bool needsOnboarding})> _processOAuthCallbackUri(Uri uri) async {
    final err = uri.queryParameters['error'];
    final token = uri.queryParameters['token'];
    final isNewParam = uri.queryParameters['is_new'] == '1';

    if (err != null && err.isNotEmpty) {
      oauthStatusMessage = err;
      notifyListeners();
      return (ok: false, needsOnboarding: false);
    }

    if (token != null && token.isNotEmpty) {
      await completeTokenLogin(token);
      final doneLocally = await hasCompletedOnboarding();
      final missingProfile = user != null && (user!.specialtyRole == null || user!.specialtyRole!.isEmpty);
      final needsOnboarding = isNewParam || (!doneLocally && missingProfile);
      return (ok: true, needsOnboarding: needsOnboarding);
    }
    return (ok: false, needsOnboarding: false);
  }

  void cancelPendingGoogleSignInIfIdle() {
    if (_pendingOAuthCompleter != null && !_pendingOAuthCompleter!.isCompleted) {
      Future.delayed(const Duration(milliseconds: 900), () {
        if (_pendingOAuthCompleter != null && !_pendingOAuthCompleter!.isCompleted) {
          _pendingOAuthCompleter!.complete(null);
        }
      });
    }
  }

  void clearOauthStatus() {
    oauthStatusMessage = null;
  }

  /// Login Google OAuth 2.0 menggunakan Android Partial Bottom-Sheet Pop-Up Custom Tab
  /// (menggunakan database & akun Google yang sama persis dengan website, tanpa melempar user keluar ke browser).
  Future<({bool ok, bool needsOnboarding})> launchGoogleSignIn() async {
    final completer = Completer<Uri?>();
    _pendingOAuthCompleter = completer;

    final authUrl = '${ApiClient.baseHost}/auth/google/redirect?mode=mobile';
    await _oauthChannel.invokeMethod('openPopupAuth', {'url': authUrl});

    final uri = await completer.future.timeout(
      const Duration(minutes: 4),
      onTimeout: () => null,
    );
    _pendingOAuthCompleter = null;

    if (uri == null) {
      return (ok: false, needsOnboarding: false);
    }
    return await _processOAuthCallbackUri(uri);
  }

  Future<void> completeTokenLogin(String token) async {
    api.token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
    await refreshMe();
    unawaited(refreshUnreadCount());
    oauthStatusMessage = isEn
        ? 'Signed in with Google as ${user?.namaLengkap ?? 'Citizen'}!'
        : 'Berhasil masuk dengan Google sebagai ${user?.namaLengkap ?? 'Warga Siber'}!';
    notifyListeners();
  }

  Future<bool> hasCompletedOnboarding() async {
    if (user == null) return true;
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool('$_onboardingDonePrefix${user!.id}') ?? false;
  }

  Future<void> markOnboardingCompleted() async {
    if (user == null) return;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('$_onboardingDonePrefix${user!.id}', true);
  }

  Future<void> refreshMe() async {
    if (api.token == null || api.token!.isEmpty) return;
    try {
      final res = await api.get('/auth/me');
      if (res['user'] is Map<String, dynamic>) {
        await _persistUser(UserProfile.fromJson(res['user'] as Map<String, dynamic>), api.token!);
      }
    } on ApiException catch (e) {
      if (e.statusCode == 401) {
        await clearSession();
      }
    } catch (_) {}
  }

  Future<({bool needsOnboarding})> login(String email, String password) async {
    final res = await api.post('/auth/login', {
      'email': email.trim(),
      'password': password,
      'device_name': 'flutter_android',
    });
    final token = (res['token'] ?? '').toString();
    final u = UserProfile.fromJson(res['user'] as Map<String, dynamic>);
    await _persistUser(u, token);
    unawaited(refreshUnreadCount());

    final doneLocally = await hasCompletedOnboarding();
    final missingRole = u.specialtyRole == null || u.specialtyRole!.isEmpty;
    return (needsOnboarding: !doneLocally && missingRole);
  }

  /// Kirim kode OTP 6 digit ke email pendaftar sebelum pembuatan akun APK.
  Future<Map<String, dynamic>> sendRegisterOtp({required String email}) async {
    return await api.post('/auth/register/send-otp', {
      'email': email.trim(),
      'locale': locale,
    });
  }

  /// Register dari halaman Login/Daftar APK memerlukan Gmail/Email + Password + OTP 6 digit.
  /// Username sementara dibuat aman lalu langsung disempurnakan di Onboarding Wizard berikutnya.
  Future<void> registerEmailOnly({
    required String email,
    required String password,
    required String otp,
  }) async {
    final cleanEmail = email.trim();
    final localPart = cleanEmail.contains('@') ? cleanEmail.split('@').first : cleanEmail;
    final safeBase = localPart.replaceAll(RegExp(r'[^a-zA-Z0-9_]'), '_').toLowerCase();
    final trimmed = safeBase.isEmpty ? 'warga' : (safeBase.length > 26 ? safeBase.substring(0, 26) : safeBase);
    final randSuffix = (DateTime.now().millisecondsSinceEpoch % 10000).toString();
    final tempUsername = '${trimmed}_$randSuffix';
    final tempNick = trimmed.replaceAll('_', ' ');
    final niceNick = tempNick.isEmpty ? 'Warga Siber' : '${tempNick[0].toUpperCase()}${tempNick.substring(1)}';

    final res = await api.post('/auth/register', {
      'nama_lengkap': niceNick,
      'username': tempUsername,
      'email': cleanEmail,
      'otp': otp.trim(),
      'password': password,
      'device_name': 'flutter_android',
    });
    final token = (res['token'] ?? '').toString();
    final u = UserProfile.fromJson(res['user'] as Map<String, dynamic>);
    await _persistUser(u, token);
    unawaited(refreshUnreadCount());
  }

  Future<void> updateProfile({
    required String namaLengkap,
    String? username,
    required String bio,
    String? specialtyRole,
    String? password,
    File? avatarFile,
    File? bannerFile,
  }) async {
    final fields = <String, String>{
      'nama_lengkap': namaLengkap.trim(),
      'bio': bio.trim(),
    };
    if (username != null && username.trim().isNotEmpty) {
      fields['username'] = username.trim();
    }
    if (specialtyRole != null && specialtyRole.trim().isNotEmpty) {
      fields['specialty_role'] = specialtyRole.trim();
    }
    if (password != null && password.trim().isNotEmpty) {
      fields['password'] = password;
    }

    final res = await api.postMultipart(
      '/profile',
      fields,
      avatarFile: avatarFile,
      bannerFile: bannerFile,
    );
    if (res['user'] is Map<String, dynamic>) {
      await _persistUser(UserProfile.fromJson(res['user'] as Map<String, dynamic>), api.token!);
    }
  }

  Future<void> updateSpecialtyRole(String? role) async {
    if (!isAuthenticated || user == null) return;
    try {
      final res = await api.put('/profile/role', {'specialty_role': role});
      if (res['user'] is Map<String, dynamic>) {
        await _persistUser(UserProfile.fromJson(res['user'] as Map<String, dynamic>), api.token!);
        return;
      }
    } on ApiException catch (e) {
      if (e.statusCode != 404 && e.statusCode != 405) rethrow;
    }

    await updateProfile(
      namaLengkap: user!.namaLengkap,
      bio: user!.bio ?? '',
      specialtyRole: role,
    );
  }

  Future<void> updateSettings({
    required String newLocale,
    required String newTheme,
    PrivacySettings? privacy,
  }) async {
    locale = newLocale;
    theme = newTheme;
    api.locale = newLocale;

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_localeKey, newLocale);
    await prefs.setString(_themeKey, newTheme);

    if (isAuthenticated) {
      final res = await api.put('/profile/settings', {
        'locale': newLocale,
        'theme': newTheme,
        ...?privacy?.toJson(),
      });
      if (res['user'] is Map<String, dynamic>) {
        await _persistUser(UserProfile.fromJson(res['user'] as Map<String, dynamic>), api.token!, syncLocaleTheme: false);
      }
    }

    bumpContent();
    notifyListeners();
  }

  Future<void> toggleGuestLocale() async {
    final nextLocale = locale == 'id' ? 'en' : 'id';
    await updateSettings(newLocale: nextLocale, newTheme: theme, privacy: user?.privacySettings);
  }

  Future<void> toggleTheme() async {
    final nextTheme = theme == 'light' ? 'dark' : 'light';
    await updateSettings(newLocale: locale, newTheme: nextTheme, privacy: user?.privacySettings);
  }

  Future<void> logout() async {
    try {
      await api.post('/auth/logout');
    } catch (_) {}
    await clearSession();
  }

  Future<void> clearSession() async {
    api.token = null;
    user = null;
    unreadChatBadge = 0;
    latestChatAlert = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_userKey);
    notifyListeners();
  }

  Future<void> _persistUser(UserProfile newUser, String token, {bool syncLocaleTheme = true}) async {
    user = newUser;
    api.token = token;
    if (syncLocaleTheme) {
      if (newUser.locale == 'id' || newUser.locale == 'en') {
        locale = newUser.locale;
        api.locale = locale;
      }
      if (newUser.theme == 'light' || newUser.theme == 'dark') {
        theme = newUser.theme;
      }
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
    await prefs.setString(_userKey, jsonEncode(newUser.toJson()));
    await prefs.setString(_localeKey, locale);
    await prefs.setString(_themeKey, theme);
    bumpContent();
    notifyListeners();
  }

  @override
  void dispose() {
    _unreadTimer?.cancel();
    _linkSub?.cancel();
    super.dispose();
  }
}
