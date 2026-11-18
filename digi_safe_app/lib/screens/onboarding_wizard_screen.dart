import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../core/app_state.dart';
import '../core/theme.dart';
import '../widgets/shared_widgets.dart';
import 'main_shell.dart';

/// Interactive 4-Stage Post-Login Onboarding Wizard (setelah Google Pop-Up atau Email Register/Login awal):
/// Step 0: Isi Nickname & Username (dengan Maskot interaktif bereaksi saat mengetik)
/// Step 1: Celebratory Mascot "YEAY!" (Terinspirasi Gambar 1 Kiri — Maskot imut muncul dari bawah)
/// Step 2: Isi Bio & Pilih 1 dari 4 Role Komunitas (Kartu interaktif memantul saat dipilih)
/// Step 3: Animasi Maskot Menyiapkan Profil 0% -> 100% (Terinspirasi Gambar 1 Kanan — Maskot menatap progress bar)
class OnboardingWizardScreen extends StatefulWidget {
  final AppState appState;

  const OnboardingWizardScreen({super.key, required this.appState});

  @override
  State<OnboardingWizardScreen> createState() => _OnboardingWizardScreenState();
}

class _OnboardingWizardScreenState extends State<OnboardingWizardScreen> with TickerProviderStateMixin {
  int _stage = 0; // 0: Identity, 1: Celebrate YEAY, 2: Bio & Role, 3: Building Mascot Progress

  late final TextEditingController _nickCtrl;
  late final TextEditingController _userCtrl;
  late final TextEditingController _bioCtrl;

  String _selectedRole = SpecialtyRoleOption.all.first.value;
  String? _step0Error;
  String? _step2Error;

  late final AnimationController _bounceCtrl;
  late final AnimationController _celebrateCtrl;
  late final AnimationController _progressCtrl;
  late final AnimationController _twinkleCtrl;

  bool _savingDone = false;
  String? _saveError;

