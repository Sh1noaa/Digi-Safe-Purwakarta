import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../core/theme.dart';

class UserAvatarWidget extends StatelessWidget {
  final String? avatarUrl;
  final String initial;
  final String nameSeed;
  final bool isOwner;
  final bool isBot;
  final double size;
  final Color? ringColor;

  const UserAvatarWidget({
    super.key,
    this.avatarUrl,
    required this.initial,
    required this.nameSeed,
    this.isOwner = false,
    this.isBot = false,
    this.size = 36,
    this.ringColor,
  });

  @override
  Widget build(BuildContext context) {
    final pair = AppColors.pastelPairFromName(nameSeed);
    final bg = isOwner ? AppColors.ink : pair[0];
    final fg = isOwner ? AppColors.lime : pair[1];

    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: isBot ? Colors.white : bg,
        border: ringColor != null ? Border.all(color: ringColor!, width: 2.2) : null,
      ),
      clipBehavior: Clip.antiAlias,
      child: isBot
          ? Image.asset(
              'assets/images/bot-digika.jpg',
              fit: BoxFit.cover,
              errorBuilder: (_, _, _) => _fallbackText(fg),
            )
          : (avatarUrl != null && avatarUrl!.isNotEmpty)
              ? Image.network(
                  avatarUrl!,
                  fit: BoxFit.cover,
                  errorBuilder: (_, _, _) => _fallbackText(fg),
                )
              : _fallbackText(fg),
    );
  }

  Widget _fallbackText(Color fg) {
    return Center(
      child: Text(
        initial.isEmpty ? 'U' : initial[0].toUpperCase(),
        style: TextStyle(
          color: fg,
          fontWeight: FontWeight.w800,
          fontSize: size * 0.38,
        ),
      ),
    );
  }
}

class RoleBadgeWidget extends StatelessWidget {
  final bool isOwner;
  final bool isAdmin;
  final bool compact;

  const RoleBadgeWidget({
    super.key,
    required this.isOwner,
    required this.isAdmin,
    this.compact = false,
  });

