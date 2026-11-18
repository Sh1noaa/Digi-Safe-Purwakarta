import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../models/digisafe_models.dart';

class PhishingHunterScreen extends StatefulWidget {
  final AppState appState;

  const PhishingHunterScreen({super.key, required this.appState});

  @override
  State<PhishingHunterScreen> createState() => _PhishingHunterScreenState();
}

class _PhishingHunterScreenState extends State<PhishingHunterScreen> {
  String _state = 'loading'; // loading, menu, playing, finished
  List<MinigameLevel> _levels = [];
  List<DbMinigameQuestion> _dbQuestions = [];
  MinigameLevel? _activeLevel;

  List<Map<String, dynamic>> _questions = [];
  int _idx = 0;
  int _score = 0;
  int _correct = 0;
  int _wrong = 0;
  int _timeLeft = 15;
  int _maxTime = 15;
  int _totalTimeSpent = 0;
  bool _answered = false;
  bool _isCorrect = false;
  String _feedbackTitle = '';
  String _saveStatus = '';
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _fetchConfig();
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _fetchConfig() async {
    try {
      final res = await widget.appState.api.get('/minigame/phishing');
      if (!mounted) return;
      setState(() {
        _levels = (res['levels'] as List? ?? [])
            .map((e) => MinigameLevel.fromJson(e as Map<String, dynamic>))
            .toList();
        _dbQuestions = (res['db_questions'] as List? ?? [])
            .map((e) => DbMinigameQuestion.fromJson(e as Map<String, dynamic>))
            .toList();
        _state = 'menu';
      });
    } catch (_) {
      if (mounted) setState(() => _state = 'menu');
    }
  }

  void _startGame(MinigameLevel lvl) {
    final isEn = widget.appState.isEn;
    final customDb = _dbQuestions
        .where((q) => q.level == lvl.slug)
        .map(
          (q) => {
            'mode': 'mcq',
            'sender': isEn
                ? 'Digi-Safe Admin Question Bank'
                : 'Bank Soal Admin Digi-Safe',
            'address': 'cases@digisafe.purwakarta.id',
            'channel': lvl.badgeLabel,
            'subject': isEn
                ? 'Digital Security Case Analysis'
                : 'Analisis Kasus Keamanan Digital',
            'body': q.pertanyaan,
            'options': q.opsi,
            'answer': q.kunci,
            'explanation':
                '${isEn ? 'Correct answer (' : 'Jawaban tepat ('}${q.kunci}): ${q.kunciTeks}',
          },
        );

    final pool = _phishingPool(isEn, lvl.slug);
    setState(() {
      _activeLevel = lvl;
      _maxTime = lvl.waktuDetik;
      _questions = [...customDb, ...pool];
      _idx = 0;
      _score = 0;
      _correct = 0;
      _wrong = 0;
      _totalTimeSpent = 0;
      _state = 'playing';
    });
    _startTimer();
  }

  void _startTimer() {
    _timer?.cancel();
    setState(() {
      _answered = false;
      _timeLeft = _maxTime;
    });
    _timer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (!mounted) return;
      if (_timeLeft > 1) {
        setState(() => _timeLeft--);
      } else {
        t.cancel();
        _onTimeout();
      }
    });
  }

  void _onTimeout() {
    if (_answered) return;
    final isEn = widget.appState.isEn;
    setState(() {
      _answered = true;
      _isCorrect = false;
      _wrong++;
      _totalTimeSpent += _maxTime;
      _score = math.max(0, _score - (_activeLevel?.penaltiSalah ?? 5));
      _feedbackTitle = isEn ? 'Time Up!' : 'Waktu Habis!';
    });
  }

  void _submitAnswer(String choice) {
    if (_answered) return;
    _timer?.cancel();
    final isEn = widget.appState.isEn;
    final q = _questions[_idx];
    final correct = choice == q['answer'];
    setState(() {
      _answered = true;
      _totalTimeSpent += math.max(1, _maxTime - _timeLeft);
      _isCorrect = correct;
      if (correct) {
        _correct++;
        _score += _activeLevel?.poinBenar ?? 10;
        _feedbackTitle = isEn ? 'Spot On!' : 'Tepat Sekali!';
      } else {
        _wrong++;
        _score = math.max(0, _score - (_activeLevel?.penaltiSalah ?? 5));
        _feedbackTitle = isEn ? 'Incorrect!' : 'Kurang Tepat!';
      }
    });
  }

  void _nextQuestion() {
    if (_idx + 1 < _questions.length) {
      setState(() => _idx++);
      _startTimer();
    } else {
      _finishGame();
    }
  }