  @override
  void initState() {
    super.initState();
    final u = widget.appState.user;
    final defaultNick = (u?.namaLengkap ?? '').trim();
    final defaultUser = (u?.username ?? '').trim();
    final defaultBio = (u?.bio ?? '').trim();

    _nickCtrl = TextEditingController(text: defaultNick);
    _userCtrl = TextEditingController(text: defaultUser);
    _bioCtrl = TextEditingController(
      text: defaultBio.isNotEmpty ? defaultBio : 'Warga Siber Purwakarta siap jaga ruang digital!',
    );
    if (u?.specialtyRole != null && u!.specialtyRole!.isNotEmpty) {
      _selectedRole = u.specialtyRole!;
    }

    _bounceCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 1800))..repeat(reverse: true);
    _celebrateCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 900));
    _progressCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 2600));
    _twinkleCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 2400))..repeat();
  }

  @override
  void dispose() {
    _nickCtrl.dispose();
    _userCtrl.dispose();
    _bioCtrl.dispose();
    _bounceCtrl.dispose();
    _celebrateCtrl.dispose();
    _progressCtrl.dispose();
    _twinkleCtrl.dispose();
    super.dispose();
  }

  String get _cleanUsername => _userCtrl.text
      .trim()
      .toLowerCase()
      .replaceAll(' ', '_')
      .replaceAll(RegExp(r'[^a-z0-9_-]'), '');

  void _goToCelebrate() {
    final nick = _nickCtrl.text.trim();
    final uname = _cleanUsername;
    if (nick.length < 2) {
      setState(() => _step0Error = widget.appState.isEn ? 'Nickname must be at least 2 chars.' : 'Nickname minimal 2 karakter ya!');
      return;
    }
    if (uname.length < 3) {
      setState(() => _step0Error = widget.appState.isEn ? 'Username must be at least 3 characters (letters/numbers).' : 'Username minimal 3 karakter (huruf/angka/_).');
      return;
    }
    _userCtrl.text = uname;
    setState(() {
      _step0Error = null;
      _stage = 1;
    });
    _celebrateCtrl.forward(from: 0);
  }

  void _goToBioRole() {
    setState(() {
      _step2Error = null;
      _stage = 2;
    });
  }

  Future<void> _submitFinalOnboarding() async {
    final bio = _bioCtrl.text.trim();
    if (bio.isEmpty) {
      setState(() => _step2Error = widget.appState.isEn ? 'Please write a short bio!' : 'Isi bio singkatmu dulu ya!');
      return;
    }

    setState(() {
      _stage = 3;
      _savingDone = false;
      _saveError = null;
    });

    _progressCtrl.forward(from: 0);

    try {
      await widget.appState.updateProfile(
        namaLengkap: _nickCtrl.text.trim(),
        username: _cleanUsername,
        bio: bio,
        specialtyRole: _selectedRole,
      );
      await widget.appState.markOnboardingCompleted();
      _savingDone = true;
    } on ApiException catch (e) {
      if (mounted) {
        setState(() {
          _saveError = e.message;
          _stage = e.message.toLowerCase().contains('username') ? 0 : 2;
          if (_stage == 0) {
            _step0Error = e.message;
          } else {
            _step2Error = e.message;
          }
        });
      }
      return;
    } catch (e) {
      // Tetap lanjut bila tersimpan sebagian atau tampilkan opsi coba lagi
      _saveError = e.toString();
      _savingDone = true;
    }

    // Tunggu animasi progress mencapai 100% agar user menikmati animasi maskotnya
    while (_progressCtrl.value < 0.99 && mounted) {
      await Future.delayed(const Duration(milliseconds: 60));
    }
    if (!mounted || !_savingDone) return;

    await Future.delayed(const Duration(milliseconds: 420));
    if (!mounted) return;

    Navigator.of(context).pushAndRemoveUntil(
      PageRouteBuilder(
        transitionDuration: const Duration(milliseconds: 420),
        pageBuilder: (_, _, _) => MainShell(appState: widget.appState),
        transitionsBuilder: (_, anim, _, child) => FadeTransition(opacity: anim, child: child),
      ),
      (_) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = widget.appState.isDark;
    final isEn = widget.appState.isEn;

    return PopScope(
      canPop: false,
      child: Scaffold(
        backgroundColor: isDark ? const Color(0xFF091122) : const Color(0xFFF7FAFF),
        body: AnimatedSwitcher(
          duration: const Duration(milliseconds: 420),
          switchInCurve: Curves.easeOutCubic,
          child: switch (_stage) {
            0 => _buildStepIdentity(isDark, isEn),
            1 => _buildStageCelebrate(isDark, isEn),
            2 => _buildStepBioRole(isDark, isEn),
            _ => _buildStageBuilding(isDark, isEn),
          },
        ),
      ),
    );
  }

  Widget _buildStepIdentity(bool isDark, bool isEn) {
    final nick = _nickCtrl.text.trim();
    final uname = _cleanUsername;
    final bubbleText = nick.isEmpty
        ? (isEn ? 'Hi! I\'m Dika! What should we call you?' : 'Hai! Aku Dika! Siapa nama panggilanmu?')
        : uname.length < 3
            ? (isEn ? 'Awesome name, $nick! Now pick a unique @username' : 'Nama keren, $nick! Sekarang pilih @username unik ya!')
            : (isEn ? 'Looking sharp @$uname! Ready to roll!' : 'Mantap banget @$uname! Yuk lanjut!');

    return SafeArea(
      key: const ValueKey('step0'),
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(22, 16, 22, 28),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Progress Pill Bar
            Row(
              children: [
                Expanded(
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(99),
                    child: LinearProgressIndicator(
                      value: 0.45,
                      minHeight: 7,
                      backgroundColor: AppColors.border(isDark),
                      color: AppColors.sky,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Text(
                  isEn ? 'Step 1 of 2' : 'Langkah 1 / 2',
                  style: AppTheme.heading(isDark, size: 11.5, color: AppColors.sky),
                ),
              ],
            ),
            const SizedBox(height: 20),

            // Animated Peeking Mascot Banner + Speech Bubble
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: isDark
                      ? [const Color(0xFF172554), const Color(0xFF1E293B)]
                      : [const Color(0xFFDBEAFE), const Color(0xFFEFF6FF)],
                ),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: AppColors.sky.withValues(alpha: 0.25)),
              ),
              child: Row(
                children: [
                  AnimatedBuilder(
                    animation: _bounceCtrl,
                    builder: (_, child) => Transform.translate(
                      offset: Offset(0, math.sin(_bounceCtrl.value * math.pi) * -5),
                      child: child,
                    ),
                    child: Container(
                      width: 66,
                      height: 66,
                      padding: const EdgeInsets.all(4),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(color: AppColors.sky.withValues(alpha: 0.25), blurRadius: 12, offset: const Offset(0, 4)),
                        ],
                      ),
                      child: Image.asset('assets/images/mascot-register.png', fit: BoxFit.contain),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: AnimatedSwitcher(
                      duration: const Duration(milliseconds: 220),
                      child: Container(
                        key: ValueKey(bubbleText),
                        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 10),
                        decoration: BoxDecoration(
                          color: isDark ? AppColors.darkCard : Colors.white,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Text(
                          bubbleText,
                          style: AppTheme.body(isDark, size: 12.5, weight: FontWeight.w600, height: 1.4),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 24),
            Text(
              isEn ? 'Create Your Cyber Identity' : 'Atur Identitas Warga Sibermu',
              style: AppTheme.heading(isDark, size: 24, height: 1.18),
            ),
            const SizedBox(height: 6),
            Text(
              isEn
                  ? 'Set your display nickname and unique @username seen across Digi-Safe.'
                  : 'Tentukan nama panggilan dan @username unik yang tampil di komunitas Purwakarta.',
              style: AppTheme.body(isDark, size: 12.5, color: AppColors.textSecondary(isDark), height: 1.5),
            ),

            if (_step0Error != null) ...[
              const SizedBox(height: 14),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF2F2),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFFCA5A5)),
                ),
                child: Text(_step0Error!, style: AppTheme.body(false, size: 12, color: const Color(0xFFDC2626))),
              ),
            ],

            const SizedBox(height: 20),
            TextField(
              controller: _nickCtrl,
              onChanged: (_) => setState(() {}),
              decoration: InputDecoration(
                labelText: isEn ? 'Nickname / Display Name' : 'Nickname / Nama Panggilan',
                hintText: isEn ? 'e.g. Budi Santoso' : 'Contoh: Razief / Budi Santoso',
                prefixIcon: const Icon(Icons.face_retouching_natural_rounded, color: AppColors.sky),
                filled: true,
                fillColor: AppColors.cardBg(isDark),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(18), borderSide: BorderSide(color: AppColors.border(isDark))),
              ),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _userCtrl,
              onChanged: (_) => setState(() {}),
              decoration: InputDecoration(
                labelText: 'Username',
                hintText: 'contoh_warga_pwk',
                prefixIcon: const Icon(Icons.alternate_email_rounded, color: AppColors.cobalt),
                filled: true,
                fillColor: AppColors.cardBg(isDark),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(18), borderSide: BorderSide(color: AppColors.border(isDark))),
              ),
            ),
            if (uname.isNotEmpty) ...[
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(Icons.check_circle_outline_rounded, size: 14, color: AppColors.emerald),
                  const SizedBox(width: 6),
                  Text(
                    'Preview: @$uname',
                    style: AppTheme.heading(isDark, size: 11.5, color: AppColors.emerald),
                  ),
                ],
              ),
            ],
            const SizedBox(height: 28),

            SizedBox(
              width: double.infinity,
              height: 54,
              child: ElevatedButton(
                onPressed: _goToCelebrate,
                style: ElevatedButton.styleFrom(
                  backgroundColor: isDark ? AppColors.cobalt : AppColors.ink,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Flexible(
                      child: Text(
                        isEn ? 'Continue' : 'Lanjut Sekarang',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: AppTheme.heading(false, size: 14.5, color: Colors.white),
                      ),
                    ),
                    const SizedBox(width: 8),
                    const Icon(Icons.arrow_forward_rounded, size: 18, color: AppColors.lime),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// Intermission "YEAY!" terinspirasi penuh dari Gambar 1 Kiri ("Great!")
  Widget _buildStageCelebrate(bool isDark, bool isEn) {
    final uname = _cleanUsername.isNotEmpty ? _cleanUsername : 'WargaSiber';

    return SafeArea(
      key: const ValueKey('stageCelebrate'),
      bottom: false,
      child: Stack(
        children: [
          // Sparkle Background Painter
          Positioned.fill(
            child: AnimatedBuilder(
              animation: _twinkleCtrl,
              builder: (_, _) => CustomPaint(
                painter: _SparklesBgPainter(progress: _twinkleCtrl.value, color: AppColors.sky),
              ),
            ),
          ),

          Column(
            children: [
              const SizedBox(height: 28),
              // Animated Elastic Checkmark Circle
              ScaleTransition(
                scale: CurvedAnimation(parent: _celebrateCtrl, curve: Curves.elasticOut),
                child: Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    color: AppColors.sky.withValues(alpha: 0.14),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.check_rounded, size: 34, color: AppColors.cobalt),
                ),
              ),
              const SizedBox(height: 22),
              Text(
                isEn ? 'Yeay! Awesome!' : 'Yeay! Keren Banget!',
                style: AppTheme.heading(isDark, size: 25),
              ),
              const SizedBox(height: 10),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 34),
                child: Text(
                  isEn
                      ? '@$uname is ready!\nLet\'s customize your bio and community role next.'
                      : 'Identitas @$uname sudah siap!\nSatu langkah lagi untuk mengisi bio & memilih role kamu.',
                  textAlign: TextAlign.center,
                  style: AppTheme.body(isDark, size: 13.5, color: AppColors.textSecondary(isDark), height: 1.55),
                ),
              ),
              const SizedBox(height: 18),

              // Speech Cheer Chip
              AnimatedBuilder(
                animation: _bounceCtrl,
                builder: (_, child) => Transform.translate(
                  offset: Offset(0, math.sin(_bounceCtrl.value * math.pi) * -6),
                  child: child,
                ),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 9),
                  decoration: BoxDecoration(
                    color: AppColors.lime,
                    borderRadius: BorderRadius.circular(99),
                    boxShadow: [
                      BoxShadow(color: AppColors.lime.withValues(alpha: 0.45), blurRadius: 16, offset: const Offset(0, 6)),
                    ],
                  ),
                  child: Text(
                    isEn ? '🎉 YEAY! LET\'S GO!' : '🎉 YEAY! LANJUT YUK!',
                    style: AppTheme.heading(false, size: 13, color: AppColors.ink),
                  ),
                ),
              ),

              const Spacer(),

              // Bottom-peeking Giant Rounded Cute Mascot + Overlay Pill Button (Mirrors Image 1 Left!)
              SlideTransition(
                position: Tween<Offset>(begin: const Offset(0, 0.45), end: Offset.zero).animate(
                  CurvedAnimation(parent: _celebrateCtrl, curve: Curves.easeOutBack),
                ),
                child: Stack(
                  alignment: Alignment.bottomCenter,
                  children: [
                    SizedBox(
                      height: 260,
                      width: double.infinity,
                      child: AnimatedBuilder(
                        animation: _bounceCtrl,
                        builder: (_, _) => CustomPaint(
                          painter: _PeekingMascotPainter(
                            bob: _bounceCtrl.value,
                            isDark: isDark,
                          ),
                        ),
                      ),
                    ),
                    // Mascot miniature cheering badge
                    Positioned(
                      top: 22,
                      right: 48,
                      child: Transform.rotate(
                        angle: 0.12,
                        child: Container(
                          width: 62,
                          height: 62,
                          padding: const EdgeInsets.all(4),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(color: Colors.black.withValues(alpha: 0.12), blurRadius: 12),
                            ],
                          ),
                          child: Image.asset('assets/images/mascot-login.png', fit: BoxFit.contain),
                        ),
                      ),
                    ),
                    Padding(
                      padding: EdgeInsets.fromLTRB(32, 0, 32, MediaQuery.of(context).padding.bottom + 28),
                      child: SizedBox(
                        width: 220,
                        height: 52,
                        child: ElevatedButton(
                          onPressed: _goToBioRole,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: isDark ? AppColors.cobalt : AppColors.cobalt,
                            foregroundColor: Colors.white,
                            elevation: 6,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                          ),
                          child: Text(
                            isEn ? 'Customize Bio & Role →' : 'Isi Bio & Role →',
                            style: AppTheme.heading(false, size: 13.5, color: Colors.white),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStepBioRole(bool isDark, bool isEn) {
    final activeOpt = SpecialtyRoleOption.find(_selectedRole) ?? SpecialtyRoleOption.all.first;
    final quickBios = isEn
        ? ['Student at SMKN 2 Purwakarta', 'Anti-phishing digital champion', 'Purwakarta cyber citizen']
        : ['Pelajar SMK Negeri 2 Purwakarta', 'Pejuang anti-phishing & hoaks', 'Warga Siber Purwakarta'];

    return SafeArea(
      key: const ValueKey('step2'),
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(22, 16, 22, 28),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                GestureDetector(
                  onTap: () => setState(() => _stage = 0),
                  child: Container(
                    width: 34,
                    height: 34,
                    decoration: BoxDecoration(
                      color: AppColors.cardBg(isDark),
                      shape: BoxShape.circle,
                      border: Border.all(color: AppColors.border(isDark)),
                    ),
                    child: const Icon(Icons.arrow_back_rounded, size: 17),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(99),
                    child: LinearProgressIndicator(
                      value: 0.90,
                      minHeight: 7,
                      backgroundColor: AppColors.border(isDark),
                      color: AppColors.emerald,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Text(
                  isEn ? 'Step 2 of 2' : 'Langkah 2 / 2',
                  style: AppTheme.heading(isDark, size: 11.5, color: AppColors.emerald),
                ),
              ],
            ),
            const SizedBox(height: 18),

            // Bot Digika + Interactive Role Reaction
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: activeOpt.color.withValues(alpha: isDark ? 0.18 : 0.10),
                borderRadius: BorderRadius.circular(22),
                border: Border.all(color: activeOpt.color.withValues(alpha: 0.32)),
              ),
              child: Row(
                children: [
                  const CircleAvatar(radius: 26, backgroundImage: AssetImage('assets/images/bot-digika.jpg')),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isEn ? 'Awesome choice: ${activeOpt.value}!' : 'Pilihan mantap: ${activeOpt.value}!',
                          style: AppTheme.heading(isDark, size: 13.5, color: activeOpt.color),
                        ),
                        Text(
                          isEn ? activeOpt.descEn : activeOpt.descId,
                          style: AppTheme.body(isDark, size: 11.5, color: AppColors.textSecondary(isDark)),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),
            Text(isEn ? 'Bio & Your Community Role' : 'Lengkapi Bio & Pilih Rolemu', style: AppTheme.heading(isDark, size: 22)),
            const SizedBox(height: 14),

            if (_step2Error != null) ...[
              Container(
                width: double.infinity,
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF2F2),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFFCA5A5)),
                ),
                child: Text(_step2Error!, style: AppTheme.body(false, size: 12, color: const Color(0xFFDC2626))),
              ),
            ],

            TextField(
              controller: _bioCtrl,
              maxLines: 2,
              decoration: InputDecoration(
                labelText: isEn ? 'Short Bio / Location' : 'Bio Singkat / Domisili',
                prefixIcon: const Icon(Icons.edit_note_rounded, color: AppColors.sky),
                filled: true,
                fillColor: AppColors.cardBg(isDark),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(18), borderSide: BorderSide(color: AppColors.border(isDark))),
              ),
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: quickBios
                  .map((qb) => GestureDetector(
                        onTap: () => setState(() => _bioCtrl.text = qb),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: AppColors.softBg(isDark),
                            borderRadius: BorderRadius.circular(99),
                            border: Border.all(color: AppColors.border(isDark)),
                          ),
                          child: Text('+ $qb', style: AppTheme.body(isDark, size: 10.5, weight: FontWeight.w500)),
                        ),
                      ))
                  .toList(),
            ),

            const SizedBox(height: 20),
            Text('Select Your Role', style: AppTheme.heading(isDark, size: 15)),
            const SizedBox(height: 10),
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisSpacing: 10,
              mainAxisSpacing: 10,
              childAspectRatio: 1.55,
              children: SpecialtyRoleOption.all.map((opt) {
                final selected = _selectedRole == opt.value;
                return GestureDetector(
                  onTap: () => setState(() => _selectedRole = opt.value),
                  child: AnimatedScale(
                    scale: selected ? 1.03 : 1.0,
                    duration: const Duration(milliseconds: 180),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: selected ? opt.color.withValues(alpha: isDark ? 0.24 : 0.12) : AppColors.cardBg(isDark),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                          color: selected ? opt.color : AppColors.border(isDark),
                          width: selected ? 2.0 : 1,
                        ),
                        boxShadow: selected
                            ? [BoxShadow(color: opt.color.withValues(alpha: 0.22), blurRadius: 12, offset: const Offset(0, 4))]
                            : [],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Container(
                                width: 32,
                                height: 32,
                                decoration: BoxDecoration(color: opt.color.withValues(alpha: 0.18), borderRadius: BorderRadius.circular(10)),
                                child: Icon(opt.icon, color: opt.color, size: 18),
                              ),
                              if (selected) Icon(Icons.check_circle_rounded, color: opt.color, size: 18),
                            ],
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(opt.value, maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 12.5, color: selected ? opt.color : AppColors.textPrimary(isDark))),
                              Text(
                                isEn ? opt.descEn : opt.descId,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: AppTheme.body(isDark, size: 9.8, color: AppColors.textSecondary(isDark)),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),

            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 54,
              child: ElevatedButton(
                onPressed: _submitFinalOnboarding,
                style: ElevatedButton.styleFrom(
                  backgroundColor: isDark ? AppColors.cobalt : AppColors.ink,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Flexible(
                      child: Text(
                        isEn ? 'Finish & Enter Digi-Safe' : 'Selesai & Siapkan Profil',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: AppTheme.heading(false, size: 14.5, color: Colors.white),
                      ),
                    ),
                    const SizedBox(width: 8),
                    const Icon(Icons.auto_awesome_rounded, size: 18, color: AppColors.lime),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// Screen Building Mascot 0% -> 100% (Terinspirasi Gambar 1 Kanan: Spiral Character Looking at Progress Bar)
  Widget _buildStageBuilding(bool isDark, bool isEn) {
    final nick = _nickCtrl.text.trim().isNotEmpty ? _nickCtrl.text.trim() : 'Warga Siber';

    return AnimatedBuilder(
      key: const ValueKey('stageBuilding'),
      animation: Listenable.merge([_progressCtrl, _twinkleCtrl, _bounceCtrl]),
      builder: (context, _) {
        final pct = (_progressCtrl.value * 100).round().clamp(0, 100);
        return Stack(
          children: [
            Positioned.fill(
              child: DecoratedBox(
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF0A1020) : const Color(0xFFFAF9F6),
                ),
              ),
            ),
            Positioned.fill(
              child: CustomPaint(
                painter: _SparklesBgPainter(progress: _twinkleCtrl.value, color: AppColors.cobalt),
              ),
            ),

            SafeArea(
              bottom: false,
              child: Column(
                children: [
                  const Spacer(flex: 2),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 34),
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Text(
                                isEn ? 'Creating $nick\'s shield' : 'Menyiapkan profil $nick',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: AppTheme.heading(isDark, size: 14, color: AppColors.cobalt),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Text(
                              '$pct%',
                              style: AppTheme.heading(isDark, size: 14.5, color: isDark ? AppColors.lime : AppColors.ink),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(99),
                          child: LinearProgressIndicator(
                            value: _progressCtrl.value,
                            minHeight: 8,
                            backgroundColor: AppColors.cobalt.withValues(alpha: 0.16),
                            color: AppColors.cobalt,
                          ),
                        ),
                        if (_saveError != null) ...[
                          const SizedBox(height: 8),
                          Text(_saveError!, style: AppTheme.body(false, size: 11, color: AppColors.amber)),
                        ],
                      ],
                    ),
                  ),
                  const Spacer(flex: 1),

                  // Bottom Animated Spiral-Shield Mascot Looking Up at Progress Bar
                  SizedBox(
                    height: 310,
                    width: double.infinity,
                    child: CustomPaint(
                      painter: _ProgressNotebookMascotPainter(
                        progress: _progressCtrl.value,
                        bob: _bounceCtrl.value,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        );
      },
    );
  }
}

class _SparklesBgPainter extends CustomPainter {
  final double progress;
  final Color color;

  const _SparklesBgPainter({required this.progress, required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final spots = [
      (x: 0.16, y: 0.14, r: 14.0, phase: 0.0),
      (x: 0.84, y: 0.22, r: 16.0, phase: 1.4),
      (x: 0.25, y: 0.34, r: 9.0, phase: 2.3),
      (x: 0.72, y: 0.11, r: 10.0, phase: 3.1),
      (x: 0.88, y: 0.42, r: 8.0, phase: 4.5),
      (x: 0.14, y: 0.46, r: 11.0, phase: 5.2),
    ];

    for (final s in spots) {
      final twinkle = 0.35 + 0.65 * (0.5 + 0.5 * math.sin(progress * 2 * math.pi + s.phase));
      final cx = s.x * size.width;
      final cy = s.y * size.height;
      final r = s.r * (0.78 + 0.25 * twinkle);

      final paint = Paint()
        ..color = color.withValues(alpha: 0.55 * twinkle)
        ..style = PaintingStyle.fill;

      final p = Path()
        ..moveTo(cx, cy - r)
        ..quadraticBezierTo(cx + r * 0.18, cy - r * 0.18, cx + r, cy)
        ..quadraticBezierTo(cx + r * 0.18, cy + r * 0.18, cx, cy + r)
        ..quadraticBezierTo(cx - r * 0.18, cy + r * 0.18, cx - r, cy)
        ..quadraticBezierTo(cx - r * 0.18, cy - r * 0.18, cx, cy - r)
        ..close();
      canvas.drawPath(p, paint);
    }
  }

  @override
  bool shouldRepaint(covariant _SparklesBgPainter oldDelegate) => oldDelegate.progress != progress;
}

class _PeekingMascotPainter extends CustomPainter {
  final double bob;
  final bool isDark;

  const _PeekingMascotPainter({required this.bob, required this.isDark});

  @override
  void paint(Canvas canvas, Size size) {
    final dy = math.sin(bob * math.pi) * 6;
    final bodyPaint = Paint()
      ..color = isDark ? const Color(0xFF1D4ED8).withValues(alpha: 0.35) : const Color(0xFFE3ECFA)
      ..style = PaintingStyle.fill;

    final bodyRect = Rect.fromLTWH(-40, 48 + dy, size.width + 80, size.height + 120);
    canvas.drawOval(bodyRect, bodyPaint);

    // Cute hair tuft at top
    final cx = size.width * 0.5;
    final tuftPaint = Paint()..color = isDark ? const Color(0xFF2563EB).withValues(alpha: 0.55) : const Color(0xFFD0DFF7);
    canvas.drawOval(Rect.fromCenter(center: Offset(cx - 12, 46 + dy), width: 22, height: 32), tuftPaint);
    canvas.drawOval(Rect.fromCenter(center: Offset(cx + 10, 48 + dy), width: 20, height: 28), tuftPaint);

    // Sparkling dark eyes
    final eyePaint = Paint()..color = isDark ? Colors.white : const Color(0xFF111827);
    canvas.drawOval(Rect.fromCenter(center: Offset(cx - 26, 118 + dy), width: 24, height: 28), eyePaint);
    canvas.drawOval(Rect.fromCenter(center: Offset(cx + 26, 118 + dy), width: 24, height: 28), eyePaint);

    // 4-point star reflections inside eyes
    final starPaint = Paint()..color = isDark ? AppColors.cobalt : Colors.white;
    for (final ex in [cx - 26, cx + 26]) {
      final ey = 114 + dy;
      final sp = Path()
        ..moveTo(ex, ey - 6)
        ..quadraticBezierTo(ex + 1.5, ey - 1.5, ex + 6, ey)
        ..quadraticBezierTo(ex + 1.5, ey + 1.5, ex, ey + 6)
        ..quadraticBezierTo(ex - 1.5, ey + 1.5, ex - 6, ey)
        ..quadraticBezierTo(ex - 1.5, ey - 1.5, ex, ey - 6);
      canvas.drawPath(sp, starPaint);
    }

    // Happy smile
    final smilePaint = Paint()
      ..color = isDark ? Colors.white : const Color(0xFF111827)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3.5
      ..strokeCap = StrokeCap.round;
    canvas.drawArc(
      Rect.fromCenter(center: Offset(cx, 140 + dy), width: 32, height: 22),
      0.15,
      math.pi - 0.3,
      false,
      smilePaint,
    );
  }

  @override
  bool shouldRepaint(covariant _PeekingMascotPainter oldDelegate) => oldDelegate.bob != bob;
}

class _ProgressNotebookMascotPainter extends CustomPainter {
  final double progress;
  final double bob;

  const _ProgressNotebookMascotPainter({required this.progress, required this.bob});

  @override
  void paint(Canvas canvas, Size size) {
    final dy = math.sin(bob * math.pi) * 5;
    final topY = 58.0 + dy;

    // Cobalt-Sky Body (Digi-Safe Brand Mascot adapted cleanly from Image 1 Right Phone!)
    final bodyPaint = Paint()
      ..shader = const LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: [Color(0xFF3B82F6), Color(0xFF2D46E6)],
      ).createShader(Rect.fromLTWH(0, topY, size.width, size.height));

    final bodyPath = Path()
      ..moveTo(0, topY + 14)
      ..quadraticBezierTo(size.width * 0.5, topY - 12, size.width, topY + 14)
      ..lineTo(size.width, size.height + 20)
      ..lineTo(0, size.height + 20)
      ..close();
    canvas.drawPath(bodyPath, bodyPaint);

    // Spiral Loops at Top (matching Image 1 Right Phone aesthetic!)
    final spiralPaint = Paint()
      ..color = const Color(0xFF1E293B)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 5.5
      ..strokeCap = StrokeCap.round;

    final startX = size.width * 0.16;
    final gap = (size.width * 0.68) / 4;
    for (int i = 0; i < 4; i++) {
      final lx = startX + i * gap;
      final oval = Rect.fromLTWH(lx, topY - 52, gap * 1.15, 76);
      canvas.drawArc(oval, math.pi * 0.85, math.pi * 1.45, false, spiralPaint);
    }

    // Expressive Eyebrows
    final cx = size.width * 0.5;
    final browOffset = -math.sin(progress * math.pi) * 6;
    final browPaint = Paint()
      ..color = const Color(0xFF0F172A)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 6
      ..strokeCap = StrokeCap.round;
    canvas.drawArc(Rect.fromCenter(center: Offset(cx - 32, topY + 62 + browOffset), width: 32, height: 18), math.pi + 0.3, math.pi - 0.6, false, browPaint);
    canvas.drawArc(Rect.fromCenter(center: Offset(cx + 32, topY + 62 + browOffset), width: 32, height: 18), math.pi + 0.3, math.pi - 0.6, false, browPaint);

    // Big White Oval Eyes + Pupils looking up at the live progress bar!
    final whiteEye = Paint()..color = Colors.white;
    final pupilPaint = Paint()..color = const Color(0xFF0F172A);
    final pupilShiftX = (progress - 0.5) * 14;
    final pupilShiftY = -7.0;

    for (final ex in [cx - 28, cx + 28]) {
      final ey = topY + 96;
      canvas.drawOval(Rect.fromCenter(center: Offset(ex, ey), width: 42, height: 54), whiteEye);
      canvas.drawCircle(Offset(ex + pupilShiftX, ey + pupilShiftY), 12, pupilPaint);
      canvas.drawCircle(Offset(ex + pupilShiftX - 4, ey + pupilShiftY - 4), 3.5, whiteEye);
    }

    // Cute Big Smile getting wider as progress -> 100%
    final smileW = 46.0 + progress * 24.0;
    final smilePaint = Paint()
      ..color = const Color(0xFF0F172A)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 6.5
      ..strokeCap = StrokeCap.round;
    canvas.drawArc(
      Rect.fromCenter(center: Offset(cx, topY + 146), width: smileW, height: 34 + progress * 10),
      0.15,
      math.pi - 0.3,
      false,
      smilePaint,
    );
  }

  @override
  bool shouldRepaint(covariant _ProgressNotebookMascotPainter oldDelegate) =>
      oldDelegate.progress != progress || oldDelegate.bob != bob;
}
