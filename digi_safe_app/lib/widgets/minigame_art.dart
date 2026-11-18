import 'package:flutter/material.dart';

import '../core/theme.dart';

class MinigameHeaderArt extends StatelessWidget {
  final String slug; // phishing, cyber-defender, link-trap
  final bool isDark;

  const MinigameHeaderArt({
    super.key,
    required this.slug,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 150,
      width: double.infinity,
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          Positioned.fill(
            child: ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
              child: CustomPaint(
                painter: _MinigameBackdropPainter(
                  slug: slug,
                  waveColor: isDark ? AppColors.darkCard : Colors.white,
                ),
              ),
            ),
          ),
          if (slug == 'phishing')
            Positioned(
              top: -16,
              right: 12,
              child: SizedBox(
                width: 96,
                height: 74,
                child: CustomPaint(painter: _UfoCraftPainter()),
              ),
            )
          else if (slug == 'cyber-defender')
            Positioned(
              top: -22,
              left: 0,
              right: 0,
              child: Center(
                child: SizedBox(
                  width: 82,
                  height: 124,
                  child: CustomPaint(painter: _RocketCraftPainter()),
                ),
              ),
            )
          else
            Positioned(
              top: -18,
              left: 16,
              child: SizedBox(
                width: 96,
                height: 84,
                child: CustomPaint(painter: _SatelliteCraftPainter()),
              ),
            ),
        ],
      ),
    );
  }
}

class _MinigameBackdropPainter extends CustomPainter {
  final String slug;
  final Color waveColor;

  _MinigameBackdropPainter({required this.slug, required this.waveColor});

  @override
  void paint(Canvas canvas, Size size) {
    final rect = Rect.fromLTWH(0, 0, size.width, size.height);

    Gradient bgGrad;
    if (slug == 'phishing') {
      bgGrad = const LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [Color(0xFF0047AB), Color(0xFF1E88E5)],
      );
    } else if (slug == 'cyber-defender') {
      bgGrad = const LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: [Color(0xFF0B154B), Color(0xFF172554)],
      );
    } else {
      bgGrad = const LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [Color(0xFF0369A1), Color(0xFF0EA5E9)],
      );
    }

    canvas.drawRect(rect, Paint()..shader = bgGrad.createShader(rect));

    if (slug == 'phishing') {
      // Sorot cahaya UFO
      final beamPath = Path()
        ..moveTo(size.width * 0.78, 16)
        ..lineTo(size.width * 0.34, size.height)
        ..lineTo(size.width * 0.88, size.height)
        ..close();
      canvas.drawPath(
        beamPath,
        Paint()
          ..shader = const LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Color(0x88FFFFFF), Color(0x14DBEAFE)],
          ).createShader(rect),
      );

      // Awan putih bergelombang dari kiri atas ke kanan bawah
      final cloudPath = Path()
        ..moveTo(0, size.height * 0.34)
        ..quadraticBezierTo(size.width * 0.14, size.height * 0.25, size.width * 0.22, size.height * 0.45)
        ..quadraticBezierTo(size.width * 0.34, size.height * 0.38, size.width * 0.44, size.height * 0.60)
        ..quadraticBezierTo(size.width * 0.58, size.height * 0.52, size.width * 0.72, size.height * 0.75)
        ..quadraticBezierTo(size.width * 0.86, size.height * 0.66, size.width, size.height * 0.78)
        ..lineTo(size.width, size.height + 2)
        ..lineTo(0, size.height + 2)
        ..close();
      canvas.drawPath(cloudPath, Paint()..color = waveColor);
    } else if (slug == 'cyber-defender') {
      // Kubah planet biru
      final planetPath = Path()
        ..moveTo(size.width * 0.08, size.height)
        ..cubicTo(
          size.width * 0.24,
          size.height * 0.32,
          size.width * 0.76,
          size.height * 0.32,
          size.width * 0.92,
          size.height,
        )
        ..close();
      canvas.drawPath(planetPath, Paint()..color = AppColors.sky);

      // Kepulan awan putih di tengah bawah
      final puffPath = Path()
        ..moveTo(size.width * 0.18, size.height)
        ..quadraticBezierTo(size.width * 0.28, size.height * 0.70, size.width * 0.38, size.height * 0.78)
        ..quadraticBezierTo(size.width * 0.50, size.height * 0.55, size.width * 0.62, size.height * 0.78)
        ..quadraticBezierTo(size.width * 0.72, size.height * 0.70, size.width * 0.82, size.height)
        ..close();
      canvas.drawPath(puffPath, Paint()..color = waveColor);
      canvas.drawRect(
        Rect.fromLTWH(0, size.height - 6, size.width, 8),
        Paint()..color = waveColor,
      );
    } else {
      // Gelombang radar sinyal satelit
      final ringPaint = Paint()
        ..color = AppColors.lime.withValues(alpha: 0.42)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2;
      canvas.drawCircle(Offset(size.width * 0.28, 22), 38, ringPaint);
      canvas.drawCircle(
        Offset(size.width * 0.28, 22),
        66,
        Paint()
          ..color = Colors.white.withValues(alpha: 0.28)
          ..style = PaintingStyle.stroke
          ..strokeWidth = 1.5,
      );

      final scanBeam = Path()
        ..moveTo(size.width * 0.28, 22)
        ..lineTo(size.width * 0.12, size.height)
        ..lineTo(size.width * 0.60, size.height)
        ..close();
      canvas.drawPath(scanBeam, Paint()..color = AppColors.lime.withValues(alpha: 0.16));

      final cloudPath = Path()
        ..moveTo(size.width, size.height * 0.34)
        ..quadraticBezierTo(size.width * 0.84, size.height * 0.28, size.width * 0.74, size.height * 0.50)
        ..quadraticBezierTo(size.width * 0.60, size.height * 0.42, size.width * 0.48, size.height * 0.66)
        ..quadraticBezierTo(size.width * 0.28, size.height * 0.58, 0, size.height * 0.80)
        ..lineTo(0, size.height + 2)
        ..lineTo(size.width, size.height + 2)
        ..close();
      canvas.drawPath(cloudPath, Paint()..color = waveColor);
    }
  }

  @override
  bool shouldRepaint(covariant _MinigameBackdropPainter oldDelegate) =>
      oldDelegate.slug != slug || oldDelegate.waveColor != waveColor;
}

