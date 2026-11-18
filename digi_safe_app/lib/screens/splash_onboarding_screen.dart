import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import 'auth_screens.dart';
import 'main_shell.dart';

/// Loading & Welcome Screen Beranimasi Maskot + Gelombang Konsentris (Terinspirasi dari Gambar 2 GIF):
/// - Dipakai saat awal membuka aplikasi (baik user baru/tamu maupun user lama yang ingin melanjutkan harinya)
/// - Dipakai pula tepat setelah user lama masuk akun sebelum masuk ke Beranda utama.
class OrbitalSplashScreen extends StatefulWidget {
  final AppState appState;
  final bool returningMode;

  const OrbitalSplashScreen({
    super.key,
    required this.appState,
    this.returningMode = false,
  });

  @override
  State<OrbitalSplashScreen> createState() => _OrbitalSplashScreenState();
}

class _OrbitalSplashScreenState extends State<OrbitalSplashScreen> with TickerProviderStateMixin {
  late final AnimationController _waveCtrl;
  late final AnimationController _mascotCtrl;

  bool _ready = false;
  bool _navigated = false;

  @override
  void initState() {
    super.initState();
    _waveCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 2800))..repeat();
    _mascotCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 1400))..repeat(reverse: true);
    _prepareAndDecide();
  }

  Future<void> _prepareAndDecide() async {
    if (!widget.appState.initialized) {
      await widget.appState.init();
    } else if (widget.returningMode && widget.appState.isAuthenticated) {
      await Future.wait([
        widget.appState.refreshMe(),
        widget.appState.refreshUnreadCount(),
      ]);
    }

    await Future<void>.delayed(Duration(milliseconds: widget.returningMode ? 1500 : 1200));
    if (!mounted) return;
    setState(() => _ready = true);

    if (widget.appState.isAuthenticated || widget.returningMode) {
      await Future<void>.delayed(const Duration(milliseconds: 700));
      if (mounted && !_navigated) {
        _goToMainShell();
      }
    }
  }

  void _goToMainShell() {
    if (_navigated) return;
    _navigated = true;
    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        transitionDuration: const Duration(milliseconds: 450),
        pageBuilder: (_, _, _) => MainShell(appState: widget.appState),
        transitionsBuilder: (_, anim, _, child) => FadeTransition(opacity: anim, child: child),
      ),
    );
  }

  void _goToAuthWelcome() {
    if (_navigated) return;
    _navigated = true;
    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        transitionDuration: const Duration(milliseconds: 450),
        pageBuilder: (_, _, _) => AuthWelcomeScreen(appState: widget.appState),
        transitionsBuilder: (_, anim, _, child) => FadeTransition(opacity: anim, child: child),
      ),
    );
  }

  @override
  void dispose() {
    _waveCtrl.dispose();
    _mascotCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final isEn = state.isEn;
    final isReturningUser = state.isAuthenticated || widget.returningMode;
    final firstName = (state.user?.namaLengkap ?? 'Warga Siber').split(' ').first;

    return Scaffold(
      body: Stack(
        children: [
          // Vibrant Digi-Safe Gradient Backdrop (Cobalt -> Sky Blue -> Bright Cyan / Deep Indigo in Dark Mode)
          Positioned.fill(
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: isDark
                      ? const [Color(0xFF0A1432), Color(0xFF172554), Color(0xFF0B1120)]
                      : const [Color(0xFF2D46E6), Color(0xFF1E88E5), Color(0xFF0EA5E9)],
                ),
              ),
            ),
          ),

          // Pulsing Concentric Wave Rings & Floating Geometric Stars/Triangles/Circles (Image 2 GIF style!)
          Positioned.fill(
            child: AnimatedBuilder(
              animation: _waveCtrl,
              builder: (_, _) => CustomPaint(
                painter: _ConcentricPulseWavePainter(progress: _waveCtrl.value, isDark: isDark),
              ),
            ),
          ),

          SafeArea(
            child: Column(
              children: [
                // Top bar: Subtle returning badge or language switch
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 8, 20, 0),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      if (isReturningUser)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.16),
                            borderRadius: BorderRadius.circular(99),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const CircleAvatar(radius: 9, backgroundImage: AssetImage('assets/images/bot-digika.jpg')),
                              const SizedBox(width: 6),
                              Text(
                                isEn ? 'Daily Sync Active' : 'Sinkronisasi Harian',
                                style: AppTheme.heading(false, size: 10.5, color: Colors.white),
                              ),
                            ],
                          ),
                        )
                      else
                        const SizedBox(width: 40),
                      GestureDetector(
                        onTap: () => state.toggleGuestLocale(),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.18),
                            borderRadius: BorderRadius.circular(99),
                            border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                          ),
                          child: Text(
                            state.locale.toUpperCase(),
                            style: AppTheme.heading(false, size: 10.5, color: Colors.white),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // CENTER ANIMATED MASCOT & STYLIZED WORDMARK (Image 2 GIF Focal Point)
                Expanded(
                  child: Center(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 24),
                      child: AnimatedBuilder(
                        animation: _mascotCtrl,
                        builder: (_, _) {
                          final bob = math.sin(_mascotCtrl.value * math.pi) * -9;
                          final tilt = math.sin(_mascotCtrl.value * math.pi * 2) * 0.04;

                          return Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Transform.translate(
                                offset: Offset(0, bob),
                                child: Transform.rotate(
                                  angle: tilt,
                                  child: Stack(
                                    clipBehavior: Clip.none,
                                    alignment: Alignment.center,
                                    children: [
                                      Container(
                                        width: 132,
                                        height: 132,
                                        padding: const EdgeInsets.all(10),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          shape: BoxShape.circle,
                                          boxShadow: [
                                            BoxShadow(
                                              color: AppColors.lime.withValues(alpha: 0.42),
                                              blurRadius: 34,
                                              spreadRadius: 4,
                                            ),
                                          ],
                                        ),
                                        child: ClipOval(
                                          child: Image.asset(
                                            isReturningUser ? 'assets/images/mascot-login.png' : 'assets/images/mascot-register.png',
                                            fit: BoxFit.contain,
                                          ),
                                        ),
                                      ),
                                      // Small floating logo crest at bottom-right
                                      Positioned(
                                        right: -4,
                                        bottom: 2,
                                        child: Container(
                                          width: 42,
                                          height: 42,
                                          padding: const EdgeInsets.all(7),
                                          decoration: BoxDecoration(
                                            color: AppColors.lime,
                                            shape: BoxShape.circle,
                                            border: Border.all(color: Colors.white, width: 2.5),
                                          ),
                                          child: Image.asset('assets/images/logo.png', fit: BoxFit.contain),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              const SizedBox(height: 22),

                              // Stylized Block Typography (echoing Image 2 bold lettermark)
                              Text(
                                'DIGI•SAFE',
                                style: AppTheme.heading(false, size: 36, color: Colors.white).copyWith(
                                  letterSpacing: 1.5,
                                  shadows: [
                                    Shadow(color: Colors.black.withValues(alpha: 0.22), blurRadius: 16, offset: const Offset(0, 6)),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                isReturningUser
                                    ? (isEn ? 'Welcome back, $firstName!' : 'Halo lagi, $firstName!')
                                    : (isEn ? 'Purwakarta Cyber Shield\nLearn, Play & Stay Safe' : 'Perisai Siber Purwakarta\nBelajar, Bermain & Aman'),
                                textAlign: TextAlign.center,
                                style: AppTheme.body(false, size: 15, weight: FontWeight.w600, color: Colors.white.withValues(alpha: 0.92), height: 1.4),
                              ),
                            ],
                          );
                        },
                      ),
                    ),
                  ),
                ),

                // BOTTOM PILL ACTION / DAILY RETURNING LOADER
                Padding(
                  padding: const EdgeInsets.fromLTRB(30, 8, 30, 32),
                  child: Column(
                    children: [
                      AnimatedSwitcher(
                        duration: const Duration(milliseconds: 350),
                        child: isReturningUser || !_ready
                            ? Container(
                                key: const ValueKey('wave_loading'),
                                width: double.infinity,
                                height: 56,
                                padding: const EdgeInsets.symmetric(horizontal: 20),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(99),
                                  boxShadow: [
                                    BoxShadow(color: Colors.black.withValues(alpha: 0.16), blurRadius: 20, offset: const Offset(0, 8)),
                                  ],
                                ),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    const SizedBox(
                                      width: 18,
                                      height: 18,
                                      child: CircularProgressIndicator(strokeWidth: 2.3, color: AppColors.cobalt),
                                    ),
                                    const SizedBox(width: 12),
                                    Flexible(
                                      child: Text(
                                        isReturningUser
                                            ? (isEn ? 'Preparing your safe day...' : 'Melanjutkan harimu di Digi-Safe...')
                                            : state.tr('splash_connecting'),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: AppTheme.heading(false, size: 13.5, color: AppColors.cobalt),
                                      ),
                                    ),
                                  ],
                                ),
                              )
                            : SizedBox(
                                key: const ValueKey('get_started_pill'),
                                width: double.infinity,
                                height: 56,
                                child: ElevatedButton(
                                  onPressed: _goToAuthWelcome,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppColors.lime,
                                    foregroundColor: AppColors.ink,
                                    elevation: 10,
                                    shadowColor: Colors.black.withValues(alpha: 0.25),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                                  ),
                                  child: Text(
                                    state.tr('splash_cta'),
                                    style: AppTheme.heading(false, size: 15.5, color: AppColors.ink),
                                  ),
                                ),
                              ),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        'digisafepurwakarta.web.id • Live Database Sync',
                        style: AppTheme.body(false, size: 10.5, color: Colors.white.withValues(alpha: 0.72)),
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

class _ConcentricPulseWavePainter extends CustomPainter {
  final double progress;
  final bool isDark;

  const _ConcentricPulseWavePainter({required this.progress, required this.isDark});

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width * 0.5, size.height * 0.44);
    final maxR = math.max(size.width, size.height) * 0.78;
    final breath = math.sin(progress * 2 * math.pi);

    // 5 nested luminous concentric wave bands (mirrored directly from Image 2 GIF!)
    for (int i = 5; i >= 1; i--) {
      final baseRatio = i / 5.2;
      final r = maxR * baseRatio + breath * (8.0 + i * 2.5);
      final paint = Paint()
        ..color = Colors.white.withValues(alpha: (isDark ? 0.045 : 0.075) + (6 - i) * 0.01)
        ..style = PaintingStyle.fill;
      canvas.drawCircle(center, math.max(20, r), paint);
    }

    // Floating twinkling stars & crisp geometric confetti accents (star, dot, triangle, ring)
    final angle = progress * 2 * math.pi;
    final decors = [
      (x: 0.16, y: 0.23, type: 0, col: Colors.white),
      (x: 0.82, y: 0.28, type: 1, col: AppColors.lime),
      (x: 0.22, y: 0.58, type: 2, col: const Color(0xFF93C5FD)),
      (x: 0.85, y: 0.52, type: 3, col: Colors.white),
      (x: 0.30, y: 0.16, type: 3, col: AppColors.lime),
      (x: 0.72, y: 0.64, type: 0, col: Colors.white70),
    ];

    for (int idx = 0; idx < decors.length; idx++) {
      final d = decors[idx];
      final dx = d.x * size.width + math.cos(angle + idx) * 8;
      final dy = d.y * size.height + math.sin(angle + idx * 1.4) * 10;
      final p = Paint()..color = d.col.withValues(alpha: 0.85);

      if (d.type == 0) {
        _drawStar(canvas, Offset(dx, dy), 11, p);
      } else if (d.type == 1) {
        canvas.drawCircle(Offset(dx, dy), 5.5, p);
      } else if (d.type == 2) {
        final ring = Paint()
          ..color = d.col.withValues(alpha: 0.75)
          ..style = PaintingStyle.stroke
          ..strokeWidth = 2.4;
        canvas.drawCircle(Offset(dx, dy), 8, ring);
      } else {
        final tri = Path()
          ..moveTo(dx, dy - 7)
          ..lineTo(dx + 7, dy + 6)
          ..lineTo(dx - 7, dy + 6)
          ..close();
        canvas.drawPath(tri, p);
      }
    }
  }

  void _drawStar(Canvas canvas, Offset c, double outerR, Paint p) {
    final path = Path();
    final innerR = outerR * 0.45;
    for (int i = 0; i < 10; i++) {
      final r = i.isEven ? outerR : innerR;
      final a = -math.pi / 2 + i * (math.pi / 5);
      final pt = Offset(c.dx + math.cos(a) * r, c.dy + math.sin(a) * r);
      if (i == 0) {
        path.moveTo(pt.dx, pt.dy);
      } else {
        path.lineTo(pt.dx, pt.dy);
      }
    }
    path.close();
    canvas.drawPath(path, p);
  }

  @override
  bool shouldRepaint(covariant _ConcentricPulseWavePainter oldDelegate) =>
      oldDelegate.progress != progress || oldDelegate.isDark != isDark;
}
