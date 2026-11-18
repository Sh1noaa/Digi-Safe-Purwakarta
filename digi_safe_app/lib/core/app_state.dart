import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:app_links/app_links.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';

import '../models/digisafe_models.dart';
import 'api_client.dart';
import 'strings.dart';

class AppState extends ChangeNotifier {
  static const _tokenKey = 'digisafe_api_token';
  static const _userKey = 'digisafe_user_json';
  static const _localeKey = 'digisafe_locale';
  static const _themeKey = 'digisafe_theme';

  final ApiClient api = ApiClient();
  final AppLinks _appLinks = AppLinks();
  StreamSubscription<Uri>? _linkSub;

  bool initialized = false;
  int currentTabIndex = 0; // 0: Beranda, 1: Edukasi, 2: Minigame, 3: Forum, 4: Testimoni, 5: Tentang
  int contentVersion = 0;

  String locale = 'id';
  String theme = 'light';
  UserProfile? user;
  String? oauthStatusMessage;

  bool get isDark => theme == 'dark';
  bool get isEn => locale == 'en';
  bool get isAuthenticated => user != null && (api.token?.isNotEmpty ?? false);

  String tr(String key, [Map<String, String> params = const {}]) => S.t(key, isEn, params);

  void setTab(int idx) {
    if (idx == currentTabIndex) return;
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

    // Sinkronkan profil & preferensi terbaru dari server jika sudah login
    if (savedToken != null && savedToken.isNotEmpty) {
      unawaited(refreshMe());
    }

    _listenDeepLinks();
  }

  void _listenDeepLinks() {
    if (kIsWeb) return;
    _appLinks.getInitialLink().then((uri) {
      if (uri != null) {
        _handleIncomingUri(uri);
      }
    }).catchError((_) {});

    _linkSub = _appLinks.uriLinkStream.listen(_handleIncomingUri, onError: (_) {});
  }

  Future<void> _handleIncomingUri(Uri uri) async {
    if (uri.scheme == 'digisafe' && uri.host == 'auth') {
      final err = uri.queryParameters['error'];
      final token = uri.queryParameters['token'];

      if (err != null && err.isNotEmpty) {
        oauthStatusMessage = err;
        notifyListeners();
        return;
      }

      if (token != null && token.isNotEmpty) {
        await completeTokenLogin(token);
      }
    }
  }

  void clearOauthStatus() {
    oauthStatusMessage = null;
  }

  Future<void> launchGoogleSignIn() async {
    final uri = Uri.parse('${ApiClient.baseHost}/auth/google/redirect?mode=mobile');
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  Future<void> completeTokenLogin(String token) async {
    api.token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
    await refreshMe();
    oauthStatusMessage = isEn ? 'Signed in with Google!' : 'Berhasil masuk dengan akun Google!';
    notifyListeners();
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
    } catch (_) {
      // Tetap pertahankan cache lokal jika offline sesaat
    }
  }

  Future<void> login(String email, String password) async {
    final res = await api.post('/auth/login', {
      'email': email.trim(),
      'password': password,
      'device_name': 'flutter_android',
    });
    final token = (res['token'] ?? '').toString();
    final u = UserProfile.fromJson(res['user'] as Map<String, dynamic>);
    await _persistUser(u, token);
  }

  Future<void> register({
    required String namaLengkap,
    required String username,
    required String email,
    required String password,
  }) async {
    final res = await api.post('/auth/register', {
      'nama_lengkap': namaLengkap.trim(),
      'username': username.trim(),
      'email': email.trim(),
      'password': password,
      'device_name': 'flutter_android',
    });
    final token = (res['token'] ?? '').toString();
    final u = UserProfile.fromJson(res['user'] as Map<String, dynamic>);
    await _persistUser(u, token);
  }

  Future<void> updateProfile({
    required String namaLengkap,
    required String bio,
    String? password,
    File? avatarFile,
    File? bannerFile,
  }) async {
    final fields = <String, String>{
      'nama_lengkap': namaLengkap.trim(),
      'bio': bio.trim(),
    };
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
    _linkSub?.cancel();
    super.dispose();
  }
}