  Future<void> _finishGame() async {
    _timer?.cancel();
    final isEn = widget.appState.isEn;
    setState(() {
      _state = 'finished';
      _saveStatus = isEn
          ? 'Saving score to server...'
          : 'Menyimpan skor ke server...';
    });

    try {
      final res = await widget.appState.api.post('/minigame/score', {
        'game_type': 'phishing',
        'skor': _score,
        'benar': _correct,
        'salah': _wrong,
        'avg_time_sec': _questions.isNotEmpty
            ? _totalTimeSpent / _questions.length
            : 8,
      });
      await widget.appState.refreshMe();
      if (mounted) {
        setState(
          () => _saveStatus =
              (res['message'] ?? (isEn ? 'Score saved!' : 'Skor tersimpan!'))
                  .toString(),
        );
      }
    } catch (e) {
      if (mounted) setState(() => _saveStatus = e.toString());
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = widget.appState.isDark;
    final isEn = widget.appState.isEn;

    return Scaffold(
      backgroundColor: isDark ? AppColors.darkBg : const Color(0xFFF7F8FC),
      appBar: AppBar(
        title: Text(
          'Phishing Hunter',
          style: AppTheme.heading(isDark, size: 17),
        ),
      ),
      body: _state == 'loading'
          ? const Center(child: CircularProgressIndicator())
          : _state == 'menu'
          ? _LevelSelectBody(
              isDark: isDark,
              isEn: isEn,
              title: 'Phishing Hunter',
              subtitle: isEn
                  ? 'Choose a level to detect genuine alerts vs phishing traps.'
                  : 'Pilih level tantangan untuk mendeteksi pesan resmi vs jebakan phishing.',
              levels: _levels,
              isHpMode: false,
              onSelect: _startGame,
            )
          : _state == 'finished'
          ? _GameResultBody(
              isDark: isDark,
              state: widget.appState,
              badgeText:
                  '${((_correct / math.max(1, _correct + _wrong)) * 100).round()}%',
              score: _score,
              correct: _correct,
              wrong: _wrong,
              saveStatus: _saveStatus,
              onReplayMenu: () => setState(() => _state = 'menu'),
            )
          : _buildPlayingBody(isDark, isEn),
    );
  }

  Widget _buildPlayingBody(bool isDark, bool isEn) {
    final q = _questions[_idx];
    final isMcq = q['mode'] == 'mcq';

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Status bar
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          decoration: BoxDecoration(
            color: AppColors.cardBg(isDark),
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: AppColors.border(isDark)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '${widget.appState.tr('game_question_label')} ${_idx + 1} / ${_questions.length}',
                style: AppTheme.heading(
                  isDark,
                  size: 12.5,
                  color: AppColors.textSecondary(isDark),
                ),
              ),
              Text(
                '⏱ $_timeLeft${widget.appState.tr('game_sec_short')}',
                style: AppTheme.heading(
                  isDark,
                  size: 14,
                  color: _timeLeft <= 4
                      ? AppColors.danger
                      : AppColors.textPrimary(isDark),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 5,
                ),
                decoration: BoxDecoration(
                  color: AppColors.lime,
                  borderRadius: BorderRadius.circular(99),
                ),
                child: Text(
                  '${widget.appState.tr('game_score_label')}: $_score',
                  style: AppTheme.heading(
                    false,
                    size: 12,
                    color: AppColors.ink,
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 10),
        ClipRRect(
          borderRadius: BorderRadius.circular(99),
          child: LinearProgressIndicator(
            value: _maxTime > 0 ? (_timeLeft / _maxTime) : 0,
            minHeight: 7,
            backgroundColor: AppColors.border(isDark),
            color: _timeLeft <= 4 ? AppColors.danger : AppColors.sky,
          ),
        ),
        const SizedBox(height: 16),

        // Message Card
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
              Row(
                children: [
                  CircleAvatar(
                    backgroundColor: AppColors.skySoft,
                    child: const Icon(
                      Icons.mail_outline_rounded,
                      color: AppColors.sky,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${q['sender']}',
                          style: AppTheme.heading(isDark, size: 14),
                        ),
                        Text(
                          '${q['address']}',
                          style: AppTheme.body(
                            isDark,
                            size: 11,
                            color: AppColors.textSecondary(isDark),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: AppColors.softBg(isDark),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      '${q['channel']}',
                      style: AppTheme.heading(
                        isDark,
                        size: 10,
                        color: AppColors.textSecondary(isDark),
                      ),
                    ),
                  ),
                ],
              ),
              const Divider(height: 24),
              Text(
                '${q['subject']}',
                style: AppTheme.heading(isDark, size: 16),
              ),
              const SizedBox(height: 10),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.softBg(isDark),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border(isDark)),
                ),
                child: Text(
                  '${q['body']}',
                  style: AppTheme.body(isDark, size: 13.5, height: 1.55),
                ),
              ),
              const SizedBox(height: 16),

              if (!_answered && isMcq) ...[
                ...['A', 'B', 'C', 'D'].map((letter) {
                  final opts = q['options'] as Map<String, String>;
                  return Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: OutlinedButton(
                      onPressed: () => _submitAnswer(letter),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.all(14),
                        alignment: Alignment.centerLeft,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                      child: Row(
                        children: [
                          Container(
                            width: 26,
                            height: 26,
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              color: AppColors.ink,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              letter,
                              style: AppTheme.heading(
                                false,
                                size: 11.5,
                                color: AppColors.lime,
                              ),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              opts[letter] ?? '',
                              style: AppTheme.body(
                                isDark,
                                size: 12.5,
                                weight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }),
              ] else if (!_answered) ...[
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _submitAnswer('asli'),
                        icon: const Icon(Icons.check_rounded, size: 18),
                        label: Text(
                          widget.appState.tr('game_genuine_safe'),
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF059669),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 15),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _submitAnswer('phishing'),
                        icon: const Icon(Icons.warning_amber_rounded, size: 18),
                        label: Text(
                          widget.appState.tr('game_phishing_trap'),
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFDC2626),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 15),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ] else ...[
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: _isCorrect
                        ? const Color(0xFFECFDF5)
                        : const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: _isCorrect
                          ? const Color(0xFFA7F3D0)
                          : const Color(0xFFFCA5A5),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            _feedbackTitle,
                            style: AppTheme.heading(
                              false,
                              size: 13,
                              color: _isCorrect
                                  ? const Color(0xFF065F46)
                                  : const Color(0xFF991B1B),
                            ),
                          ),
                          Text(
                            _isCorrect
                                ? '+${_activeLevel?.poinBenar ?? 10} pts'
                                : '-${_activeLevel?.penaltiSalah ?? 5} pts',
                            style: AppTheme.heading(
                              false,
                              size: 12,
                              color: _isCorrect
                                  ? const Color(0xFF065F46)
                                  : const Color(0xFF991B1B),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        '${q['explanation']}',
                        style: AppTheme.body(
                          false,
                          size: 12,
                          color: _isCorrect
                              ? const Color(0xFF064E3B)
                              : const Color(0xFF7F1D1D),
                          height: 1.5,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Align(
                        alignment: Alignment.centerRight,
                        child: ElevatedButton(
                          onPressed: _nextQuestion,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.ink,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(99),
                            ),
                          ),
                          child: Text(
                            _idx + 1 < _questions.length
                                ? widget.appState.tr('game_next_q')
                                : widget.appState.tr('game_view_result'),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
      ],
    );
  }
}

class CyberDefenderScreen extends StatefulWidget {
  final AppState appState;

  const CyberDefenderScreen({super.key, required this.appState});

  @override
  State<CyberDefenderScreen> createState() => _CyberDefenderScreenState();
}

class _CyberDefenderScreenState extends State<CyberDefenderScreen> {
  String _state = 'loading';
  List<MinigameLevel> _levels = [];
  MinigameLevel? _activeLevel;

  int _maxHp = 3;
  int _hp = 3;
  int _score = 0;
  int _correct = 0;
  int _wrong = 0;
  int _sessionTime = 60;
  double _threatProgress = 0.08; // 0.0 .. 0.72
  Map<String, String> _activeThreat = {};
  String _statusBanner = '';
  bool _statusOk = true;
  String _saveStatus = '';

  Timer? _secTimer;
  Timer? _fallTimer;

  @override
  void initState() {
    super.initState();
    _fetchConfig();
  }

  @override
  void dispose() {
    _secTimer?.cancel();
    _fallTimer?.cancel();
    super.dispose();
  }

  Future<void> _fetchConfig() async {
    try {
      final res = await widget.appState.api.get('/minigame/cyber-defender');
      if (!mounted) return;
      setState(() {
        _levels = (res['levels'] as List? ?? [])
            .map((e) => MinigameLevel.fromJson(e as Map<String, dynamic>))
            .toList();
        _state = 'menu';
      });
    } catch (_) {
      if (mounted) setState(() => _state = 'menu');
    }
  }

  void _startGame(MinigameLevel lvl) {
    setState(() {
      _activeLevel = lvl;
      _maxHp = lvl.hpAwal;
      _hp = lvl.hpAwal;
      _score = 0;
      _correct = 0;
      _wrong = 0;
      _sessionTime = 60;
      _statusBanner = '';
      _state = 'playing';
    });
    _spawnThreat();

    _secTimer?.cancel();
    _fallTimer?.cancel();

    final baseStep = math.max(
      0.012,
      (20 - math.min(16, lvl.waktuDetik)) * 0.0022,
    );

    _secTimer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (!mounted) return;
      if (_sessionTime > 1) {
        setState(() => _sessionTime--);
      } else {
        t.cancel();
        _endSession();
      }
    });

    _fallTimer = Timer.periodic(const Duration(milliseconds: 100), (_) {
      if (!mounted || _state != 'playing') return;
      final boost = _score >= 100 ? 0.008 : (_score >= 50 ? 0.004 : 0.0);
      setState(() {
        _threatProgress += baseStep + boost;
      });
      if (_threatProgress >= 0.72) {
        _takeDamage(
          widget.appState.isEn
              ? 'Threat Breached Server! -1 HP'
              : 'Ancaman Menyentuh Server! -1 HP',
        );
      }
    });
  }

  void _spawnThreat() {
    final pool = _defenderPool(widget.appState.isEn);
    final idx = math.Random().nextInt(pool.length);
    setState(() {
      _threatProgress = 0.08;
      _activeThreat = pool[idx];
    });
  }

  void _defend(String choice) {
    if (_state != 'playing') return;
    final pts = _activeLevel?.poinBenar ?? 15;
    final isEn = widget.appState.isEn;
    if (choice == _activeThreat['type']) {
      setState(() {
        _score += pts;
        _correct++;
        _statusOk = true;
        _statusBanner =
            '${isEn ? 'Threat Neutralized! +' : 'Ancaman Dihancurkan! +'}$pts';
      });
      _spawnThreat();
    } else {
      _takeDamage(isEn ? 'Wrong Defense! -1 HP' : 'Pertahanan Salah! -1 HP');
    }
  }

  void _takeDamage(String msg) {
    setState(() {
      _hp--;
      _wrong++;
      _statusOk = false;
      _statusBanner = msg;
    });
    if (_hp <= 0) {
      _endSession();
    } else {
      _spawnThreat();
    }
  }

  Future<void> _endSession() async {
    _secTimer?.cancel();
    _fallTimer?.cancel();
    final isEn = widget.appState.isEn;
    setState(() {
      _state = 'finished';
      _saveStatus = isEn
          ? 'Saving score to server...'
          : 'Menyimpan skor ke server...';
    });
    try {
      final res = await widget.appState.api.post('/minigame/score', {
        'game_type': 'cyber-defender',
        'skor': _score,
        'benar': _correct,
        'salah': _wrong,
        'avg_time_sec': _correct > 0
            ? math.max(1, (60 - _sessionTime) / _correct)
            : 99,
      });
      await widget.appState.refreshMe();
      if (mounted) {
        setState(
          () => _saveStatus =
              (res['message'] ?? (isEn ? 'Completed!' : 'Selesai!')).toString(),
        );
      }
    } catch (e) {
      if (mounted) setState(() => _saveStatus = e.toString());
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = widget.appState.isDark;
    final isEn = widget.appState.isEn;

    return Scaffold(
      backgroundColor: isDark ? AppColors.darkBg : const Color(0xFFF7F8FC),
      appBar: AppBar(
        title: Text(
          'Cyber Defender',
          style: AppTheme.heading(isDark, size: 17),
        ),
      ),
      body: _state == 'loading'
          ? const Center(child: CircularProgressIndicator())
          : _state == 'menu'
          ? _LevelSelectBody(
              isDark: isDark,
              isEn: isEn,
              title: 'Cyber Defender',
              subtitle: isEn
                  ? 'Protect the core system from falling viruses, malware & social attacks!'
                  : 'Lindungi server inti Digi-Safe dari gelombang virus, malware & phishing sosial!',
              levels: _levels,
              isHpMode: true,
              onSelect: _startGame,
            )
          : _state == 'finished'
          ? _GameResultBody(
              isDark: isDark,
              state: widget.appState,
              badgeText: _hp > 0 ? 'WIN' : 'KO',
              score: _score,
              correct: _correct,
              wrong: _wrong,
              saveStatus: _saveStatus,
              onReplayMenu: () => setState(() => _state = 'menu'),
            )
          : Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      color: AppColors.cardBg(isDark),
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: AppColors.border(isDark)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: List.generate(
                            _maxHp,
                            (i) => Icon(
                              Icons.favorite_rounded,
                              size: 20,
                              color: (i + 1) <= _hp
                                  ? AppColors.danger
                                  : Colors.grey.shade300,
                            ),
                          ),
                        ),
                        Text(
                          '⏱ $_sessionTime${widget.appState.tr('game_sec_short')}',
                          style: AppTheme.heading(isDark, size: 14),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 5,
                          ),
                          decoration: BoxDecoration(
                            color: AppColors.lime,
                            borderRadius: BorderRadius.circular(99),
                          ),
                          child: Text(
                            '${widget.appState.tr('game_score_label')}: $_score',
                            style: AppTheme.heading(
                              false,
                              size: 12,
                              color: AppColors.ink,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Radar Arena
                  Expanded(
                    child: Container(
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: AppColors.darkBg,
                        borderRadius: BorderRadius.circular(24),
                        border: Border.all(color: const Color(0xFF1E293B)),
                      ),
                      padding: const EdgeInsets.all(16),
                      child: LayoutBuilder(
                        builder: (_, constraints) {
                          final topOffset =
                              _threatProgress * (constraints.maxHeight - 85);
                          return Stack(
                            children: [
                              if (_statusBanner.isNotEmpty)
                                Align(
                                  alignment: Alignment.topRight,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 12,
                                      vertical: 5,
                                    ),
                                    decoration: BoxDecoration(
                                      color: _statusOk
                                          ? AppColors.lime
                                          : AppColors.danger,
                                      borderRadius: BorderRadius.circular(99),
                                    ),
                                    child: Text(
                                      _statusBanner,
                                      style: AppTheme.heading(
                                        false,
                                        size: 11,
                                        color: _statusOk
                                            ? AppColors.ink
                                            : Colors.white,
                                      ),
                                    ),
                                  ),
                                ),
                              Positioned(
                                top: topOffset.clamp(
                                  28.0,
                                  constraints.maxHeight - 130,
                                ),
                                left: 8,
                                right: 8,
                                child: Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(16),
                                    border: Border.all(
                                      color: AppColors.danger,
                                      width: 2,
                                    ),
                                  ),
                                  child: Row(
                                    children: [
                                      Container(
                                        width: 36,
                                        height: 36,
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFFEF2F2),
                                          borderRadius: BorderRadius.circular(
                                            10,
                                          ),
                                        ),
                                        child: const Icon(
                                          Icons.warning_amber_rounded,
                                          color: AppColors.danger,
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              _activeThreat['categoryLabel'] ??
                                                  'THREAT',
                                              style: AppTheme.heading(
                                                false,
                                                size: 9.5,
                                                color: AppColors.danger,
                                              ),
                                            ),
                                            Text(
                                              _activeThreat['name'] ?? '',
                                              style: AppTheme.heading(
                                                false,
                                                size: 13,
                                                color: AppColors.ink,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              Align(
                                alignment: Alignment.bottomCenter,
                                child: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Container(
                                      width: 48,
                                      height: 48,
                                      decoration: BoxDecoration(
                                        color: AppColors.sky.withValues(
                                          alpha: 0.22,
                                        ),
                                        borderRadius: BorderRadius.circular(14),
                                        border: Border.all(
                                          color: AppColors.sky,
                                        ),
                                      ),
                                      child: const Icon(
                                        Icons.verified_user_rounded,
                                        color: AppColors.lime,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      'DIGI-SAFE CORE SERVER',
                                      style: AppTheme.heading(
                                        false,
                                        size: 10,
                                        color: Colors.white70,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          );
                        },
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // 3 Defense Buttons
                  Row(
                    children: [
                      Expanded(
                        child: _DefenseBtn(
                          isDark: isDark,
                          title: 'Antivirus',
                          sub: isEn ? 'Block Virus' : 'Blokir Virus',
                          icon: Icons.security_rounded,
                          color: AppColors.sky,
                          onTap: () => _defend('virus'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _DefenseBtn(
                          isDark: isDark,
                          title: 'Firewall',
                          sub: isEn ? 'Block Malware' : 'Tahan Malware',
                          icon: Icons.lock_rounded,
                          color: AppColors.purpleDeep,
                          onTap: () => _defend('malware'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _DefenseBtn(
                          isDark: isDark,
                          title: isEn ? 'Awareness' : 'Edukasi',
                          sub: isEn ? 'Reject Phishing' : 'Tolak Phishing',
                          icon: Icons.menu_book_rounded,
                          color: AppColors.emerald,
                          onTap: () => _defend('phishing'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
    );
  }
}

class _DefenseBtn extends StatelessWidget {
  final bool isDark;
  final String title;
  final String sub;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  const _DefenseBtn({
    required this.isDark,
    required this.title,
    required this.sub,
    required this.icon,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: onTap,
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.cardBg(isDark),
        foregroundColor: AppColors.textPrimary(isDark),
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 6),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(18),
          side: BorderSide(color: AppColors.border(isDark)),
        ),
        elevation: 0,
      ),
      child: Column(
        children: [
          Container(
            width: 34,
            height: 34,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, size: 18, color: color),
          ),
          const SizedBox(height: 6),
          Text(title, style: AppTheme.heading(isDark, size: 12)),
          Text(
            sub,
            style: AppTheme.body(
              isDark,
              size: 9.5,
              color: AppColors.textSecondary(isDark),
            ),
          ),
        ],
      ),
    );
  }
}

class LinkTrapScreen extends StatefulWidget {
  final AppState appState;

  const LinkTrapScreen({super.key, required this.appState});

  @override
  State<LinkTrapScreen> createState() => _LinkTrapScreenState();
}

class _LinkTrapScreenState extends State<LinkTrapScreen> {
  String _state = 'loading';
  List<MinigameLevel> _levels = [];
  List<DbMinigameQuestion> _dbQuestions = [];
  MinigameLevel? _activeLevel;

  List<Map<String, dynamic>> _items = [];
  int _idx = 0;
  int _score = 0;
  int _combo = 0;
  int _multiplier = 1;
  int _maxCombo = 1;
  int _correct = 0;
  int _wrong = 0;
  int _maxTime = 10;
  int _timeLeft = 10;
  bool _answered = false;
  bool _isCorrect = false;
  String _feedbackHeader = '';
  String _lastPointsText = '';
  String _saveStatus = '';
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _fetchConfig();
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _fetchConfig() async {
    try {
      final res = await widget.appState.api.get('/minigame/link-trap');
      if (!mounted) return;
      setState(() {
        _levels = (res['levels'] as List? ?? [])
            .map((e) => MinigameLevel.fromJson(e as Map<String, dynamic>))
            .toList();
        _dbQuestions = (res['db_questions'] as List? ?? [])
            .map((e) => DbMinigameQuestion.fromJson(e as Map<String, dynamic>))
            .toList();
        _state = 'menu';
      });
    } catch (_) {
      if (mounted) setState(() => _state = 'menu');
    }
  }

  void _startGame(MinigameLevel lvl) {
    final isEn = widget.appState.isEn;
    final customDb = _dbQuestions
        .where((q) => q.level == lvl.slug)
        .map(
          (q) => {
            'mode': 'mcq',
            'context':
                '${isEn ? 'URL Inspection Case (' : 'Kasus Inspeksi Tautan ('}${lvl.badgeLabel})',
            'url': q.pertanyaan,
            'options': q.opsi,
            'type': q.kunci,
            'reason':
                '${isEn ? 'Correct answer (' : 'Jawaban tepat ('}${q.kunci}): ${q.kunciTeks}',
          },
        );

    setState(() {
      _activeLevel = lvl;
      _maxTime = lvl.waktuDetik;
      _items = [...customDb, ..._urlTrapPool(isEn)];
      _idx = 0;
      _score = 0;
      _combo = 0;
      _multiplier = 1;
      _maxCombo = 1;
      _correct = 0;
      _wrong = 0;
      _state = 'playing';
    });
    _startTimer();
  }

  void _startTimer() {
    _timer?.cancel();
    setState(() {
      _answered = false;
      _timeLeft = _maxTime;
    });
    _timer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (!mounted) return;
      if (_timeLeft > 1) {
        setState(() => _timeLeft--);
      } else {
        t.cancel();
        _onTimeout();
      }
    });
  }

  void _onTimeout() {
    if (_answered) return;
    setState(() {
      _answered = true;
      _isCorrect = false;
      _wrong++;
      _combo = 0;
      _multiplier = 1;
      _feedbackHeader = widget.appState.isEn
          ? 'Time Up! Combo Reset'
          : 'Waktu Habis! Combo Reset';
      _lastPointsText = '0 pts';
    });
  }

  void _choose(String choice) {
    if (_answered) return;
    _timer?.cancel();
    final isEn = widget.appState.isEn;
    final basePts = _activeLevel?.poinBenar ?? 10;
    final correct = choice == _items[_idx]['type'];

    setState(() {
      _answered = true;
      _isCorrect = correct;
      if (correct) {
        _correct++;
        _combo++;
        _multiplier = math.min(3, _combo);
        if (_multiplier > _maxCombo) _maxCombo = _multiplier;
        final earned = basePts * _multiplier;
        _score += earned;
        _feedbackHeader = isEn ? 'Accurate Analysis!' : 'Analisis Tepat!';
        _lastPointsText = '+$earned pts (x$_multiplier)';
      } else {
        _wrong++;
        _combo = 0;
        _multiplier = 1;
        _feedbackHeader = isEn
            ? 'Trapped! Combo Reset'
            : 'Terjebak! Combo Reset';
        _lastPointsText = '0 pts';
      }
    });
  }

  void _nextUrl() {
    if (_idx + 1 < _items.length) {
      setState(() => _idx++);
      _startTimer();
    } else {
      _finishGame();
    }
  }

  Future<void> _finishGame() async {
    _timer?.cancel();
    final isEn = widget.appState.isEn;
    setState(() {
      _state = 'finished';
      _saveStatus = isEn
          ? 'Saving score to server...'
          : 'Menyimpan skor ke server...';
    });
    try {
      final res = await widget.appState.api.post('/minigame/score', {
        'game_type': 'link-trap',
        'skor': _score,
        'benar': _correct,
        'salah': _wrong,
        'avg_time_sec': 7.5,
      });
      await widget.appState.refreshMe();
      if (mounted) {
        setState(
          () => _saveStatus =
              (res['message'] ?? (isEn ? 'Completed!' : 'Selesai!')).toString(),
        );
      }
    } catch (e) {
      if (mounted) setState(() => _saveStatus = e.toString());
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = widget.appState.isDark;
    final isEn = widget.appState.isEn;

    return Scaffold(
      backgroundColor: isDark ? AppColors.darkBg : const Color(0xFFF7F8FC),
      appBar: AppBar(
        title: Text('Link or Trap?', style: AppTheme.heading(isDark, size: 17)),
      ),
      body: _state == 'loading'
          ? const Center(child: CircularProgressIndicator())
          : _state == 'menu'
          ? _LevelSelectBody(
              isDark: isDark,
              isEn: isEn,
              title: 'Link or Trap?',
              subtitle: isEn
                  ? 'Spot lookalike typosquatting domains & build combo multipliers!'
                  : 'Identifikasi keaslian domain tautan & kumpulkan multiplier combo skor!',
              levels: _levels,
              isHpMode: false,
              onSelect: _startGame,
            )
          : _state == 'finished'
          ? _GameResultBody(
              isDark: isDark,
              state: widget.appState,
              badgeText: 'x$_maxCombo',
              score: _score,
              correct: _correct,
              wrong: _wrong,
              saveStatus: _saveStatus,
              onReplayMenu: () => setState(() => _state = 'menu'),
            )
          : _buildUrlArena(isDark, isEn),
    );
  }

  Widget _buildUrlArena(bool isDark, bool isEn) {
    final item = _items[_idx];
    final isMcq = item['mode'] == 'mcq';

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          decoration: BoxDecoration(
            color: AppColors.cardBg(isDark),
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: AppColors.border(isDark)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'URL ${_idx + 1}/${_items.length}',
                style: AppTheme.heading(isDark, size: 12.5),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: _multiplier > 1
                      ? AppColors.sky
                      : AppColors.softBg(isDark),
                  borderRadius: BorderRadius.circular(99),
                ),
                child: Text(
                  'Combo x$_multiplier',
                  style: AppTheme.heading(
                    isDark,
                    size: 11,
                    color: _multiplier > 1
                        ? Colors.white
                        : AppColors.textSecondary(isDark),
                  ),
                ),
              ),
              Text(
                '⏱ $_timeLeft${widget.appState.tr('game_sec_short')}',
                style: AppTheme.heading(
                  isDark,
                  size: 13.5,
                  color: _timeLeft <= 3
                      ? AppColors.danger
                      : AppColors.textPrimary(isDark),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: AppColors.lime,
                  borderRadius: BorderRadius.circular(99),
                ),
                child: Text(
                  '$_score pts',
                  style: AppTheme.heading(
                    false,
                    size: 11.5,
                    color: AppColors.ink,
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 10),
        ClipRRect(
          borderRadius: BorderRadius.circular(99),
          child: LinearProgressIndicator(
            value: _maxTime > 0 ? (_timeLeft / _maxTime) : 0,
            minHeight: 7,
            backgroundColor: AppColors.border(isDark),
            color: _timeLeft <= 3 ? AppColors.danger : AppColors.sky,
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
              Text(
                '${item['context']}',
                style: AppTheme.heading(isDark, size: 16),
              ),
              const SizedBox(height: 12),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.softBg(isDark),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border(isDark)),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.public_rounded,
                      size: 18,
                      color: AppColors.sky,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        '${item['url']}',
                        style: AppTheme.body(
                          isDark,
                          size: 13,
                          weight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              if (!_answered && isMcq)
                ...['A', 'B', 'C', 'D'].map((letter) {
                  final opts = item['options'] as Map<String, String>;
                  return Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: OutlinedButton(
                      onPressed: () => _choose(letter),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.all(14),
                        alignment: Alignment.centerLeft,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                      child: Row(
                        children: [
                          Container(
                            width: 26,
                            height: 26,
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              color: AppColors.ink,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              letter,
                              style: AppTheme.heading(
                                false,
                                size: 11.5,
                                color: AppColors.lime,
                              ),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              opts[letter] ?? '',
                              style: AppTheme.body(
                                isDark,
                                size: 12.5,
                                weight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                })
              else if (!_answered)
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _choose('aman'),
                        icon: const Icon(Icons.verified_user_rounded, size: 18),
                        label: Text(
                          widget.appState.tr('game_safe_btn'),
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF059669),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 15),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _choose('trap'),
                        icon: const Icon(Icons.warning_amber_rounded, size: 18),
                        label: Text(
                          widget.appState.tr('game_trap_btn'),
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFDC2626),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 15),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                          ),
                        ),
                      ),
                    ),
                  ],
                )
              else
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: _isCorrect
                        ? const Color(0xFFECFDF5)
                        : const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: _isCorrect
                          ? const Color(0xFFA7F3D0)
                          : const Color(0xFFFCA5A5),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            _feedbackHeader,
                            style: AppTheme.heading(
                              false,
                              size: 13,
                              color: _isCorrect
                                  ? const Color(0xFF065F46)
                                  : const Color(0xFF991B1B),
                            ),
                          ),
                          Text(
                            _lastPointsText,
                            style: AppTheme.heading(
                              false,
                              size: 12,
                              color: _isCorrect
                                  ? const Color(0xFF065F46)
                                  : const Color(0xFF991B1B),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        '${item['reason']}',
                        style: AppTheme.body(
                          false,
                          size: 12,
                          color: _isCorrect
                              ? const Color(0xFF064E3B)
                              : const Color(0xFF7F1D1D),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Align(
                        alignment: Alignment.centerRight,
                        child: ElevatedButton(
                          onPressed: _nextUrl,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.ink,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(99),
                            ),
                          ),
                          child: Text(
                            _idx + 1 < _items.length
                                ? widget.appState.tr('game_next_q')
                                : widget.appState.tr('game_view_result'),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
            ],
          ),
        ),
      ],
    );
  }
}

class _LevelSelectBody extends StatelessWidget {
  final bool isDark;
  final bool isEn;
  final String title;
  final String subtitle;
  final List<MinigameLevel> levels;
  final bool isHpMode;
  final ValueChanged<MinigameLevel> onSelect;

  const _LevelSelectBody({
    required this.isDark,
    required this.isEn,
    required this.title,
    required this.subtitle,
    required this.levels,
    required this.isHpMode,
    required this.onSelect,
  });

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: AppColors.cardBg(isDark),
            borderRadius: BorderRadius.circular(24),
            border: Border.all(color: AppColors.border(isDark)),
          ),
          child: Column(
            children: [
              Text(title, style: AppTheme.heading(isDark, size: 22)),
              const SizedBox(height: 6),
              Text(
                subtitle,
                textAlign: TextAlign.center,
                style: AppTheme.body(
                  isDark,
                  size: 12.5,
                  color: AppColors.textSecondary(isDark),
                ),
              ),
              const SizedBox(height: 18),
              ...levels.map((lvl) {
                final colors = AppColors.levelBadgeColors(lvl.warna, isDark);
                return GestureDetector(
                  onTap: () => onSelect(lvl),
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(15),
                    decoration: BoxDecoration(
                      color: AppColors.softBg(isDark),
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: AppColors.border(isDark)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: colors[0],
                                borderRadius: BorderRadius.circular(99),
                              ),
                              child: Text(
                                lvl.badgeLabel,
                                style: AppTheme.heading(
                                  false,
                                  size: 10,
                                  color: colors[1],
                                ),
                              ),
                            ),
                            Text(
                              isHpMode
                                  ? '❤ ${lvl.hpAwal} HP'
                                  : '⏱ ${lvl.waktuDetik}${isEn ? 's' : 'd'}',
                              style: AppTheme.heading(
                                isDark,
                                size: 11.5,
                                color: isHpMode
                                    ? AppColors.danger
                                    : AppColors.textPrimary(isDark),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          lvl.namaLevel,
                          style: AppTheme.heading(isDark, size: 15),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          lvl.deskripsi,
                          style: AppTheme.body(
                            isDark,
                            size: 12,
                            color: AppColors.textSecondary(isDark),
                          ),
                        ),
                        const SizedBox(height: 10),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              '+${lvl.poinBenar} pts',
                              style: AppTheme.heading(
                                isDark,
                                size: 11.5,
                                color: AppColors.emerald,
                              ),
                            ),
                            Text(
                              '${isEn ? 'Start' : 'Mulai'} →',
                              style: AppTheme.heading(
                                isDark,
                                size: 12,
                                color: AppColors.sky,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              }),
            ],
          ),
        ),
      ],
    );
  }
}

class _GameResultBody extends StatelessWidget {
  final bool isDark;
  final AppState state;
  final String badgeText;
  final int score;
  final int correct;
  final int wrong;
  final String saveStatus;
  final VoidCallback onReplayMenu;

  const _GameResultBody({
    required this.isDark,
    required this.state,
    required this.badgeText,
    required this.score,
    required this.correct,
    required this.wrong,
    required this.saveStatus,
    required this.onReplayMenu,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: AppColors.cardBg(isDark),
            borderRadius: BorderRadius.circular(28),
            border: Border.all(color: AppColors.border(isDark)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 68,
                height: 68,
                alignment: Alignment.center,
                decoration: const BoxDecoration(
                  color: AppColors.lime,
                  shape: BoxShape.circle,
                ),
                child: Text(
                  badgeText,
                  style: AppTheme.heading(
                    false,
                    size: 20,
                    color: AppColors.ink,
                  ),
                ),
              ),
              const SizedBox(height: 14),
              Text(
                state.tr('game_session_finished'),
                style: AppTheme.heading(isDark, size: 21),
              ),
              const SizedBox(height: 6),
              Text(
                saveStatus,
                textAlign: TextAlign.center,
                style: AppTheme.body(
                  isDark,
                  size: 12,
                  color: AppColors.sky,
                  weight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 18),
              Row(
                children: [
                  Expanded(
                    child: _ResultStat(
                      isDark: isDark,
                      title: state.tr('game_total_score'),
                      val: '$score',
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _ResultStat(
                      isDark: isDark,
                      title: state.tr('game_correct'),
                      val: '$correct',
                      color: AppColors.emerald,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _ResultStat(
                      isDark: isDark,
                      title: state.tr('game_wrong'),
                      val: '$wrong',
                      color: AppColors.danger,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton(
                      onPressed: onReplayMenu,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.lime,
                        foregroundColor: AppColors.ink,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(99),
                        ),
                      ),
                      child: Text(
                        state.tr('game_choose_other_level'),
                        style: AppTheme.heading(
                          false,
                          size: 11.5,
                          color: AppColors.ink,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () => Navigator.of(context).pop(),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.ink,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(99),
                        ),
                      ),
                      child: Text(
                        state.tr('close'),
                        style: AppTheme.heading(
                          false,
                          size: 11.5,
                          color: Colors.white,
                        ),
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
}

class _ResultStat extends StatelessWidget {
  final bool isDark;
  final String title;
  final String val;
  final Color? color;

  const _ResultStat({
    required this.isDark,
    required this.title,
    required this.val,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.softBg(isDark),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        children: [
          Text(
            title,
            style: AppTheme.body(
              isDark,
              size: 10.5,
              color: color ?? AppColors.textSecondary(isDark),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            val,
            style: AppTheme.heading(
              isDark,
              size: 20,
              color: color ?? AppColors.textPrimary(isDark),
            ),
          ),
        ],
      ),
    );
  }
}

List<Map<String, dynamic>> _phishingPool(bool isEn, String level) {
  if (isEn) {
    return [
      {
        'sender': 'Express Courier',
        'address': '+62 813-9900-1122 (WhatsApp)',
        'channel': 'WhatsApp',
        'subject': 'Parcel Delivery Receipt Photo',
        'body':
            'Hi, your parcel arrived at Purwakarta hub. Open LIHAT-RESI-PAKET.apk to inspect the parcel receipt photo.',
        'answer': 'phishing',
        'explanation':
            'Phishing Trap! Legitimate couriers never send receipts as Android .apk installers (OTP/SMS spyware).',
      },
      {
        'sender': 'Diskominfo Purwakarta',
        'address': 'info@purwakartakab.go.id',
        'channel': 'Official Email',
        'subject': 'Digital Literacy Socialization Schedule',
        'body':
            'Dear youth community leaders, view next week’s cyber education schedule on our official portal purwakartakab.go.id.',
        'answer': 'asli',
        'explanation':
            'Genuine Message! Official Indonesian government domain (.go.id) and does not ask for PIN/OTP.',
      },
      {
        'sender': 'Security Ops Alert',
        'address': 'pemenang-resmi@hadiah-bank-bri.xyz',
        'channel': 'Email',
        'subject': 'URGENT! Claim Rp 50,000,000 Prize in 10 Mins',
        'body':
            'Enter your ATM PIN, card number, and OTP immediately on the link below before your prize expires.',
        'answer': 'phishing',
        'explanation':
            'Phishing Trap! Uses a fake .xyz domain, creates fake urgency, and requests PIN & OTP.',
      },
    ];
  }

  return [
    {
      'sender': 'Kurir Ekspedisi Cepat',
      'address': '+62 813-9900-1122 (WhatsApp)',
      'channel': 'Chat WA',
      'subject': 'Foto Resi Paket Anda',
      'body':
          'Halo kak, paket sudah sampai di gudang Purwakarta tapi alamat kurang jelas. Buka file LIHAT-RESI-PAKET.apk untuk cek resinya.',
      'answer': 'phishing',
      'explanation':
          'Jebakan Phishing! Kurir resmi tidak pernah mengirim resi berformat .apk pencuri SMS/OTP.',
    },
    {
      'sender': 'Diskominfo Purwakarta',
      'address': 'info@purwakartakab.go.id',
      'channel': 'Email Resmi',
      'subject': 'Undangan Sosialisasi Literasi Digital Desa',
      'body':
          'Yth. Pengurus Karang Taruna, informasi lengkap jadwal literasi siber dapat dilihat pada portal resmi purwakartakab.go.id.',
      'answer': 'asli',
      'explanation':
          'Pesan Asli! Menggunakan domain pemerintah resmi (.go.id) dan tidak meminta PIN atau OTP.',
    },
    {
      'sender': 'Info Undian Berhadiah',
      'address': 'pemenang-resmi@hadiah-bank-bri.xyz',
      'channel': 'Email',
      'subject': 'SELAMAT! Anda Memenangkan Rp 50.000.000',
      'body':
          'Segera masukkan PIN, nomor kartu ATM, dan kode OTP Anda pada tautan berikut dalam 10 menit.',
      'answer': 'phishing',
      'explanation':
          'Jebakan Phishing! Domain .xyz palsu, menciptakan kepanikan waktu, dan meminta PIN/OTP.',
    },
    if (level != 'easy')
      {
        'sender': 'Bendahara Kecamatan',
        'address':
            'keuangan@purwakartakab.go.id (Reply-To: pencairan99@yahoo.com)',
        'channel': 'Email',
        'subject': 'Revisi Nomor Rekening Pencairan Dana',
        'body':
            'Mohon alihkan dana kegiatan hari ini ke rekening pribadi karena rekening dinas sedang gangguan.',
        'answer': 'phishing',
        'explanation':
            'Business Email Compromise! Terdapat Reply-To palsu (@yahoo.com) dan permintaan transfer mendadak ke rekening pribadi.',
      },
  ];
}

List<Map<String, String>> _defenderPool(bool isEn) {
  if (isEn) {
    return [
      {
        'name': 'Trojan .exe Download File',
        'type': 'virus',
        'categoryLabel': 'FILE VIRUS',
      },
      {
        'name': 'Public USB Drive Worm',
        'type': 'virus',
        'categoryLabel': 'FILE VIRUS',
      },
      {
        'name': 'Network Ransomware Outbreak',
        'type': 'malware',
        'categoryLabel': 'NETWORK MALWARE',
      },
      {
        'name': 'Open Port 4444 Intrusion',
        'type': 'malware',
        'categoryLabel': 'NETWORK MALWARE',
      },
      {
        'name': 'WhatsApp Fake .APK Invite',
        'type': 'phishing',
        'categoryLabel': 'SOCIAL PHISHING',
      },
      {
        'name': 'Fake Bank Officer OTP Call',
        'type': 'phishing',
        'categoryLabel': 'SOCIAL PHISHING',
      },
    ];
  }
  return [
    {
      'name': 'Trojan File Unduhan .exe',
      'type': 'virus',
      'categoryLabel': 'VIRUS FILE',
    },
    {
      'name': 'Worm Flashdisk Publik',
      'type': 'virus',
      'categoryLabel': 'VIRUS FILE',
    },
    {
      'name': 'Serangan Ransomware Jaringan',
      'type': 'malware',
      'categoryLabel': 'MALWARE JARINGAN',
    },
    {
      'name': 'Intrusi Port Terbuka 4444',
      'type': 'malware',
      'categoryLabel': 'MALWARE JARINGAN',
    },
    {
      'name': 'Pesan WA Undangan .APK Palsu',
      'type': 'phishing',
      'categoryLabel': 'PHISHING SOSIAL',
    },
    {
      'name': 'Telepon Penyamar Petugas Bank',
      'type': 'phishing',
      'categoryLabel': 'PHISHING SOSIAL',
    },
  ];
}

List<Map<String, dynamic>> _urlTrapPool(bool isEn) {
  if (isEn) {
    return [
      {
        'context': 'Google Account Sign-In Page',
        'url': 'https://accounts.goog1e.com/signin/v2',
        'type': 'trap',
        'reason':
            "Fake domain 'goog1e.com' replaces letter 'l' with digit '1' (typosquatting).",
      },
      {
        'context': 'Purwakarta Regency Official Government Portal',
        'url': 'https://purwakartakab.go.id/berita',
        'type': 'aman',
        'reason':
            "'purwakartakab.go.id' is an authentic Indonesian government portal (.go.id).",
      },
      {
        'context': 'Electricity Subsidy Claim Link',
        'url': 'http://pln.co.id.klaim-token-gratis.xyz/login',
        'type': 'trap',
        'reason':
            "Subdomain Trap! Root domain is 'klaim-token-gratis.xyz', not pln.co.id.",
      },
      {
        'context': 'National Digital Literacy Portal',
        'url': 'https://literasidigital.id/',
        'type': 'aman',
        'reason':
            "'literasidigital.id' is Indonesia's official digital literacy portal.",
      },
    ];
  }
  return [
    {
      'context': 'Halaman Login Akun Google',
      'url': 'https://accounts.goog1e.com/signin/v2',
      'type': 'trap',
      'reason':
          "Domain 'goog1e.com' palsu karena memakai angka 1 menggantikan huruf l (typosquatting).",
    },
    {
      'context': 'Portal Resmi Pemerintah Kab. Purwakarta',
      'url': 'https://purwakartakab.go.id/berita',
      'type': 'aman',
      'reason':
          "Domain 'purwakartakab.go.id' adalah situs resmi pemerintah daerah (.go.id).",
    },
    {
      'context': 'Tautan Klaim Subsidi Listrik',
      'url': 'http://pln.co.id.klaim-token-gratis.xyz/login',
      'type': 'trap',
      'reason':
          "Jebakan Subdomain! Domain utamanya adalah 'klaim-token-gratis.xyz', bukan pln.co.id.",
    },
    {
      'context': 'Portal Literasi Digital Nasional',
      'url': 'https://literasidigital.id/',
      'type': 'aman',
      'reason':
          "Domain 'literasidigital.id' adalah portal resmi literasi digital nasional.",
    },
  ];
}