  @override
  Widget build(BuildContext context) {
    if (!isOwner && !isAdmin) return const SizedBox.shrink();

    if (isOwner) {
      return Container(
        padding: EdgeInsets.symmetric(horizontal: compact ? 7 : 9, vertical: 3),
        decoration: BoxDecoration(
          color: AppColors.ink,
          borderRadius: BorderRadius.circular(99),
          border: Border.all(color: AppColors.lime.withValues(alpha: 0.45)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.workspace_premium_rounded, color: AppColors.lime, size: compact ? 10 : 12),
            const SizedBox(width: 3),
            Text(
              'Owner',
              style: TextStyle(
                color: AppColors.lime,
                fontSize: compact ? 9 : 10,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ),
      );
    }

    return Container(
      padding: EdgeInsets.symmetric(horizontal: compact ? 7 : 9, vertical: 3),
      decoration: BoxDecoration(
        color: AppColors.lime,
        borderRadius: BorderRadius.circular(99),
      ),
      child: Text(
        'Admin',
        style: TextStyle(
          color: AppColors.ink,
          fontSize: compact ? 9 : 10,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class SpecialtyRoleOption {
  final String value;
  final String descId;
  final String descEn;
  final IconData icon;
  final Color color;

  const SpecialtyRoleOption({
    required this.value,
    required this.descId,
    required this.descEn,
    required this.icon,
    required this.color,
  });

  static const List<SpecialtyRoleOption> all = [
    SpecialtyRoleOption(
      value: 'Cyber Guardian',
      descId: 'Pelindung ruang digital & anti-penipuan',
      descEn: 'Digital safety & anti-scam protector',
      icon: Icons.shield_rounded,
      color: Color(0xFF1E88E5),
    ),
    SpecialtyRoleOption(
      value: 'Digital Educator',
      descId: 'Pelajar, guru & penggerak literasi',
      descEn: 'Student, teacher & literacy advocate',
      icon: Icons.menu_book_rounded,
      color: Color(0xFF10B981),
    ),
    SpecialtyRoleOption(
      value: 'Phishing Hunter',
      descId: 'Detektif tautan & analis ancaman',
      descEn: 'Link inspector & threat analyst',
      icon: Icons.radar_rounded,
      color: Color(0xFFF59E0B),
    ),
    SpecialtyRoleOption(
      value: 'Tech Explorer',
      descId: 'Developer, kreator & penggiat teknologi',
      descEn: 'Developer, creator & IT enthusiast',
      icon: Icons.terminal_rounded,
      color: Color(0xFF7C3AED),
    ),
  ];

  static SpecialtyRoleOption? find(String? val) {
    if (val == null || val.trim().isEmpty) return null;
    for (final opt in all) {
      if (opt.value.toLowerCase() == val.trim().toLowerCase()) return opt;
    }
    return null;
  }
}

class SpecialtyRoleChip extends StatelessWidget {
  final String? specialtyRole;
  final bool isDark;
  final bool compact;

  const SpecialtyRoleChip({
    super.key,
    required this.specialtyRole,
    required this.isDark,
    this.compact = false,
  });

  @override
  Widget build(BuildContext context) {
    if (specialtyRole == null || specialtyRole!.trim().isEmpty) {
      return const SizedBox.shrink();
    }
    final opt = SpecialtyRoleOption.find(specialtyRole);
    final accent = opt?.color ?? AppColors.sky;
    final icon = opt?.icon ?? Icons.auto_awesome_rounded;

    return Container(
      padding: EdgeInsets.symmetric(horizontal: compact ? 8 : 10, vertical: compact ? 3 : 4),
      decoration: BoxDecoration(
        color: accent.withValues(alpha: isDark ? 0.22 : 0.11),
        borderRadius: BorderRadius.circular(99),
        border: Border.all(color: accent.withValues(alpha: 0.35)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: compact ? 10.5 : 12, color: accent),
          const SizedBox(width: 4),
          Text(
            specialtyRole!,
            style: TextStyle(
              color: isDark ? Colors.white : accent,
              fontSize: compact ? 9.5 : 10.5,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}

class GoogleGMark extends StatelessWidget {
  final double size;

  const GoogleGMark({super.key, this.size = 20});

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      size: Size.square(size),
      painter: _GoogleGPainter(),
    );
  }
}

class _GoogleGPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;
    final strokeW = w * 0.21;
    final rect = Rect.fromLTWH(strokeW / 2, strokeW / 2, w - strokeW, h - strokeW);

    final paint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeW
      ..strokeCap = StrokeCap.butt;

    // Red top arc
    paint.color = const Color(0xFFEA4335);
    canvas.drawArc(rect, -2.55, 1.85, false, paint);

    // Yellow left arc
    paint.color = const Color(0xFFFBBC05);
    canvas.drawArc(rect, 2.38, 1.38, false, paint);

    // Green bottom arc
    paint.color = const Color(0xFF34A853);
    canvas.drawArc(rect, 0.72, 1.72, false, paint);

    // Blue right arc + crossbar
    paint.color = const Color(0xFF4285F4);
    canvas.drawArc(rect, -0.10, 0.86, false, paint);

    final barPaint = Paint()
      ..color = const Color(0xFF4285F4)
      ..style = PaintingStyle.fill;
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromLTWH(w * 0.50, h * 0.42, w * 0.46, strokeW * 0.92),
        const Radius.circular(2),
      ),
      barPaint,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class DigiSectionKicker extends StatelessWidget {
  final String text;
  final bool isDark;
  final Color? textColor;

  const DigiSectionKicker({
    super.key,
    required this.text,
    required this.isDark,
    this.textColor,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        const Text(
          '• ',
          style: TextStyle(color: AppColors.sky, fontWeight: FontWeight.w900, fontSize: 13),
        ),
        Text(
          text.toUpperCase(),
          style: AppTheme.heading(
            isDark,
            size: 10.5,
            weight: FontWeight.w700,
            color: textColor ?? AppColors.textSecondary(isDark),
          ).copyWith(letterSpacing: 1.8),
        ),
      ],
    );
  }
}

/// Animasi kawanan burung berterbangan di atas hero langit Beranda (identik dengan SVG beranda web).
class FlyingBirdsOverlay extends StatefulWidget {
  const FlyingBirdsOverlay({super.key});

  @override
  State<FlyingBirdsOverlay> createState() => _FlyingBirdsOverlayState();
}

class _FlyingBirdsOverlayState extends State<FlyingBirdsOverlay> with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 24),
    )..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: AnimatedBuilder(
        animation: _controller,
        builder: (context, _) {
          return CustomPaint(
            painter: _BirdsPainter(_controller.value),
            size: Size.infinite,
          );
        },
      ),
    );
  }
}

class _BirdsPainter extends CustomPainter {
  final double progress;
  _BirdsPainter(this.progress);

  @override
  void paint(Canvas canvas, Size size) {
    final flocks = [
      (baseX: 0.15, baseY: 0.22, speed: 1.0, scale: 1.0, alpha: 0.85, count: 3),
      (baseX: 0.55, baseY: 0.14, speed: 1.35, scale: 0.85, alpha: 0.72, count: 3),
      (baseX: 0.75, baseY: 0.34, speed: 0.8, scale: 0.78, alpha: 0.65, count: 2),
      (baseX: 0.32, baseY: 0.42, speed: 1.15, scale: 0.72, alpha: 0.58, count: 2),
    ];

    for (var i = 0; i < flocks.length; i++) {
      final f = flocks[i];
      final xNorm = (f.baseX + progress * f.speed) % 1.25 - 0.12;
      final cx = xNorm * size.width;
      final cy = f.baseY * size.height + math.sin((progress * 2 * math.pi) + i) * 7;

      for (var b = 0; b < f.count; b++) {
        final bx = cx - b * 24 * f.scale;
        final by = cy + (b == 1 ? -10 : b == 2 ? 9 : 0) * f.scale;
        final flap = math.sin(progress * 18 * math.pi + b * 1.4 + i) * 4.5 * f.scale;

        final paint = Paint()
          ..color = Colors.white.withValues(alpha: (f.alpha - b * 0.1).clamp(0.1, 1.0))
          ..style = PaintingStyle.stroke
          ..strokeWidth = 1.8 * f.scale
          ..strokeCap = StrokeCap.round;

        final path = Path()
          ..moveTo(bx - 12 * f.scale, by + flap)
          ..quadraticBezierTo(bx - 6 * f.scale, by - 6 * f.scale, bx, by)
          ..quadraticBezierTo(bx + 6 * f.scale, by - 6 * f.scale, bx + 12 * f.scale, by + flap);

        canvas.drawPath(path, paint);
      }
    }
  }

  @override
  bool shouldRepaint(covariant _BirdsPainter oldDelegate) => oldDelegate.progress != progress;
}