class _UfoCraftPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    canvas.save();
    canvas.translate(size.width / 2, size.height / 2);
    canvas.rotate(0.24);
    canvas.translate(-size.width / 2, -size.height / 2);

    // Kubah kaca
    final domeRect = Rect.fromCenter(center: Offset(size.width * 0.5, size.height * 0.42), width: 38, height: 28);
    canvas.drawArc(domeRect, 3.14, 3.14, true, Paint()..color = const Color(0xFF93C5FD));
    canvas.drawArc(
      domeRect,
      3.14,
      3.14,
      true,
      Paint()
        ..color = const Color(0xFF1E3A8A)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2,
    );

    // Badan piringan
    final saucer = Rect.fromCenter(center: Offset(size.width * 0.5, size.height * 0.54), width: 78, height: 24);
    canvas.drawOval(saucer, Paint()..color = const Color(0xFF60A5FA));
    canvas.drawOval(
      saucer,
      Paint()
        ..color = const Color(0xFF1E3A8A)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2,
    );

    canvas.drawOval(
      Rect.fromCenter(center: Offset(size.width * 0.5, size.height * 0.58), width: 54, height: 14),
      Paint()..color = const Color(0xFF1D4ED8),
    );
    canvas.drawOval(
      Rect.fromCenter(center: Offset(size.width * 0.5, size.height * 0.59), width: 34, height: 9),
      Paint()..color = const Color(0xFFFEF9C3),
    );

    canvas.restore();
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _RocketCraftPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;

    // Api roket
    final flameOuter = Path()
      ..moveTo(w * 0.42, h * 0.72)
      ..lineTo(w * 0.50, h * 0.96)
      ..lineTo(w * 0.58, h * 0.72)
      ..close();
    canvas.drawPath(flameOuter, Paint()..color = const Color(0xFFF97316));

    final flameInner = Path()
      ..moveTo(w * 0.45, h * 0.72)
      ..lineTo(w * 0.50, h * 0.87)
      ..lineTo(w * 0.55, h * 0.72)
      ..close();
    canvas.drawPath(flameInner, Paint()..color = const Color(0xFFFACC15));

    // Sirip kiri & kanan
    final leftFin = Path()
      ..moveTo(w * 0.34, h * 0.52)
      ..lineTo(w * 0.16, h * 0.70)
      ..lineTo(w * 0.36, h * 0.67)
      ..close();
    canvas.drawPath(leftFin, Paint()..color = const Color(0xFFF97316));

    final rightFin = Path()
      ..moveTo(w * 0.66, h * 0.52)
      ..lineTo(w * 0.84, h * 0.70)
      ..lineTo(w * 0.64, h * 0.67)
      ..close();
    canvas.drawPath(rightFin, Paint()..color = const Color(0xFFF97316));

    // Badan roket putih
    final body = Path()
      ..moveTo(w * 0.50, h * 0.06)
      ..cubicTo(w * 0.30, h * 0.24, w * 0.30, h * 0.52, w * 0.36, h * 0.72)
      ..lineTo(w * 0.64, h * 0.72)
      ..cubicTo(w * 0.70, h * 0.52, w * 0.70, h * 0.24, w * 0.50, h * 0.06)
      ..close();
    canvas.drawPath(body, Paint()..color = Colors.white);
    canvas.drawPath(
      body,
      Paint()
        ..color = const Color(0xFFE2E8F0)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 1.5,
    );

    // Ujung hidung kuning
    final nose = Path()
      ..moveTo(w * 0.50, h * 0.06)
      ..lineTo(w * 0.38, h * 0.22)
      ..lineTo(w * 0.62, h * 0.22)
      ..close();
    canvas.drawPath(nose, Paint()..color = const Color(0xFFFACC15));

    // Jendela bulat
    canvas.drawCircle(Offset(w * 0.50, h * 0.38), 8.5, Paint()..color = const Color(0xFF93C5FD));
    canvas.drawCircle(
      Offset(w * 0.50, h * 0.38),
      8.5,
      Paint()
        ..color = const Color(0xFFF97316)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2.4,
    );

    // Sirip tengah
    canvas.drawRRect(
      RRect.fromRectAndRadius(Rect.fromLTWH(w * 0.47, h * 0.54, w * 0.06, h * 0.20), const Radius.circular(3)),
      Paint()..color = const Color(0xFFEA580C),
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _SatelliteCraftPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    canvas.save();
    canvas.translate(size.width / 2, size.height / 2);
    canvas.rotate(-0.22);
    canvas.translate(-size.width / 2, -size.height / 2);

    final stroke = Paint()
      ..color = const Color(0xFF0F172A)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2;

    final leftWing = RRect.fromRectAndRadius(const Rect.fromLTWH(8, 32, 22, 16), const Radius.circular(3));
    final rightWing = RRect.fromRectAndRadius(const Rect.fromLTWH(66, 32, 22, 16), const Radius.circular(3));
    canvas.drawRRect(leftWing, Paint()..color = const Color(0xFF38BDF8));
    canvas.drawRRect(leftWing, stroke);
    canvas.drawRRect(rightWing, Paint()..color = const Color(0xFF38BDF8));
    canvas.drawRRect(rightWing, stroke);

    canvas.drawLine(const Offset(30, 40), const Offset(34, 40), stroke..strokeWidth = 2.5);
    canvas.drawLine(const Offset(62, 40), const Offset(66, 40), stroke..strokeWidth = 2.5);

    final core = RRect.fromRectAndRadius(const Rect.fromLTWH(34, 24, 28, 32), const Radius.circular(7));
    canvas.drawRRect(core, Paint()..color = Colors.white);
    canvas.drawRRect(core, stroke..strokeWidth = 2);

    canvas.drawCircle(const Offset(48, 40), 8, Paint()..color = AppColors.lime);
    canvas.drawCircle(const Offset(48, 40), 8, stroke);
    canvas.drawCircle(const Offset(48, 40), 3, Paint()..color = const Color(0xFF0F172A));

    canvas.drawLine(const Offset(48, 56), const Offset(48, 67), stroke);
    canvas.drawCircle(const Offset(48, 69), 3.5, Paint()..color = const Color(0xFFF97316));

    canvas.restore();
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

