import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../widgets/shared_widgets.dart';
import 'main_shell.dart';
import 'onboarding_wizard_screen.dart';
import 'splash_onboarding_screen.dart';

/// Auth Welcome Screen (Desain Terinspirasi Gambar 3):
/// - Login Email hanya Email (Gmail) + Password
/// - Daftar Baru hanya Email (Gmail) + Password + Accept/Konfirmasi Password
/// - Google Sign-In memakai Android Partial Bottom-Sheet Pop-Up (bukan melempar ke full browser!)
/// - Setelah auth berhasil -> otomatis menuju OnboardingWizardScreen (jika baru/lengkapi profil)
///   atau OrbitalSplashScreen(returningMode: true) (jika user lama melanjutkan harinya)!
class AuthWelcomeScreen extends StatefulWidget {
  final AppState appState;
  final bool openEmailInitially;
  final bool openRegisterInitially;
  final bool popOnSuccess;

  const AuthWelcomeScreen({
    super.key,
    required this.appState,
    this.openEmailInitially = false,
    this.openRegisterInitially = false,
    this.popOnSuccess = false,
  });

  @override
  State<AuthWelcomeScreen> createState() => _AuthWelcomeScreenState();
}

class _AuthWelcomeScreenState extends State<AuthWelcomeScreen>
    with SingleTickerProviderStateMixin, WidgetsBindingObserver {
  late final AnimationController _floatCtrl;
  bool _googleBusy = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _floatCtrl = AnimationController(vsync: this, duration: const Duration(seconds: 6))..repeat();

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      if (widget.openEmailInitially) {
        _openEmailLoginSheet();
      } else if (widget.openRegisterInitially) {
        _openRegisterSheet();
      }
    });
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed && _googleBusy) {
      // Apabila pop-up Google ditutup user secara manual tanpa menyelesaikan login
      widget.appState.cancelPendingGoogleSignInIfIdle();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _floatCtrl.dispose();
    super.dispose();
  }

  void _routeAfterSuccess({required bool needsOnboarding}) {
    if (!mounted) return;
    if (needsOnboarding) {
      Navigator.of(context).pushAndRemoveUntil(
        PageRouteBuilder(
          transitionDuration: const Duration(milliseconds: 420),
          pageBuilder: (_, _, _) => OnboardingWizardScreen(appState: widget.appState),
          transitionsBuilder: (_, anim, _, child) => FadeTransition(opacity: anim, child: child),
        ),
        (_) => false,
      );
      return;
    }

    // Returning user masuk lagi -> tampilkan Loading Screen Gelombang + Maskot (Gambar 2 GIF style!)
    Navigator.of(context).pushAndRemoveUntil(
      PageRouteBuilder(
        transitionDuration: const Duration(milliseconds: 420),
        pageBuilder: (_, _, _) => OrbitalSplashScreen(appState: widget.appState, returningMode: true),
        transitionsBuilder: (_, anim, _, child) => FadeTransition(opacity: anim, child: child),
      ),
      (_) => false,
    );
  }

  void _enterGuest() {
    if (!mounted) return;
    if (widget.popOnSuccess && Navigator.of(context).canPop()) {
      Navigator.of(context).pop(true);
    } else {
      Navigator.of(context).pushReplacement(
        PageRouteBuilder(
          transitionDuration: const Duration(milliseconds: 380),
          pageBuilder: (_, _, _) => MainShell(appState: widget.appState),
          transitionsBuilder: (_, anim, _, child) => FadeTransition(opacity: anim, child: child),
        ),
      );
    }
  }

  Future<void> _handleGoogleLogin() async {
    if (_googleBusy) return;

    // Tampilkan konfirmasi pop-up ringan bergaya Google Account Picker lalu buka Partial Bottom Sheet OAuth Pop-Up
    final proceed = await _showGooglePopupLauncher();
    if (proceed != true || !mounted) return;

    setState(() => _googleBusy = true);
    try {
      final result = await widget.appState.launchGoogleSignIn();
      if (!mounted) return;
      if (result.ok && widget.appState.isAuthenticated) {
        _routeAfterSuccess(needsOnboarding: result.needsOnboarding);
      } else {
        final msg = widget.appState.oauthStatusMessage ??
            (widget.appState.isEn ? 'Google sign-in was cancelled.' : 'Login Google dibatalkan.');
        widget.appState.clearOauthStatus();
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              widget.appState.isEn ? 'Unable to start Google sign-in: $e' : 'Gagal membuka login Google: $e',
            ),
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _googleBusy = false);
    }
  }

  Future<bool?> _showGooglePopupLauncher() {
    final isDark = widget.appState.isDark;
    final isEn = widget.appState.isEn;
    return showDialog<bool>(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: AppColors.cardBg(isDark),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(22, 22, 22, 18),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                children: [
                  Container(
                    width: 42,
                    height: 42,
                    decoration: BoxDecoration(
                      color: AppColors.softBg(isDark),
                      shape: BoxShape.circle,
                      border: Border.all(color: AppColors.border(isDark)),
                    ),
                    alignment: Alignment.center,
                    child: const GoogleGMark(size: 22),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isEn ? 'Google Pop-Up Sign-In' : 'Pop-Up Masuk Google',
                          style: AppTheme.heading(isDark, size: 16),
                        ),
                        Text(
                          'Digi-Safe Purwakarta Account Sync',
                          style: AppTheme.body(isDark, size: 11, color: AppColors.textSecondary(isDark)),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.softBg(isDark),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border(isDark)),
                ),
                child: Text(
                  isEn
                      ? 'Choose your Gmail in the quick pop-up sheet. Your account will be created & synced with the same Digi-Safe database!'
                      : 'Pilih akun Gmail melalui pop-up instan tanpa berpindah aplikasi browser. Akun otomatis dibuat/terhubung ke database Digi-Safe yang sama!',
                  style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark), height: 1.45),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: TextButton(
                      onPressed: () => Navigator.pop(ctx, false),
                      child: Text(isEn ? 'Cancel' : 'Batal'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    flex: 2,
                    child: ElevatedButton.icon(
                      onPressed: () => Navigator.pop(ctx, true),
                      icon: const GoogleGMark(size: 16),
                      label: Text(
                        isEn ? 'Open Account Picker' : 'Pilih Akun Gmail',
                        style: AppTheme.heading(false, size: 12.5, color: Colors.white),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.cobalt,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _openEmailLoginSheet() {
    showModalBottomSheet<({bool ok, bool needsOnboarding})?>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _EmailLoginSheet(
        appState: widget.appState,
        onSwitchToRegister: () {
          Navigator.of(context).pop(null);
          _openRegisterSheet();
        },
        onGoogleTap: () {
          Navigator.of(context).pop(null);
          _handleGoogleLogin();
        },
      ),
    ).then((res) {
      if (res != null && res.ok && mounted) {
        _routeAfterSuccess(needsOnboarding: res.needsOnboarding);
      }
    });
  }

  void _openRegisterSheet() {
    showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _RegisterEmailOnlySheet(
        appState: widget.appState,
        onSwitchToLogin: () {
          Navigator.of(context).pop(false);
          _openEmailLoginSheet();
        },
        onGoogleTap: () {
          Navigator.of(context).pop(false);
          _handleGoogleLogin();
        },
      ),
    ).then((registered) {
      if (registered == true && mounted) {
        // Setelah Register (Gmail + Password), lanjut panel Username -> YEAY -> Bio & Role -> Builder!
        _routeAfterSuccess(needsOnboarding: true);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0A1020) : Colors.white,
      body: Stack(
        children: [
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            height: MediaQuery.of(context).size.height * 0.56,
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: isDark
                      ? [
                          const Color(0xFF172554).withValues(alpha: 0.78),
                          const Color(0xFF0F172A).withValues(alpha: 0.45),
                          const Color(0xFF0A1020),
                        ]
                      : const [
                          Color(0xFFE3EEFF),
                          Color(0xFFEFF4FF),
                          Color(0xFFFFFFFF),
                        ],
                ),
              ),
            ),
          ),

          SafeArea(
            child: Column(
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 6, 16, 0),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      if (widget.popOnSuccess && Navigator.of(context).canPop())
                        IconButton(
                          onPressed: () => Navigator.of(context).pop(false),
                          icon: Icon(Icons.close_rounded, color: AppColors.textPrimary(isDark)),
                        )
                      else
                        const SizedBox(width: 40),
                      GestureDetector(
                        onTap: () => setState(() => state.toggleGuestLocale()),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 5),
                          decoration: BoxDecoration(
                            color: (isDark ? Colors.white : AppColors.cobalt).withValues(alpha: 0.10),
                            borderRadius: BorderRadius.circular(99),
                          ),
                          child: Text(
                            state.locale.toUpperCase(),
                            style: AppTheme.heading(isDark, size: 10.5, color: isDark ? Colors.white : AppColors.cobalt),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // DASHED ORBITAL + 6 GLOSSY 3D STICKERS + FROSTED CENTRAL BRAND MEDALLION (Image 3)
                Expanded(
                  child: Center(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      child: FittedBox(
                        fit: BoxFit.scaleDown,
                        child: SizedBox(
                          width: 310,
                          height: 300,
                          child: AnimatedBuilder(
                            animation: _floatCtrl,
                            builder: (context, _) {
                              final t = _floatCtrl.value * 2 * math.pi;
                              return Stack(
                                alignment: Alignment.center,
                                clipBehavior: Clip.none,
                                children: [
                                  CustomPaint(
                                    size: const Size(290, 290),
                                    painter: _DashedOrbitsPainter(isDark: isDark),
                                  ),

                                  Container(
                                    width: 126,
                                    height: 126,
                                    decoration: BoxDecoration(
                                      shape: BoxShape.circle,
                                      color: (isDark ? const Color(0xFF1E293B) : Colors.white).withValues(alpha: 0.65),
                                      border: Border.all(
                                        color: Colors.white.withValues(alpha: 0.7),
                                        width: 1.5,
                                      ),
                                      boxShadow: [
                                        BoxShadow(
                                          color: AppColors.cobalt.withValues(alpha: 0.10),
                                          blurRadius: 28,
                                          offset: const Offset(0, 8),
                                        ),
                                      ],
                                    ),
                                    child: Column(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Image.asset('assets/images/logo.png', width: 32, height: 32),
                                        const SizedBox(height: 6),
                                        ShaderMask(
                                          shaderCallback: (bounds) => const LinearGradient(
                                            colors: [Color(0xFF2563EB), Color(0xFF0EA5E9), Color(0xFF4F46E5)],
                                          ).createShader(bounds),
                                          child: Text(
                                            'DIGI•SAFE',
                                            style: AppTheme.heading(false, size: 19, color: Colors.white)
                                                .copyWith(letterSpacing: -0.4),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),

                                  Positioned(
                                    top: 8 + math.sin(t) * 5,
                                    left: 52,
                                    child: _StickerSquircle(
                                      angle: -0.14,
                                      size: 56,
                                      gradient: const [Color(0xFF38BDF8), Color(0xFF1D4ED8)],
                                      child: ClipRRect(
                                        borderRadius: BorderRadius.circular(14),
                                        child: Image.asset('assets/images/bot-digika.jpg', fit: BoxFit.cover),
                                      ),
                                    ),
                                  ),

                                  Positioned(
                                    top: 20 + math.cos(t + 1.0) * 5,
                                    right: 34,
                                    child: const _StickerSquircle(
                                      angle: 0.16,
                                      size: 46,
                                      gradient: [Color(0xFFFF7A6B), Color(0xFFEF4444)],
                                      child: Icon(Icons.location_on_rounded, color: Colors.white, size: 23),
                                    ),
                                  ),

                                  Positioned(
                                    top: 108 + math.sin(t + 2.1) * 5,
                                    left: 4,
                                    child: const _StickerSquircle(
                                      angle: -0.10,
                                      size: 52,
                                      gradient: [Color(0xFFC084FC), Color(0xFF7C3AED)],
                                      child: Icon(Icons.forum_rounded, color: Colors.white, size: 24),
                                    ),
                                  ),

                                  Positioned(
                                    top: 112 + math.cos(t + 2.8) * 5,
                                    right: 2,
                                    child: const _StickerSquircle(
                                      angle: 0.12,
                                      size: 58,
                                      gradient: [Color(0xFF60A5FA), Color(0xFF1E40AF)],
                                      child: Icon(Icons.terminal_rounded, color: Colors.white, size: 28),
                                    ),
                                  ),

                                  Positioned(
                                    bottom: 16 + math.sin(t + 4.0) * 5,
                                    left: 32,
                                    child: const _StickerSquircle(
                                      angle: -0.12,
                                      size: 56,
                                      gradient: [Color(0xFFFCA5A5), Color(0xFFDC2626)],
                                      child: Icon(Icons.security_rounded, color: Colors.white, size: 26),
                                    ),
                                  ),

                                  Positioned(
                                    bottom: 12 + math.cos(t + 5.0) * 5,
                                    right: 44,
                                    child: const _StickerSquircle(
                                      angle: 0.18,
                                      size: 52,
                                      gradient: [Color(0xFF34D399), Color(0xFF059669)],
                                      child: Icon(Icons.sports_esports_rounded, color: Colors.white, size: 24),
                                    ),
                                  ),
                                ],
                              );
                            },
                          ),
                        ),
                      ),
                    ),
                  ),
                ),

                Padding(
                  padding: const EdgeInsets.fromLTRB(26, 4, 26, 26),
                  child: Column(
                    children: [
                      Text(
                        '${state.tr('auth_landing_line1')}\n${state.tr('auth_landing_line2')}',
                        textAlign: TextAlign.center,
                        style: AppTheme.heading(isDark, size: 27, height: 1.15),
                      ),
                      const SizedBox(height: 26),

                      // Pill 1: Continue with Email (Hanya Gmail & Password!)
                      SizedBox(
                        width: double.infinity,
                        height: 56,
                        child: ElevatedButton(
                          onPressed: _googleBusy ? null : _openEmailLoginSheet,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: isDark ? AppColors.cobalt : const Color(0xFF17181D),
                            foregroundColor: Colors.white,
                            elevation: 0,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                          ),
                          child: Text(
                            state.tr('auth_continue_email'),
                            style: AppTheme.heading(false, size: 15, color: Colors.white),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Pill 2: Create New Account (Hanya Gmail, Password & Accept Password -> Wizard!)
                      SizedBox(
                        width: double.infinity,
                        height: 56,
                        child: ElevatedButton(
                          onPressed: _googleBusy ? null : _openRegisterSheet,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: isDark ? AppColors.darkCard : const Color(0xFFF4F6FA),
                            foregroundColor: AppColors.textPrimary(isDark),
                            elevation: 0,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(99),
                              side: BorderSide(color: isDark ? AppColors.darkBorder : const Color(0xFFEAECEF)),
                            ),
                          ),
                          child: Text(
                            state.tr('auth_continue_register'),
                            style: AppTheme.heading(isDark, size: 15),
                          ),
                        ),
                      ),

                      const SizedBox(height: 18),

                      Row(
                        children: [
                          Expanded(child: Divider(color: AppColors.border(isDark).withValues(alpha: 0.7))),
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 14),
                            child: Text(
                              state.tr('auth_or'),
                              style: AppTheme.heading(
                                isDark,
                                size: 11,
                                color: AppColors.textSecondary(isDark),
                              ),
                            ),
                          ),
                          Expanded(child: Divider(color: AppColors.border(isDark).withValues(alpha: 0.7))),
                        ],
                      ),

                      const SizedBox(height: 18),

                      Row(
                        children: [
                          Expanded(
                            child: SizedBox(
                              height: 54,
                              child: ElevatedButton.icon(
                                onPressed: _googleBusy ? null : _enterGuest,
                                icon: Icon(
                                  Icons.explore_outlined,
                                  size: 20,
                                  color: AppColors.textPrimary(isDark),
                                ),
                                label: Text(
                                  state.tr('auth_explore_guest'),
                                  style: AppTheme.heading(isDark, size: 14),
                                ),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: isDark ? AppColors.darkCard : const Color(0xFFF4F6FA),
                                  foregroundColor: AppColors.textPrimary(isDark),
                                  elevation: 0,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(99),
                                    side: BorderSide(color: isDark ? AppColors.darkBorder : const Color(0xFFEAECEF)),
                                  ),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: SizedBox(
                              height: 54,
                              child: ElevatedButton(
                                onPressed: _googleBusy ? null : _handleGoogleLogin,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: isDark ? AppColors.darkCard : const Color(0xFFF4F6FA),
                                  foregroundColor: AppColors.textPrimary(isDark),
                                  elevation: 0,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(99),
                                    side: BorderSide(color: isDark ? AppColors.darkBorder : const Color(0xFFEAECEF)),
                                  ),
                                ),
                                child: _googleBusy
                                    ? const SizedBox(
                                        width: 20,
                                        height: 20,
                                        child: CircularProgressIndicator(strokeWidth: 2.2, color: AppColors.sky),
                                      )
                                    : Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          const GoogleGMark(size: 20),
                                          const SizedBox(width: 8),
                                          Text(
                                            state.tr('auth_google_short'),
                                            style: AppTheme.heading(isDark, size: 14.5),
                                          ),
                                        ],
                                      ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class LoginScreen extends StatelessWidget {
  final AppState appState;

  const LoginScreen({super.key, required this.appState});

  @override
  Widget build(BuildContext context) {
    return AuthWelcomeScreen(
      appState: appState,
      openEmailInitially: true,
      popOnSuccess: true,
    );
  }
}

class RegisterScreen extends StatelessWidget {
  final AppState appState;

  const RegisterScreen({super.key, required this.appState});

  @override
  Widget build(BuildContext context) {
    return AuthWelcomeScreen(
      appState: appState,
      openRegisterInitially: true,
      popOnSuccess: true,
    );
  }
}

/// Login dengan Email: HANYA Gmail/Email + Password.
class _EmailLoginSheet extends StatefulWidget {
  final AppState appState;
  final VoidCallback onSwitchToRegister;
  final VoidCallback onGoogleTap;

  const _EmailLoginSheet({
    required this.appState,
    required this.onSwitchToRegister,
    required this.onGoogleTap,
  });

  @override
  State<_EmailLoginSheet> createState() => _EmailLoginSheetState();
}

class _EmailLoginSheetState extends State<_EmailLoginSheet> {
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  bool _showPass = false;
  bool _submitting = false;
  String? _errorMsg;

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final email = _emailCtrl.text.trim();
    final pass = _passCtrl.text;
    if (email.isEmpty || pass.isEmpty) {
      setState(() => _errorMsg = widget.appState.isEn ? 'Gmail/Email and password are required.' : 'Gmail/Email dan password wajib diisi.');
      return;
    }

    setState(() {
      _submitting = true;
      _errorMsg = null;
    });

    try {
      final result = await widget.appState.login(email, pass);
      if (mounted) {
        Navigator.of(context).pop((ok: true, needsOnboarding: result.needsOnboarding));
      }
    } catch (e) {
      if (mounted) setState(() => _errorMsg = e.toString());
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final isEn = state.isEn;

    return Container(
      padding: EdgeInsets.fromLTRB(22, 16, 22, MediaQuery.of(context).viewInsets.bottom + 24),
      decoration: BoxDecoration(
        color: AppColors.cardBg(isDark),
        borderRadius: const BorderRadius.vertical(top: Radius.circular(30)),
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 42,
                height: 4,
                decoration: BoxDecoration(color: AppColors.border(isDark), borderRadius: BorderRadius.circular(99)),
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.cobaltTint,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Image.asset('assets/images/logo.png'),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(state.tr('auth_login_title'), style: AppTheme.heading(isDark, size: 18)),
                      Text(
                        isEn ? 'Sign in using your Gmail & Password' : 'Masuk cukup pakai Gmail & Password kamu',
                        style: AppTheme.body(isDark, size: 11.5, color: AppColors.textSecondary(isDark)),
                      ),
                    ],
                  ),
                ),
                IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.close_rounded)),
              ],
            ),
            if (_errorMsg != null) ...[
              const SizedBox(height: 12),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF2F2),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFFCA5A5)),
                ),
                child: Text(_errorMsg!, style: AppTheme.body(false, size: 12, color: const Color(0xFFDC2626))),
              ),
            ],
            const SizedBox(height: 16),
            TextField(
              controller: _emailCtrl,
              keyboardType: TextInputType.emailAddress,
              onChanged: (_) => setState(() {}),
              decoration: _pillInputDecoration(
                isDark,
                label: 'Gmail / Email',
                hint: 'nama@gmail.com',
                icon: Icons.mail_outline_rounded,
                suffix: _emailCtrl.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.close_rounded, size: 17),
                        onPressed: () => setState(() => _emailCtrl.clear()),
                      )
                    : null,
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _passCtrl,
              obscureText: !_showPass,
              decoration: _pillInputDecoration(
                isDark,
                label: 'Password',
                hint: '••••••••',
                icon: Icons.lock_outline_rounded,
                suffix: IconButton(
                  icon: Icon(_showPass ? Icons.visibility_off_rounded : Icons.visibility_rounded, size: 18),
                  onPressed: () => setState(() => _showPass = !_showPass),
                ),
              ),
            ),
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                onPressed: _submitting ? null : _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: isDark ? AppColors.cobalt : AppColors.ink,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                ),
                child: _submitting
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2.2, color: AppColors.lime))
                    : Text(state.tr('auth_login_now'), style: AppTheme.heading(false, size: 14, color: Colors.white)),
              ),
            ),
            const SizedBox(height: 10),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Flexible(
                  child: TextButton(
                    onPressed: widget.onSwitchToRegister,
                    child: Text(
                      isEn ? 'Create an account' : 'Belum punya akun? Daftar',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: AppTheme.heading(isDark, size: 11.5, color: AppColors.sky),
                    ),
                  ),
                ),
                TextButton.icon(
                  onPressed: widget.onGoogleTap,
                  icon: const GoogleGMark(size: 16),
                  label: Text('Google Pop-Up', style: AppTheme.heading(isDark, size: 11.5)),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

/// Register Email-Only: HANYA Gmail/Email + Password + Accept/Konfirmasi Password!
/// Username, Nickname, Bio, dan Role diatur interaktif di OnboardingWizardScreen tepat sesudah ini.
class _RegisterEmailOnlySheet extends StatefulWidget {
  final AppState appState;
  final VoidCallback onSwitchToLogin;
  final VoidCallback onGoogleTap;

  const _RegisterEmailOnlySheet({
    required this.appState,
    required this.onSwitchToLogin,
    required this.onGoogleTap,
  });

  @override
  State<_RegisterEmailOnlySheet> createState() => _RegisterEmailOnlySheetState();
}

class _RegisterEmailOnlySheetState extends State<_RegisterEmailOnlySheet> {
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmPassCtrl = TextEditingController();
  bool _showPass = false;
  bool _submitting = false;
  String? _errorMsg;

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _confirmPassCtrl.dispose();
    super.dispose();
  }

  int get _passwordStrength {
    final p = _passCtrl.text;
    if (p.isEmpty) return 0;
    int score = 0;
    if (p.length >= 8) score++;
    if (RegExp(r'[A-Z]').hasMatch(p) && RegExp(r'[0-9]').hasMatch(p)) score++;
    if (p.length >= 10 && RegExp(r'[^a-zA-Z0-9]').hasMatch(p)) score++;
    return math.max(1, score);
  }

  Future<void> _submit() async {
    final email = _emailCtrl.text.trim();
    final pass = _passCtrl.text;
    final accept = _confirmPassCtrl.text;

    if (email.isEmpty || !email.contains('@')) {
      setState(() => _errorMsg = widget.appState.isEn ? 'Enter a valid Gmail / Email address.' : 'Masukkan alamat Gmail / Email yang valid.');
      return;
    }
    if (pass.length < 8) {
      setState(() => _errorMsg = widget.appState.isEn ? 'Password must be at least 8 characters.' : 'Password minimal 8 karakter.');
      return;
    }
    if (pass != accept) {
      setState(() => _errorMsg = widget.appState.isEn ? 'Password confirmation does not match!' : 'Konfirmasi (Accept) password tidak sama!');
      return;
    }

    setState(() {
      _submitting = true;
      _errorMsg = null;
    });

    try {
      await widget.appState.registerEmailOnly(email: email, password: pass);
      if (mounted) Navigator.of(context).pop(true);
    } catch (e) {
      if (mounted) setState(() => _errorMsg = e.toString());
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final isEn = state.isEn;
    final s = _passwordStrength;

    return Container(
      constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.88),
      padding: EdgeInsets.fromLTRB(22, 16, 22, MediaQuery.of(context).viewInsets.bottom + 24),
      decoration: BoxDecoration(
        color: AppColors.cardBg(isDark),
        borderRadius: const BorderRadius.vertical(top: Radius.circular(30)),
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 42,
                height: 4,
                decoration: BoxDecoration(color: AppColors.border(isDark), borderRadius: BorderRadius.circular(99)),
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(color: AppColors.lime, borderRadius: BorderRadius.circular(12)),
                  child: const Icon(Icons.mark_email_read_outlined, color: AppColors.ink, size: 20),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(state.tr('auth_register_title'), style: AppTheme.heading(isDark, size: 17.5)),
                      Text(
                        isEn
                            ? 'Enter Gmail & Password first — customize profile next!'
                            : 'Cukup Gmail & Password dulu — username & role di halaman berikutnya!',
                        style: AppTheme.body(isDark, size: 11, color: AppColors.textSecondary(isDark)),
                      ),
                    ],
                  ),
                ),
                IconButton(onPressed: () => Navigator.pop(context, false), icon: const Icon(Icons.close_rounded)),
              ],
            ),
            if (_errorMsg != null) ...[
              const SizedBox(height: 12),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF2F2),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFFCA5A5)),
                ),
                child: Text(_errorMsg!, style: AppTheme.body(false, size: 12, color: const Color(0xFFDC2626))),
              ),
            ],
            const SizedBox(height: 16),
            TextField(
              controller: _emailCtrl,
              keyboardType: TextInputType.emailAddress,
              decoration: _pillInputDecoration(
                isDark,
                label: 'Gmail / Email',
                hint: 'nama@gmail.com',
                icon: Icons.mail_outline_rounded,
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _passCtrl,
              obscureText: !_showPass,
              onChanged: (_) => setState(() {}),
              decoration: _pillInputDecoration(
                isDark,
                label: 'Password (min. 8)',
                hint: 'Min. 8 karakter + huruf & angka',
                icon: Icons.lock_outline_rounded,
                suffix: IconButton(
                  icon: Icon(_showPass ? Icons.visibility_off_rounded : Icons.visibility_rounded, size: 18),
                  onPressed: () => setState(() => _showPass = !_showPass),
                ),
              ),
            ),
            const SizedBox(height: 8),
            Row(
              children: List.generate(3, (i) {
                final active = s > i;
                final col = s == 1
                    ? AppColors.danger
                    : s == 2
                        ? AppColors.amber
                        : AppColors.emerald;
                return Expanded(
                  child: Container(
                    height: 4,
                    margin: EdgeInsets.only(right: i == 2 ? 0 : 6),
                    decoration: BoxDecoration(
                      color: active ? col : AppColors.border(isDark),
                      borderRadius: BorderRadius.circular(99),
                    ),
                  ),
                );
              }),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _confirmPassCtrl,
              obscureText: !_showPass,
              decoration: _pillInputDecoration(
                isDark,
                label: isEn ? 'Accept / Confirm Password' : 'Accept / Konfirmasi Password',
                hint: isEn ? 'Re-type your password' : 'Ketik ulang password kamu',
                icon: Icons.verified_user_outlined,
              ),
            ),
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                onPressed: _submitting ? null : _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: isDark ? AppColors.cobalt : AppColors.ink,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                ),
                child: _submitting
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2.2, color: AppColors.lime))
                    : Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(isEn ? 'Continue to Profile Setup' : 'Lanjut Atur Profil', style: AppTheme.heading(false, size: 14, color: Colors.white)),
                          const SizedBox(width: 8),
                          const Icon(Icons.arrow_forward_rounded, size: 17, color: AppColors.lime),
                        ],
                      ),
              ),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Flexible(
                  child: TextButton(
                    onPressed: widget.onSwitchToLogin,
                    child: Text(
                      isEn ? 'Already have an account? Sign In' : 'Sudah punya akun? Masuk',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: AppTheme.heading(isDark, size: 11.5, color: AppColors.sky),
                    ),
                  ),
                ),
                TextButton.icon(
                  onPressed: widget.onGoogleTap,
                  icon: const GoogleGMark(size: 16),
                  label: Text('Google Pop-Up', style: AppTheme.heading(isDark, size: 11.5)),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

InputDecoration _pillInputDecoration(
  bool isDark, {
  required String label,
  required String hint,
  required IconData icon,
  Widget? suffix,
}) {
  return InputDecoration(
    labelText: label,
    hintText: hint,
    prefixIcon: Icon(icon, size: 19, color: AppColors.sky),
    suffixIcon: suffix,
    filled: true,
    fillColor: AppColors.softBg(isDark),
    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
    border: OutlineInputBorder(borderRadius: BorderRadius.circular(18), borderSide: BorderSide(color: AppColors.border(isDark))),
    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(18), borderSide: BorderSide(color: AppColors.border(isDark))),
    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(18), borderSide: const BorderSide(color: AppColors.cobalt, width: 1.6)),
  );
}

