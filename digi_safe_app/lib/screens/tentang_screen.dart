import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../widgets/shared_widgets.dart';

class TentangScreen extends StatefulWidget {
  final AppState appState;

  const TentangScreen({super.key, required this.appState});

  @override
  State<TentangScreen> createState() => _TentangScreenState();
}

class _TentangScreenState extends State<TentangScreen> {
  Map<String, dynamic> _developer = {
    'nama': 'Razief Afkar Rusli',
    'inisial': 'RA',
    'asal': 'Kelas XII RPL, SMK Negeri 2 Purwakarta',
    'deskripsi': 'Pengembang di balik platform Digi-Safe Purwakarta.',
    'socials': [],
  };
  List<Map<String, dynamic>> _visiMisi = [];
  String _mapsUrl = 'https://maps.google.com/?q=SMKN+2+Purwakarta';
  int _lastVersion = -1;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  @override
  void didUpdateWidget(covariant TentangScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.appState.contentVersion != _lastVersion) {
      _loadData();
    }
  }

  Future<void> _loadData() async {
    _lastVersion = widget.appState.contentVersion;
    try {
      final res = await widget.appState.api.get('/tentang');
      if (!mounted) return;
      setState(() {
        if (res['developer'] is Map) {
          _developer = Map<String, dynamic>.from(res['developer'] as Map);
        }
        _visiMisi = (res['visi_misi'] as List? ?? [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
        if (res['location'] is Map && res['location']['maps_url'] != null) {
          _mapsUrl = res['location']['maps_url'].toString();
        }
      });
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final socials = (_developer['socials'] as List? ?? [])
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          // 1. Hero Dokumen Editor bergaya pastel green (identik tentang/index.blade.php)
          Container(
            padding: const EdgeInsets.fromLTRB(16, 26, 16, 28),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: isDark
                    ? [const Color(0xFF0F172A), const Color(0xFF132A1E)]
                    : [AppColors.pastelGreen, AppColors.pastelDark, const Color(0xFFDCEEDC)],
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Tab header nama file
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: AppColors.cardBg(isDark),
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.description_outlined, size: 16, color: AppColors.sky),
                      const SizedBox(width: 6),
                      Text(state.tr('about_doc_title'), style: AppTheme.heading(isDark, size: 12)),
                    ],
                  ),
                ),

                // Container Putih Dokumen Editor
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: AppColors.cardBg(isDark),
                    borderRadius: const BorderRadius.only(
                      topRight: Radius.circular(22),
                      bottomLeft: Radius.circular(22),
                      bottomRight: Radius.circular(22),
                    ),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 20, offset: const Offset(0, 8)),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Toolbar editor
                      Row(
                        children: [
                          Icon(Icons.undo_rounded, size: 16, color: AppColors.textSecondary(isDark)),
                          const SizedBox(width: 10),
                          Icon(Icons.redo_rounded, size: 16, color: AppColors.textSecondary(isDark)),
                          const SizedBox(width: 12),
                          Text('Arial  ▾   14 pt  ▾', style: AppTheme.body(isDark, size: 11.5, weight: FontWeight.w600)),
                          const Spacer(),
                          Text('B   I   U', style: AppTheme.heading(isDark, size: 12)),
                        ],
                      ),
                      const Divider(height: 22),
                      Text(state.tr('about_heading'), style: AppTheme.heading(isDark, size: 18)),
                      const SizedBox(height: 10),
                      Text(
                        state.tr('about_body'),
                        style: AppTheme.body(isDark, size: 13, color: AppColors.textSecondary(isDark), height: 1.7),
                      ),
                    ],
                  ),
                ),

                // Floating Command Bar
                Transform.translate(
                  offset: const Offset(0, -14),
                  child: Center(
                    child: Container(
                      width: double.infinity,
                      margin: const EdgeInsets.symmetric(horizontal: 14),
                      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                      decoration: BoxDecoration(
                        color: AppColors.cardBg(isDark).withValues(alpha: 0.92),
                        borderRadius: BorderRadius.circular(22),
                        border: Border.all(color: Colors.white),
                        boxShadow: [
                          BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 16, offset: const Offset(0, 8)),
                        ],
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(state.tr('about_prompt'), style: AppTheme.body(isDark, size: 12.5, weight: FontWeight.w500)),
                          ),
                          Container(
                            width: 34,
                            height: 34,
                            decoration: const BoxDecoration(color: AppColors.ink, shape: BoxShape.circle),
                            child: const Icon(Icons.arrow_downward_rounded, size: 16, color: Colors.white),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

          // 2. Pengembang: Razief Afkar Rusli
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 26, 16, 0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: isDark ? AppColors.darkCard : AppColors.pastelGreen,
                    borderRadius: BorderRadius.circular(26),
                    border: Border.all(color: isDark ? AppColors.darkBorder : const Color(0xFFDCEEDC)),
                  ),
                  child: Column(
                    children: [
                      Container(
                        width: 96,
                        height: 96,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 5),
                        ),
                        clipBehavior: Clip.antiAlias,
                        child: Image.asset(
                          'assets/images/developer-razief.jpg',
                          fit: BoxFit.cover,
                          alignment: Alignment.topCenter,
                        ),
                      ),
                      const SizedBox(height: 14),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                        decoration: BoxDecoration(color: AppColors.cardBg(isDark), borderRadius: BorderRadius.circular(99)),
                        child: Text('• Creator & Lead Developer', style: AppTheme.heading(isDark, size: 11)),
                      ),
                      const SizedBox(height: 10),
                      Text('${_developer['nama'] ?? 'Razief Afkar Rusli'}', style: AppTheme.heading(isDark, size: 20)),
                      Text('${_developer['asal'] ?? ''}', style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark))),
                      const SizedBox(height: 14),
                      Wrap(
                        spacing: 6,
                        runSpacing: 6,
                        alignment: WrapAlignment.center,
                        children: ['Flutter Pure APK', 'Laravel API', 'Tailwind CSS', 'Cyber Security']
                            .map((tech) => Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(color: AppColors.cardBg(isDark), borderRadius: BorderRadius.circular(99)),
                                  child: Text(tech, style: AppTheme.heading(isDark, size: 10)),
                                ))
                            .toList(),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 22),
                DigiSectionKicker(text: state.tr('about_creator_badge'), isDark: isDark),
                const SizedBox(height: 6),
                Text(state.tr('about_dev_heading'), style: AppTheme.heading(isDark, size: 21)),
                const SizedBox(height: 8),
                Text(
                  '${_developer['deskripsi'] ?? ''}',
                  style: AppTheme.body(isDark, size: 13, color: AppColors.textSecondary(isDark), height: 1.55),
                ),
                const SizedBox(height: 14),

                // Social Links
                ...socials.map((soc) => GestureDetector(
                      onTap: () => launchUrl(Uri.parse('${soc['url']}'), mode: LaunchMode.externalApplication),
                      child: Container(
                        margin: const EdgeInsets.only(bottom: 10),
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppColors.cardBg(isDark),
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: AppColors.border(isDark)),
                        ),
                        child: Row(
                          children: [
                            Container(
                              width: 38,
                              height: 38,
                              decoration: BoxDecoration(
                                color: isDark ? AppColors.darkSurface : AppColors.pastelGreen,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Icon(
                                soc['type'] == 'instagram'
                                    ? Icons.camera_alt_outlined
                                    : soc['type'] == 'tiktok'
                                        ? Icons.music_note_rounded
                                        : Icons.public_rounded,
                                size: 18,
                                color: AppColors.textPrimary(isDark),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('${soc['label']}', style: AppTheme.body(isDark, size: 10.5, color: AppColors.textSecondary(isDark))),
                                  Text('${soc['handle']}', style: AppTheme.heading(isDark, size: 13)),
                                ],
                              ),
                            ),
                            const Icon(Icons.north_east_rounded, size: 16, color: AppColors.sky),
                          ],
                        ),
                      ),
                    )),
              ],
            ),
          ),

          // 3. Visi & Misi
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 26, 16, 0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                DigiSectionKicker(text: state.tr('about_vision_kicker'), isDark: isDark),
                const SizedBox(height: 6),
                Text(state.tr('about_vision_title'), style: AppTheme.heading(isDark, size: 20)),
                const SizedBox(height: 12),
                ..._visiMisi.map((vm) => Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: AppColors.cardBg(isDark),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: AppColors.border(isDark)),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 36,
                            height: 36,
                            alignment: Alignment.center,
                            decoration: BoxDecoration(color: AppColors.lime, borderRadius: BorderRadius.circular(10)),
                            child: Text('${vm['tahap']}', style: AppTheme.heading(false, size: 13, color: AppColors.ink)),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('${vm['judul']}', style: AppTheme.heading(isDark, size: 14)),
                                const SizedBox(height: 4),
                                Text('${vm['deskripsi']}', style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark), height: 1.5)),
                              ],
                            ),
                          ),
                        ],
                      ),
                    )),
              ],
            ),
          ),

          // 4. Lokasi SMKN 2 Purwakarta
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 36),
            child: GestureDetector(
              onTap: () => launchUrl(Uri.parse(_mapsUrl), mode: LaunchMode.externalApplication),
              child: Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppColors.cardBg(isDark),
                  borderRadius: BorderRadius.circular(22),
                  border: Border.all(color: AppColors.border(isDark)),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(color: AppColors.skySoft, borderRadius: BorderRadius.circular(14)),
                      child: const Icon(Icons.location_on_rounded, color: AppColors.sky),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(state.tr('about_location_kicker'), style: AppTheme.heading(isDark, size: 9.5, color: AppColors.sky)),
                          Text(state.tr('about_location_title'), style: AppTheme.heading(isDark, size: 14)),
                          Text('Jl. Jend. Ahmad Yani No. 98, Purwakarta', style: AppTheme.body(isDark, size: 11.5, color: AppColors.textSecondary(isDark))),
                        ],
                      ),
                    ),
                    const Icon(Icons.map_outlined, color: AppColors.sky),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

