import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../core/app_state.dart';
import '../core/theme.dart';

class PasswordCheckerScreen extends StatefulWidget {
  final AppState appState;

  const PasswordCheckerScreen({super.key, required this.appState});

  @override
  State<PasswordCheckerScreen> createState() => _PasswordCheckerScreenState();
}

class _PasswordCheckerScreenState extends State<PasswordCheckerScreen> {
  final _ctrl = TextEditingController();
  bool _visible = true;

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  _PwAnalysis _analyze(String pw) {
    final isEn = widget.appState.isEn;
    if (pw.isEmpty) {
      return _PwAnalysis(
        score: 0,
        label: isEn ? 'Waiting for input...' : 'Menunggu input password...',
        crackTime: '-',
        color: AppColors.muted,
        hasMin8: false,
        hasMin12: false,
        hasUpper: false,
        hasLower: false,
        hasDigit: false,
        hasSymbol: false,
        notCommon: true,
      );
    }

    final hasMin8 = pw.length >= 8;
    final hasMin12 = pw.length >= 12;
    final hasUpper = RegExp(r'[A-Z]').hasMatch(pw);
    final hasLower = RegExp(r'[a-z]').hasMatch(pw);
    final hasDigit = RegExp(r'[0-9]').hasMatch(pw);
    final hasSymbol = RegExp(r'[^a-zA-Z0-9]').hasMatch(pw);

    final low = pw.toLowerCase();
    const badWords = ['123456', 'password', 'qwerty', 'admin', 'purwakarta', 'rahasiaku', 'abc123', 'digisafe'];
    final notCommon = !badWords.any((w) => low.contains(w));

    int charset = 0;
    if (hasLower) charset += 26;
    if (hasUpper) charset += 26;
    if (hasDigit) charset += 10;
    if (hasSymbol) charset += 32;
    charset = math.max(10, charset);

    final entropy = pw.length * (math.log(charset) / math.ln2);
    int score = 0;
    if (hasMin8) score += 20;
    if (hasMin12) score += 15;
    if (hasUpper) score += 15;
    if (hasLower) score += 10;
    if (hasDigit) score += 15;
    if (hasSymbol) score += 15;
    if (notCommon) score += 10;
    if (!notCommon) score = math.min(score, 25);
    score = score.clamp(0, 100);

    String crackTime;
    if (!notCommon || entropy < 28) {
      crackTime = isEn ? 'Instantly (< 1 sec)' : 'Seketika (< 1 detik)';
    } else if (entropy < 45) {
      crackTime = isEn ? 'A few minutes to hours' : 'Menit – beberapa jam';
    } else if (entropy < 62) {
      crackTime = isEn ? 'Several months to years' : 'Beberapa bulan – tahun';
    } else if (entropy < 80) {
      crackTime = isEn ? 'Hundreds of years' : 'Ratusan tahun';
    } else {
      crackTime = isEn ? 'Millions of centuries+' : 'Jutaan abad+';
    }

    String label;
    Color col;
    if (score < 35) {
      label = isEn ? 'Very Weak — Vulnerable!' : 'Sangat Lemah — Mudah Dibobol!';
      col = AppColors.danger;
    } else if (score < 60) {
      label = isEn ? 'Fair — Needs Improvement' : 'Cukup — Perlu Ditingkatkan';
      col = AppColors.amber;
    } else if (score < 85) {
      label = isEn ? 'Strong Password' : 'Password Kuat';
      col = AppColors.sky;
    } else {
      label = isEn ? 'Cyber Fortress Level!' : 'Sangat Tangguh (Benteng Siber)!';
      col = AppColors.emerald;
    }

    return _PwAnalysis(
      score: score,
      label: label,
      crackTime: crackTime,
      color: col,
      hasMin8: hasMin8,
      hasMin12: hasMin12,
      hasUpper: hasUpper,
      hasLower: hasLower,
      hasDigit: hasDigit,
      hasSymbol: hasSymbol,
      notCommon: notCommon,
    );
  }

  void _generateSample() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#\$%&*';
    final rand = math.Random.secure();
    final pw = List.generate(14, (_) => chars[rand.nextInt(chars.length)]).join();
    setState(() => _ctrl.text = pw);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = widget.appState.isDark;
    final isEn = widget.appState.isEn;
    final a = _analyze(_ctrl.text);

