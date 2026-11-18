import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppColors {
  AppColors._();

  static const Color sky = Color(0xFF1E88E5);
  static const Color skyLight = Color(0xFF2196F3);
  static const Color skyDeep = Color(0xFF1565C0);
  static const Color skySoft = Color(0xFFDBEAFE);
  static const Color skyTint = Color(0xFFF0F8FF);
  static const Color cobalt = Color(0xFF2D46E6);
  static const Color cobaltTint = Color(0xFFE9EEFF);

  static const Color lime = Color(0xFFC6F432);
  static const Color ink = Color(0xFF0A0A0A);
  static const Color muted = Color(0xFF6B7280);

  static const Color purpleSoft = Color(0xFFE9D5FF);
  static const Color purple = Color(0xFFA78BFA);
  static const Color purpleDeep = Color(0xFF7C5CFC);

  static const Color pastelGreen = Color(0xFFE8F5E9);
  static const Color pastelDark = Color(0xFFF1F8E9);
  static const Color emerald = Color(0xFF10B981);
  static const Color danger = Color(0xFFEF4444);
  static const Color amber = Color(0xFFF59E0B);

  // Dark surface tokens matching layouts/app.blade.php
  static const Color darkBg = Color(0xFF0B1120);
  static const Color darkSurface = Color(0xFF0F172A);
  static const Color darkCard = Color(0xFF1E293B);
  static const Color darkBorder = Color(0xFF334155);
  static const Color darkText = Color(0xFFF1F5F9);
  static const Color darkMuted = Color(0xFF94A3B8);

  // Light surface tokens
  static const Color lightBg = Color(0xFFFFFFFF);
  static const Color lightSoftBg = Color(0xFFF8FAFC);
  static const Color lightBorder = Color(0xFFE5E7EB);

  static Color cardBg(bool isDark) => isDark ? darkCard : Colors.white;
  static Color softBg(bool isDark) => isDark ? darkSurface : lightSoftBg;
  static Color scaffoldBg(bool isDark) => isDark ? darkBg : lightBg;
  static Color textPrimary(bool isDark) => isDark ? darkText : ink;
  static Color textSecondary(bool isDark) => isDark ? darkMuted : muted;
  static Color border(bool isDark) => isDark ? darkBorder : lightBorder;

  /// Menghasilkan pasangan warna pastel (background, text) selaras dengan User::getPastelClassAttribute di Laravel.
  static List<Color> pastelPairFromName(String name) {
    const pairs = [
      [Color(0xFFDBEAFE), Color(0xFF1E40AF)],
      [Color(0xFFD1FAE5), Color(0xFF065F46)],
      [Color(0xFFFEF3C7), Color(0xFF92400E)],
      [Color(0xFFFCE7F3), Color(0xFF9D174D)],
      [Color(0xFFE0F2FE), Color(0xFF0369A1)],
      [Color(0xFFECFCCB), Color(0xFF365314)],
    ];
    final hash = name.codeUnits.fold<int>(0, (acc, c) => (acc * 31 + c) & 0x7fffffff);
    return pairs[hash % pairs.length];
  }

  /// Warna badge level sesuai field `warna` di database minigame_levels.
  static List<Color> levelBadgeColors(String colorKey, bool isDark) {
    switch (colorKey) {
      case 'sky':
        return [
          isDark ? sky.withValues(alpha: 0.22) : skySoft,
          isDark ? const Color(0xFF93C5FD) : sky,
        ];
      case 'amber':
        return [
          isDark ? amber.withValues(alpha: 0.2) : const Color(0xFFFFFBEB),
          isDark ? const Color(0xFFFCD34D) : const Color(0xFFB45309),
        ];
      case 'purple':
        return [
          isDark ? purpleDeep.withValues(alpha: 0.22) : const Color(0xFFF3E8FF),
          isDark ? const Color(0xFFD8B4FE) : const Color(0xFF7E22CE),
        ];
      case 'red':
        return [
          isDark ? danger.withValues(alpha: 0.22) : const Color(0xFFFEF2F2),
          isDark ? const Color(0xFFFCA5A5) : const Color(0xFFDC2626),
        ];
      case 'lime':
        return [
          lime.withValues(alpha: isDark ? 0.25 : 0.45),
          isDark ? lime : ink,
        ];
      default:
        return [
          isDark ? emerald.withValues(alpha: 0.2) : const Color(0xFFECFDF5),
          isDark ? const Color(0xFF6EE7B7) : const Color(0xFF047857),
        ];
    }
  }
}

class AppTheme {
  AppTheme._();

  static TextStyle heading(
    bool isDark, {
    double size = 18,
    FontWeight weight = FontWeight.w800,
    Color? color,
    double? height,
  }) {
    return GoogleFonts.plusJakartaSans(
      fontSize: size,
      fontWeight: weight,
      color: color ?? AppColors.textPrimary(isDark),
      letterSpacing: -0.02 * size,
      height: height,
    );
  }

  static TextStyle body(
    bool isDark, {
    double size = 13,
    FontWeight weight = FontWeight.w400,
    Color? color,
    double? height,
  }) {
    return GoogleFonts.inter(
      fontSize: size,
      fontWeight: weight,
      color: color ?? AppColors.textPrimary(isDark),
      height: height,
    );
  }

  static ThemeData build(bool isDark) {
    final base = isDark ? ThemeData.dark() : ThemeData.light();
    return base.copyWith(
      scaffoldBackgroundColor: AppColors.scaffoldBg(isDark),
      colorScheme: ColorScheme(
        brightness: isDark ? Brightness.dark : Brightness.light,
        primary: AppColors.sky,
        onPrimary: Colors.white,
        secondary: AppColors.lime,
        onSecondary: AppColors.ink,
        error: AppColors.danger,
        onError: Colors.white,
        surface: AppColors.cardBg(isDark),
        onSurface: AppColors.textPrimary(isDark),
      ),
      textTheme: GoogleFonts.interTextTheme(base.textTheme).apply(
        bodyColor: AppColors.textPrimary(isDark),
        displayColor: AppColors.textPrimary(isDark),
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: AppColors.cardBg(isDark),
        foregroundColor: AppColors.textPrimary(isDark),
        elevation: 0,
        surfaceTintColor: Colors.transparent,
      ),
      cardColor: AppColors.cardBg(isDark),
      dividerColor: AppColors.border(isDark),
    );
  }
}
