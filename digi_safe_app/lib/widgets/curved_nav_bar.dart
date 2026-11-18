import 'package:flutter/material.dart';

import '../core/theme.dart';

class CurvedNavItem {
  final String label;
  final IconData activeIcon;
  final IconData inactiveIcon;

  const CurvedNavItem({
    required this.label,
    required this.activeIcon,
    required this.inactiveIcon,
  });
}

/// Mobile Navigation Bar dengan efek Curved Concave Notch + Floating Ringed Orb (khusus Mobile APK).
class CurvedNotchBottomBar extends StatelessWidget {
  final List<CurvedNavItem> items;
  final int currentIndex;
  final bool isDark;
  final ValueChanged<int> onTap;

  const CurvedNotchBottomBar({
    super.key,
    required this.items,
    required this.currentIndex,
    required this.isDark,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).padding.bottom;
    const double barHeight = 68;
    const double orbTopOverflow = 24;
    const double totalHeight = barHeight + orbTopOverflow;

    final accentRing = isDark ? AppColors.skyLight : AppColors.cobalt;
    final barBg = isDark ? const Color(0xFF0F172A) : Colors.white;
    final orbFill = isDark ? const Color(0xFF1E293B) : Colors.white;
    final innerTint = isDark ? AppColors.cobalt.withValues(alpha: 0.28) : AppColors.cobaltTint;
    final shadowColor = isDark
        ? Colors.black.withValues(alpha: 0.45)
        : const Color(0xFF1E3A8A).withValues(alpha: 0.14);

    return Container(
      padding: EdgeInsets.fromLTRB(12, 0, 12, bottomInset > 0 ? bottomInset : 10),
      color: Colors.transparent,
      child: TweenAnimationBuilder<double>(
        tween: Tween<double>(end: currentIndex.toDouble()),
        duration: const Duration(milliseconds: 320),
        curve: Curves.easeOutCubic,
        builder: (context, animatedIndex, _) {
          return LayoutBuilder(
            builder: (context, constraints) {
              final width = constraints.maxWidth;
              final itemWidth = width / items.length;
              final rawCenter = (animatedIndex + 0.5) * itemWidth;
              const double orbSize = 54;
              const double edgePad = 14;
              final clampedOrbLeft = (rawCenter - orbSize / 2).clamp(edgePad * 0.4, width - orbSize - edgePad * 0.4);

              return SizedBox(
                height: totalHeight,
                child: Stack(
                  clipBehavior: Clip.none,
                  children: [
                    // 1. Curved Notched White/Dark Pill Surface + Shadow
                    Positioned(
                      left: 0,
                      right: 0,
                      top: orbTopOverflow,
                      height: barHeight,
                      child: CustomPaint(
                        painter: _CurvedNotchBarPainter(
                          notchCenterX: rawCenter,
                          barColor: barBg,
                          borderColor: isDark ? AppColors.darkBorder : const Color(0xFFE8EEF8),
                          shadowColor: shadowColor,
                        ),
                      ),
                    ),

                    // 2. Floating Ringed Orb inside the Notch Cradle
                    Positioned(
                      top: 2,
                      left: clampedOrbLeft,
                      child: IgnorePointer(
                        child: Container(
                          width: orbSize,
                          height: orbSize,
                          decoration: BoxDecoration(
                            color: orbFill,
                            shape: BoxShape.circle,
                            border: Border.all(color: accentRing, width: 2.6),
                            boxShadow: [
                              BoxShadow(
                                color: accentRing.withValues(alpha: isDark ? 0.36 : 0.24),
                                blurRadius: 14,
                                offset: const Offset(0, 5),
                              ),
                            ],
                          ),
                          alignment: Alignment.center,
                          child: Container(
                            width: 38,
                            height: 38,
                            decoration: BoxDecoration(
                              color: innerTint,
                              shape: BoxShape.circle,
                            ),
                            child: Icon(
                              items[currentIndex].activeIcon,
                              size: 21,
                              color: isDark ? Colors.white : accentRing,
                            ),
                          ),
                        ),
                      ),
                    ),

                    // 3. Interactive Columns (Outline Icons + Bottom Labels)
                    Positioned(
                      left: 0,
                      right: 0,
                      top: orbTopOverflow,
                      height: barHeight,
                      child: Row(
                        children: List.generate(items.length, (i) {
                          final item = items[i];
                          final active = i == currentIndex;
                          return Expanded(
                            child: GestureDetector(
                              behavior: HitTestBehavior.opaque,
                              onTap: () => onTap(i),
                              child: Padding(
                                padding: const EdgeInsets.only(top: 10, bottom: 8),
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.end,
                                  children: [
                                    if (!active)
                                      Padding(
                                        padding: const EdgeInsets.only(bottom: 3),
                                        child: Icon(
                                          item.inactiveIcon,
                                          size: 21,
                                          color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                                        ),
                                      )
                                    else
                                      const SizedBox(height: 24),
                                    Text(
                                      item.label,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: AppTheme.heading(
                                        isDark,
                                        size: active ? 11 : 10,
                                        weight: active ? FontWeight.w800 : FontWeight.w600,
                                        color: active
                                            ? AppColors.textPrimary(isDark)
                                            : (isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        }),
                      ),
                    ),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }
}

class _CurvedNotchBarPainter extends CustomPainter {
  final double notchCenterX;
  final Color barColor;
  final Color borderColor;
  final Color shadowColor;

  const _CurvedNotchBarPainter({
    required this.notchCenterX,
    required this.barColor,
    required this.borderColor,
    required this.shadowColor,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;
    const double r = 24;
    const double notchHalf = 35;
    const double notchDepth = 27;

    // Clamping center cleanly so even index 0 and index 4 retain smooth outer rounding
    final cx = notchCenterX.clamp(r + notchHalf * 0.55, w - r - notchHalf * 0.55);
    final leftStart = (cx - notchHalf).clamp(r * 0.65, w);
    final rightEnd = (cx + notchHalf).clamp(0.0, w - r * 0.65);

    final path = Path()..moveTo(0, r);

    if (leftStart > r + 4) {
      path.quadraticBezierTo(0, 0, r, 0);
      path.lineTo(leftStart, 0);
    } else {
      path.quadraticBezierTo(0, 0, leftStart, 0);
    }

    // Smooth concave dip into U-cradle
    path.cubicTo(
      leftStart + (cx - leftStart) * 0.45,
      0,
      cx - (cx - leftStart) * 0.58,
      notchDepth,
      cx,
      notchDepth,
    );
    path.cubicTo(
      cx + (rightEnd - cx) * 0.58,
      notchDepth,
      rightEnd - (rightEnd - cx) * 0.45,
      0,
      rightEnd,
      0,
    );

    if (rightEnd < w - r - 4) {
      path.lineTo(w - r, 0);
      path.quadraticBezierTo(w, 0, w, r);
    } else {
      path.quadraticBezierTo(w, 0, w, r);
    }

    path.lineTo(w, h - r);
    path.quadraticBezierTo(w, h, w - r, h);
    path.lineTo(r, h);
    path.quadraticBezierTo(0, h, 0, h - r);
    path.close();

    // Soft elevated ambient shadow
    canvas.drawShadow(path, shadowColor, 16, true);

    final fillPaint = Paint()
      ..color = barColor
      ..style = PaintingStyle.fill;
    canvas.drawPath(path, fillPaint);

    final borderPaint = Paint()
      ..color = borderColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.0;
    canvas.drawPath(path, borderPaint);
  }

  @override
  bool shouldRepaint(covariant _CurvedNotchBarPainter oldDelegate) {
    return oldDelegate.notchCenterX != notchCenterX ||
        oldDelegate.barColor != barColor ||
        oldDelegate.borderColor != borderColor;
  }
}