    return Scaffold(
      backgroundColor: AppColors.softBg(isDark),
      appBar: AppBar(
        title: Text(widget.appState.tr('pw_checker_title'), style: AppTheme.heading(isDark, size: 16)),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 14, 16, 36),
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: AppColors.cardBg(isDark),
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: AppColors.border(isDark)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(color: AppColors.lime, borderRadius: BorderRadius.circular(14)),
                      child: const Icon(Icons.password_rounded, color: AppColors.ink, size: 22),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isEn ? 'LOCAL CLIENT-SIDE CHECK' : 'PENGECEKAN 100% LOKAL',
                            style: AppTheme.heading(isDark, size: 10, color: AppColors.sky),
                          ),
                          Text(
                            isEn ? 'Your test password is never sent to any server.' : 'Password uji coba tidak pernah dikirim ke server.',
                            style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark)),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 18),
                TextField(
                  controller: _ctrl,
                  obscureText: !_visible,
                  onChanged: (_) => setState(() {}),
                  decoration: InputDecoration(
                    hintText: isEn ? 'Type a password to test...' : 'Ketik kombinasi password untuk diuji...',
                    prefixIcon: const Icon(Icons.vpn_key_outlined),
                    suffixIcon: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        IconButton(
                          icon: Icon(_visible ? Icons.visibility_off_rounded : Icons.visibility_rounded),
                          onPressed: () => setState(() => _visible = !_visible),
                        ),
                        if (_ctrl.text.isNotEmpty)
                          IconButton(
                            icon: const Icon(Icons.close_rounded),
                            onPressed: () => setState(() => _ctrl.clear()),
                          ),
                      ],
                    ),
                    filled: true,
                    fillColor: AppColors.softBg(isDark),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(18), borderSide: BorderSide(color: AppColors.border(isDark))),
                  ),
                ),
                const SizedBox(height: 14),
                ClipRRect(
                  borderRadius: BorderRadius.circular(99),
                  child: LinearProgressIndicator(
                    value: a.score / 100,
                    minHeight: 10,
                    backgroundColor: AppColors.border(isDark),
                    color: a.color,
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(child: Text(a.label, style: AppTheme.heading(isDark, size: 13.5, color: a.color))),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: a.color.withValues(alpha: 0.14),
                        borderRadius: BorderRadius.circular(99),
                      ),
                      child: Text('${a.score}/100', style: AppTheme.heading(false, size: 11.5, color: a.color)),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  '${isEn ? 'Estimated brute-force time:' : 'Estimasi waktu bobol brute-force:'} ${a.crackTime}',
                  style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark)),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: _generateSample,
                    icon: const Icon(Icons.auto_fix_high_rounded, size: 18, color: AppColors.sky),
                    label: Text(
                      isEn ? 'Generate Strong Sample Password' : 'Buat Contoh Password Kuat Otomatis',
                      style: AppTheme.heading(isDark, size: 12, color: AppColors.sky),
                    ),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      side: BorderSide(color: AppColors.sky.withValues(alpha: 0.45)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: AppColors.cardBg(isDark),
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: AppColors.border(isDark)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(isEn ? 'Security Criteria Checklist' : 'Daftar Kriteria Keamanan', style: AppTheme.heading(isDark, size: 15)),
                const SizedBox(height: 10),
                _CheckItem(ok: a.hasMin8, text: isEn ? 'Minimum 8 characters' : 'Minimal 8 karakter', isDark: isDark),
                _CheckItem(ok: a.hasMin12, text: isEn ? '12+ characters (Recommended)' : '12+ karakter (Sangat disarankan)', isDark: isDark),
                _CheckItem(ok: a.hasUpper, text: isEn ? 'Contains uppercase (A-Z)' : 'Mengandung huruf kapital (A-Z)', isDark: isDark),
                _CheckItem(ok: a.hasLower, text: isEn ? 'Contains lowercase (a-z)' : 'Mengandung huruf kecil (a-z)', isDark: isDark),
                _CheckItem(ok: a.hasDigit, text: isEn ? 'Contains numbers (0-9)' : 'Mengandung angka (0-9)', isDark: isDark),
                _CheckItem(ok: a.hasSymbol, text: isEn ? 'Contains special symbols (!@#\$)' : 'Mengandung simbol unik (!@#\$%)', isDark: isDark),
                _CheckItem(ok: a.notCommon, text: isEn ? 'No common leaked dictionary patterns' : 'Bebas kata sandi pasaran / mudah ditebak', isDark: isDark),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _PwAnalysis {
  final int score;
  final String label;
  final String crackTime;
  final Color color;
  final bool hasMin8;
  final bool hasMin12;
  final bool hasUpper;
  final bool hasLower;
  final bool hasDigit;
  final bool hasSymbol;
  final bool notCommon;

  const _PwAnalysis({
    required this.score,
    required this.label,
    required this.crackTime,
    required this.color,
    required this.hasMin8,
    required this.hasMin12,
    required this.hasUpper,
    required this.hasLower,
    required this.hasDigit,
    required this.hasSymbol,
    required this.notCommon,
  });
}

class _CheckItem extends StatelessWidget {
  final bool ok;
  final String text;
  final bool isDark;

  const _CheckItem({required this.ok, required this.text, required this.isDark});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        children: [
          Icon(
            ok ? Icons.check_circle_rounded : Icons.radio_button_unchecked_rounded,
            size: 18,
            color: ok ? AppColors.emerald : AppColors.muted,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              text,
              style: AppTheme.body(
                isDark,
                size: 12.5,
                weight: ok ? FontWeight.w600 : FontWeight.w400,
                color: ok ? AppColors.textPrimary(isDark) : AppColors.textSecondary(isDark),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
