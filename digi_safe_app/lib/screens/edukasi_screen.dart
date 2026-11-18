import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../core/api_client.dart';
import '../core/app_state.dart';
import '../core/theme.dart';
import '../models/digisafe_models.dart';
import 'admin_screen.dart';
import 'auth_screens.dart';
import 'chat_screen.dart';

class EdukasiScreen extends StatefulWidget {
  final AppState appState;

  const EdukasiScreen({super.key, required this.appState});

  @override
  State<EdukasiScreen> createState() => _EdukasiScreenState();
}

class _EdukasiScreenState extends State<EdukasiScreen> {
  List<EdukasiModule> _modules = [];
  List<EdukasiRequestItem> _myRequests = [];
  int _unreadDecisions = 0;
  bool _loading = true;
  String? _error;
  int _lastVersion = -1;

  @override
  void initState() {
    super.initState();
    _loadModules();
  }

  @override
  void didUpdateWidget(covariant EdukasiScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.appState.contentVersion != _lastVersion) {
      _loadModules();
    }
  }

  Future<void> _loadModules() async {
    _lastVersion = widget.appState.contentVersion;
    setState(() {
      _loading = _modules.isEmpty;
      _error = null;
    });
    try {
      final res = await widget.appState.api.get('/edukasi');
      final raw = res['modules'];
      if (raw is List && mounted) {
        setState(() {
          _modules = raw.map((e) => EdukasiModule.fromJson(e as Map<String, dynamic>)).toList();
        });
      }
      if (widget.appState.isAuthenticated) {
        final reqRes = await widget.appState.api.getOptional('/edukasi/requests');
        if (reqRes != null && mounted) {
          final reqList = (reqRes['requests'] as List? ?? [])
              .map((e) => EdukasiRequestItem.fromJson(e as Map<String, dynamic>))
              .toList();
          setState(() {
            _myRequests = reqList;
            _unreadDecisions = (reqRes['unread_decisions'] as num?)?.toInt() ?? 0;
          });
        }
      }
    } catch (e) {
      if (mounted) setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _markRequestsSeen() async {
    setState(() => _unreadDecisions = 0);
    try {
      await widget.appState.api.post('/edukasi/requests/mark-seen');
    } catch (_) {}
  }

  void _openRequestTopicSheet() {
    if (!widget.appState.isAuthenticated) {
      Navigator.of(context).push(MaterialPageRoute(builder: (_) => LoginScreen(appState: widget.appState)));
      return;
    }

    final temaCtrl = TextEditingController();
    final descCtrl = TextEditingController();
    const categories = [
      'Phishing & Penipuan',
      'Privasi & Data Pribadi',
      'Keamanan Akun & OTP',
      'Literasi & Hoaks',
      'Keamanan Perangkat / APK',
    ];
    String selectedCat = categories.first;
    String preferredType = 'teks';
    bool sending = false;
    final isDark = widget.appState.isDark;
    final isEn = widget.appState.isEn;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) => Container(
          padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(ctx).viewInsets.bottom + 24),
          decoration: BoxDecoration(
            color: AppColors.cardBg(isDark),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const CircleAvatar(radius: 18, backgroundImage: AssetImage('assets/images/bot-digika.jpg')),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(widget.appState.tr('edu_request_btn'), style: AppTheme.heading(isDark, size: 17)),
                          Text(
                            isEn ? 'Bot Digika will notify you once Admin reviews it' : 'Bot Digika akan mengirim pesan saat Admin menyetujui/menolak',
                            style: AppTheme.body(isDark, size: 11, color: AppColors.textSecondary(isDark)),
                          ),
                        ],
                      ),
                    ),
                    IconButton(onPressed: () => Navigator.pop(ctx), icon: const Icon(Icons.close_rounded)),
                  ],
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: temaCtrl,
                  decoration: InputDecoration(
                    labelText: isEn ? 'Topic Title' : 'Judul / Tema Edukasi',
                    hintText: isEn ? 'e.g. Recognizing Fake QRIS Scams' : 'Contoh: Cara Mengenali QRIS Palsu',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 12),
                Text(isEn ? 'Category' : 'Kategori Topik', style: AppTheme.heading(isDark, size: 12)),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: categories.map((c) {
                    final active = selectedCat == c;
                    return ChoiceChip(
                      label: Text(c, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: active ? Colors.white : AppColors.textPrimary(isDark))),
                      selected: active,
                      selectedColor: AppColors.sky,
                      backgroundColor: AppColors.softBg(isDark),
                      onSelected: (_) => setModalState(() => selectedCat = c),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 12),
                SegmentedButton<String>(
                  segments: [
                    ButtonSegment(value: 'teks', label: Text(isEn ? 'Article Module' : 'Modul Bacaan')),
                    ButtonSegment(value: 'link', label: Text(isEn ? 'External Link' : 'Link Referensi')),
                  ],
                  selected: {preferredType},
                  onSelectionChanged: (s) => setModalState(() => preferredType = s.first),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: descCtrl,
                  maxLines: 3,
                  decoration: InputDecoration(
                    labelText: isEn ? 'Why is this topic needed?' : 'Jelaskan detail atau alasan kebutuhan materi',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: sending
                        ? null
                        : () async {
                            if (temaCtrl.text.trim().length < 4 || descCtrl.text.trim().length < 10) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(isEn ? 'Topic min. 4 chars & reason min. 10 chars.' : 'Tema min. 4 karakter & penjelasan min. 10 karakter.')),
                              );
                              return;
                            }
                            setModalState(() => sending = true);
                            try {
                              final res = await widget.appState.api.post('/edukasi/requests', {
                                'tema': temaCtrl.text.trim(),
                                'kategori': selectedCat,
                                'preferred_type': preferredType,
                                'deskripsi': descCtrl.text.trim(),
                              });
                              if (ctx.mounted) Navigator.pop(ctx);
                              await _loadModules();
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(content: Text('${res['message'] ?? 'Request berhasil dikirim!'}')),
                                );
                              }
                            } on ApiException catch (e) {
                              if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
                            } finally {
                              if (ctx.mounted) setModalState(() => sending = false);
                            }
                          },
                    icon: const Icon(Icons.send_rounded, size: 17, color: AppColors.lime),
                    label: Text(sending ? '...' : (isEn ? 'Submit Topic Request' : 'Kirim Usulan ke Admin'), style: AppTheme.heading(false, size: 13, color: Colors.white)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.ink,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _openModule(EdukasiModule modul) {
    if (modul.isTextModule) {
      _openReaderModal(modul);
    } else if (modul.url.isNotEmpty) {
      launchUrl(Uri.parse(modul.url), mode: LaunchMode.externalApplication);
    }
  }

  void _openReaderModal(EdukasiModule modul) {
    final isDark = widget.appState.isDark;
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => Container(
        constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.88),
        padding: const EdgeInsets.fromLTRB(22, 20, 22, 28),
        decoration: BoxDecoration(
          color: AppColors.cardBg(isDark),
          borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.lime.withValues(alpha: 0.45),
                    borderRadius: BorderRadius.circular(99),
                  ),
                  child: Text(
                    modul.tag,
                    style: AppTheme.heading(false, size: 10.5, color: AppColors.ink),
                  ),
                ),
                const Spacer(),
                IconButton(
                  icon: const Icon(Icons.close_rounded),
                  onPressed: () => Navigator.of(context).pop(),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(modul.title, style: AppTheme.heading(isDark, size: 20, height: 1.25)),
            const Divider(height: 24),
            Expanded(
              child: SingleChildScrollView(
                child: Text(
                  modul.konten,
                  style: AppTheme.body(isDark, size: 14, height: 1.75),
                ),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () => Navigator.of(context).pop(),
                icon: const Icon(Icons.check_circle_outline_rounded, size: 18, color: AppColors.lime),
                label: Text(widget.appState.tr('edu_finish_reading'), style: AppTheme.heading(false, size: 12.5, color: Colors.white)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.ink,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _openAdminUploadSheet() {
    final titleCtrl = TextEditingController();
    final categoryCtrl = TextEditingController(text: 'Literasi Siber');
    final contentCtrl = TextEditingController();
    final urlCtrl = TextEditingController();
    String type = 'teks';
    bool saving = false;
    final isDark = widget.appState.isDark;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) => Container(
          padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(ctx).viewInsets.bottom + 24),
          decoration: BoxDecoration(
            color: AppColors.cardBg(isDark),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(widget.appState.tr('edu_add_module'), style: AppTheme.heading(isDark, size: 18)),
                const SizedBox(height: 12),
                TextField(
                  controller: titleCtrl,
                  decoration: InputDecoration(
                    labelText: 'Judul Modul',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: categoryCtrl,
                  decoration: InputDecoration(
                    labelText: 'Kategori',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 10),
                SegmentedButton<String>(
                  segments: const [
                    ButtonSegment(value: 'teks', label: Text('Modul Bacaan')),
                    ButtonSegment(value: 'link', label: Text('Link Eksternal')),
                  ],
                  selected: {type},
                  onSelectionChanged: (s) => setModalState(() => type = s.first),
                ),
                const SizedBox(height: 10),
                if (type == 'teks')
                  TextField(
                    controller: contentCtrl,
                    maxLines: 4,
                    decoration: InputDecoration(
                      labelText: 'Isi Materi Edukasi',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  )
                else
                  TextField(
                    controller: urlCtrl,
                    decoration: InputDecoration(
                      labelText: 'URL Referensi (https://...)',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: saving
                        ? null
                        : () async {
                            setModalState(() => saving = true);
                            try {
                              await widget.appState.api.post('/admin/edukasi', {
                                'judul': titleCtrl.text.trim(),
                                'kategori': categoryCtrl.text.trim(),
                                'type': type,
                                'konten': contentCtrl.text.trim(),
                                'url': urlCtrl.text.trim(),
                              });
                              if (ctx.mounted) Navigator.of(ctx).pop();
                              await _loadModules();
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Modul edukasi berhasil diterbitkan ke web & APK!')),
                                );
                              }
                            } catch (e) {
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
                              }
                            } finally {
                              if (ctx.mounted) setModalState(() => saving = false);
                            }
                          },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.sky,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                    ),
                    child: saving
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : Text(widget.appState.tr('save'), style: AppTheme.heading(false, size: 13, color: Colors.white)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final isAdmin = state.user?.hasAdminAccess ?? false;

    return Container(
      decoration: BoxDecoration(
        color: isDark ? AppColors.scaffoldBg(true) : Colors.white,
        image: const DecorationImage(
          image: AssetImage('assets/images/edu-bg-gallery.jpg'),
          fit: BoxFit.contain,
        ),
      ),
      child: Container(
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF0B1120).withValues(alpha: 0.72) : Colors.transparent,
        ),
        child: RefreshIndicator(
          onRefresh: _loadModules,
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 112),
            children: [
              // Top Status Capsule (identik dengan edukasi.blade.php)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.96),
                  borderRadius: BorderRadius.circular(99),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.08),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      width: 30,
                      height: 30,
                      decoration: const BoxDecoration(color: AppColors.lime, shape: BoxShape.circle),
                      alignment: Alignment.center,
                      child: Text('DS', style: AppTheme.heading(false, size: 11, color: AppColors.ink)),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(state.tr('edu_center_title'), style: AppTheme.heading(false, size: 12, color: AppColors.ink)),
                          Text(state.tr('edu_center_sub'), maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.body(false, size: 10.5, color: AppColors.muted)),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF3F4F6),
                        borderRadius: BorderRadius.circular(99),
                      ),
                      child: Text(
                        state.tr('edu_materials_count', {'count': '${_modules.length}'}),
                        style: AppTheme.heading(false, size: 10.5, color: AppColors.ink),
                      ),
                    ),
                  ],
                ),
              ),

              if (_unreadDecisions > 0) ...[
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.96),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: AppColors.cobalt, width: 1.5),
                  ),
                  child: Row(
                    children: [
                      const CircleAvatar(radius: 18, backgroundImage: AssetImage('assets/images/bot-digika.jpg')),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          state.isEn
                              ? 'Bot Digika sent $_unreadDecisions evaluation message(s) for your topic request!'
                              : 'Bot Digika mengirim $_unreadDecisions pesan evaluasi untuk request tema edukasimu!',
                          style: AppTheme.body(false, size: 11.5, weight: FontWeight.w600, color: AppColors.ink),
                        ),
                      ),
                      TextButton(
                        onPressed: () {
                          _markRequestsSeen();
                          Navigator.of(context).push(MaterialPageRoute(builder: (_) => ChatInboxScreen(appState: state)));
                        },
                        child: Text(state.isEn ? 'Read' : 'Baca', style: const TextStyle(fontWeight: FontWeight.w800)),
                      ),
                    ],
                  ),
                ),
              ],

              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                alignment: WrapAlignment.end,
                children: [
                  ElevatedButton.icon(
                    onPressed: _openRequestTopicSheet,
                    icon: const Icon(Icons.lightbulb_outline_rounded, size: 16, color: AppColors.ink),
                    label: Text(state.tr('edu_request_btn'), style: AppTheme.heading(false, size: 11.5, color: AppColors.ink)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.lime,
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                    ),
                  ),
                  if (isAdmin) ...[
                    ElevatedButton.icon(
                      onPressed: _openAdminUploadSheet,
                      icon: const Icon(Icons.add_circle_outline_rounded, size: 16, color: AppColors.lime),
                      label: Text(state.tr('edu_add_module'), style: AppTheme.heading(false, size: 11.5, color: Colors.white)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.ink,
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                      ),
                    ),
                    ElevatedButton.icon(
                      onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => AdminScreen(appState: state))).then((_) => _loadModules()),
                      icon: const Icon(Icons.admin_panel_settings_rounded, size: 16, color: Colors.white),
                      label: const Text('Panel Admin', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: Colors.white)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.cobalt,
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                      ),
                    ),
                  ],
                ],
              ),

              const SizedBox(height: 18),

              if (_loading)
                const Padding(
                  padding: EdgeInsets.all(40),
                  child: Center(child: CircularProgressIndicator(color: Colors.white)),
                )
              else if (_error != null)
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20)),
                  child: Column(
                    children: [
                      Text(_error!, style: AppTheme.body(false, size: 12, color: AppColors.danger)),
                      TextButton(onPressed: _loadModules, child: Text(state.tr('refresh'))),
                    ],
                  ),
                )
              else if (_modules.isEmpty)
                Container(
                  padding: const EdgeInsets.all(28),
                  decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.92), borderRadius: BorderRadius.circular(24)),
                  child: Text(state.tr('edu_empty'), textAlign: TextAlign.center),
                )
              else
                ..._modules.map((m) => _BentoModuleCard(
                      modul: m,
                      state: state,
                      onTap: () => _openModule(m),
                    )),

              if (_myRequests.isNotEmpty) ...[
                const SizedBox(height: 18),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.cardBg(isDark).withValues(alpha: 0.94),
                    borderRadius: BorderRadius.circular(22),
                    border: Border.all(color: AppColors.border(isDark)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(state.tr('edu_request_history'), style: AppTheme.heading(isDark, size: 15)),
                      const SizedBox(height: 10),
                      ..._myRequests.take(5).map((r) {
                        final badgeColor = r.status == 'approved'
                            ? AppColors.emerald
                            : r.status == 'rejected'
                                ? AppColors.danger
                                : AppColors.amber;
                        return Container(
                          margin: const EdgeInsets.only(bottom: 8),
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppColors.softBg(isDark),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: AppColors.border(isDark)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(child: Text(r.tema, style: AppTheme.heading(isDark, size: 13))),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: badgeColor.withValues(alpha: 0.15),
                                      borderRadius: BorderRadius.circular(99),
                                    ),
                                    child: Text(r.status.toUpperCase(), style: AppTheme.heading(false, size: 9, color: badgeColor)),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 3),
                              Text('${r.kategori} • ${r.createdAt}', style: AppTheme.body(isDark, size: 10.5, color: AppColors.textSecondary(isDark))),
                              if (r.adminNote != null && r.adminNote!.isNotEmpty) ...[
                                const SizedBox(height: 4),
                                Text('Catatan Admin: ${r.adminNote}', style: AppTheme.body(isDark, size: 11, color: AppColors.sky)),
                              ],
                            ],
                          ),
                        );
                      }),
                    ],
                  ),
                ),
              ],

              const SizedBox(height: 14),
              Center(
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(99),
                  child: BackdropFilter(
                    filter: ImageFilter.blur(sigmaX: 3, sigmaY: 3),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      decoration: BoxDecoration(
                        color: const Color(0xFF105EB2).withValues(alpha: 0.58),
                        borderRadius: BorderRadius.circular(99),
                        border: Border.all(color: Colors.white.withValues(alpha: 0.35)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            state.tr('edu_verified_sources'),
                            style: AppTheme.body(false, size: 11.5, weight: FontWeight.w500, color: Colors.white),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.22),
                              borderRadius: BorderRadius.circular(99),
                            ),
                            child: Text('+${_modules.length}', style: AppTheme.heading(false, size: 11, color: Colors.white)),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _BentoModuleCard extends StatefulWidget {
  final EdukasiModule modul;
  final AppState state;
  final VoidCallback onTap;

  const _BentoModuleCard({
    required this.modul,
    required this.state,
    required this.onTap,
  });

  @override
  State<_BentoModuleCard> createState() => _BentoModuleCardState();
}

class _BentoModuleCardState extends State<_BentoModuleCard> {
  bool _pressed = false;

  @override
  Widget build(BuildContext context) {
    final modul = widget.modul;
    final state = widget.state;
    final isGlass = modul.variant == 'glass' || modul.variant == 'glass-dark';
    final isDarkBlue = modul.variant == 'glass-dark';
    final isChart = modul.variant == 'chart';

    final bgColor = isDarkBlue
        ? const Color(0xFF0F172A).withValues(alpha: 0.88)
        : isGlass
            ? const Color(0xFF2563EB).withValues(alpha: 0.86)
            : (state.isDark ? AppColors.darkCard.withValues(alpha: 0.94) : Colors.white.withValues(alpha: 0.92));

    final titleColor = isGlass ? Colors.white : AppColors.textPrimary(state.isDark);
    final descColor = isGlass ? Colors.white.withValues(alpha: 0.84) : AppColors.textSecondary(state.isDark);

    return GestureDetector(
      onTapDown: (_) => setState(() => _pressed = true),
      onTapUp: (_) => setState(() => _pressed = false),
      onTapCancel: () => setState(() => _pressed = false),
      onTap: widget.onTap,
      child: AnimatedScale(
        scale: _pressed ? 1.03 : 1.0,
        duration: const Duration(milliseconds: 280),
        curve: Curves.easeOutCubic,
        child: Container(
          margin: const EdgeInsets.only(bottom: 14),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(24),
            child: BackdropFilter(
              filter: ImageFilter.blur(sigmaX: 3, sigmaY: 3),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 220),
                curve: Curves.easeOutCubic,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(
                    color: _pressed ? AppColors.lime : Colors.white.withValues(alpha: isGlass ? 0.42 : 0.75),
                    width: _pressed ? 1.6 : 1.0,
                  ),
                ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                              decoration: BoxDecoration(
                                color: isGlass
                                    ? Colors.white.withValues(alpha: 0.2)
                                    : (modul.isTextModule ? AppColors.lime.withValues(alpha: 0.45) : AppColors.sky.withValues(alpha: 0.12)),
                                borderRadius: BorderRadius.circular(99),
                              ),
                              child: Text(
                                modul.isTextModule ? state.tr('edu_read_module') : state.tr('edu_external_link'),
                                style: AppTheme.heading(false, size: 10, color: isGlass ? Colors.white : (modul.isTextModule ? AppColors.ink : AppColors.sky)),
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(modul.title, style: AppTheme.heading(false, size: 18, color: titleColor, height: 1.25)),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        width: 34,
                        height: 34,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: isGlass ? Colors.white.withValues(alpha: 0.18) : const Color(0xFFF3F4F6),
                        ),
                        child: Icon(
                          modul.isTextModule ? Icons.menu_book_rounded : Icons.language_rounded,
                          size: 16,
                          color: isGlass ? Colors.white : AppColors.muted,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(modul.description, style: AppTheme.body(false, size: 12.5, color: descColor, height: 1.5)),
                  if (isChart) ...[
                    const SizedBox(height: 14),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: List.generate(18, (b) {
                        final heights = [14.0, 24.0, 18.0, 30.0, 22.0, 16.0, 28.0, 32.0, 20.0, 26.0, 15.0, 22.0, 12.0, 18.0, 10.0, 16.0, 22.0, 14.0];
                        return Container(
                          width: 4,
                          height: heights[b],
                          decoration: BoxDecoration(
                            color: b < 9 ? AppColors.sky : const Color(0xFFE5E7EB),
                            borderRadius: BorderRadius.circular(4),
                          ),
                        );
                      }),
                    ),
                  ],
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(modul.tag, style: AppTheme.body(false, size: 11.5, weight: FontWeight.w600, color: descColor)),
                      Row(
                        children: [
                          Container(
                            width: 34,
                            height: 34,
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: isGlass ? Colors.white.withValues(alpha: 0.22) : const Color(0xFFF3F4F6),
                            ),
                            child: Text(modul.stat, style: AppTheme.heading(false, size: 11.5, color: titleColor)),
                          ),
                          const SizedBox(width: 6),
                          Container(
                            width: 34,
                            height: 34,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: isGlass ? Colors.white : AppColors.ink,
                            ),
                            child: Icon(
                              modul.isTextModule ? Icons.chrome_reader_mode_rounded : Icons.north_east_rounded,
                              size: 16,
                              color: isGlass ? AppColors.ink : Colors.white,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
      ),
    );
  }
}