class _StickerSquircle extends StatelessWidget {
  final double angle;
  final double size;
  final List<Color> gradient;
  final Widget child;

  const _StickerSquircle({
    required this.angle,
    required this.size,
    required this.gradient,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return Transform.rotate(
      angle: angle,
      child: Container(
        width: size,
        height: size,
        padding: const EdgeInsets.all(3),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
              color: gradient.last.withValues(alpha: 0.28),
              blurRadius: 16,
              offset: const Offset(0, 7),
            ),
          ],
        ),
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(15),
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: gradient,
            ),
          ),
          alignment: Alignment.center,
          child: child,
        ),
      ),
    );
  }
}

class _DashedOrbitsPainter extends CustomPainter {
  final bool isDark;
  const _DashedOrbitsPainter({required this.isDark});

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final paint = Paint()
      ..color = (isDark ? Colors.white : const Color(0xFF93C5FD)).withValues(alpha: isDark ? 0.18 : 0.45)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.4;

    for (final radius in const [68.0, 122.0]) {
      const dashLen = 6.0;
      const gapLen = 5.0;
      final circum = 2 * math.pi * radius;
      final steps = (circum / (dashLen + gapLen)).floor();
      final sweepStep = (2 * math.pi) / steps;
      final dashSweep = (dashLen / circum) * 2 * math.pi;

      final rect = Rect.fromCircle(center: center, radius: radius);
      for (int i = 0; i < steps; i++) {
        canvas.drawArc(rect, i * sweepStep, dashSweep, false, paint);
      }
    }
  }

  @override
  bool shouldRepaint(covariant _DashedOrbitsPainter oldDelegate) => oldDelegate.isDark != isDark;
}
