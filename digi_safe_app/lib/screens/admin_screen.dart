import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../core/app_state.dart';
import '../core/theme.dart';
import '../models/digisafe_models.dart';
import '../widgets/shared_widgets.dart';

class AdminScreen extends StatefulWidget {
  final AppState appState;

  const AdminScreen({super.key, required this.appState});

  @override
  State<AdminScreen> createState() => _AdminScreenState();
}

class _AdminScreenState extends State<AdminScreen> with SingleTickerProviderStateMixin {
  late final TabController _tabCtrl;
  bool _loading = true;
  bool _needsServerSync = false;

  Map<String, dynamic> _stats = {};
  List<EdukasiModule> _modules = [];
  List<EdukasiRequestItem> _requests = [];
  List<MinigameLevel> _levels = [];
  List<DbMinigameQuestion> _questions = [];
  List<ForumThread> _forums = [];
  List<UserProfile> _users = [];

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 4, vsync: this);
    _loadOverview();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadOverview() async {
    setState(() => _loading = true);
    try {
      final res = await widget.appState.api.getOptional('/admin/overview');
      if (res != null && mounted) {
        setState(() {
          _needsServerSync = false;
          _stats = Map<String, dynamic>.from(res['stats'] ?? {});
          _modules = (res['modules'] as List? ?? []).map((e) => EdukasiModule.fromJson(e as Map<String, dynamic>)).toList();
          _requests = (res['edukasi_requests'] as List? ?? []).map((e) => EdukasiRequestItem.fromJson(e as Map<String, dynamic>)).toList();
          _levels = (res['levels'] as List? ?? []).map((e) => MinigameLevel.fromJson(e as Map<String, dynamic>)).toList();
          _questions = (res['questions'] as List? ?? []).map((e) => DbMinigameQuestion.fromJson(e as Map<String, dynamic>)).toList();
          _forums = (res['forums'] as List? ?? []).map((e) => ForumThread.fromJson(e as Map<String, dynamic>)).toList();
          _users = (res['users'] as List? ?? []).map((e) => UserProfile.fromJson(e as Map<String, dynamic>)).toList();
        });
        return;
      }

      // Fallback ringkasan dasar jika hosting masih memakai routes/api.php versi awal
      final sum = await widget.appState.api.get('/admin/summary');
      final edu = await widget.appState.api.get('/edukasi');
      final frm = await widget.appState.api.get('/forum');
      final usrs = await widget.appState.api.get('/users');
      if (mounted) {
        setState(() {
          _needsServerSync = true;
          _stats = Map<String, dynamic>.from(sum['stats'] ?? {});
          _modules = (edu['modules'] as List? ?? []).map((e) => EdukasiModule.fromJson(e as Map<String, dynamic>)).toList();
          _forums = (frm['forums'] as List? ?? []).map((e) => ForumThread.fromJson(e as Map<String, dynamic>)).toList();
          _users = (usrs['users'] as List? ?? []).map((e) => UserProfile.fromJson(e as Map<String, dynamic>)).toList();
        });
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _evaluateRequest(EdukasiRequestItem item, bool approve) async {
    final noteCtrl = TextEditingController();
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(approve ? 'Setujui Usulan Tema?' : 'Tolak Usulan Tema?'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Tema: "${item.tema}" oleh ${item.userName ?? 'Warga'}'),
            const SizedBox(height: 10),
            TextField(
              controller: noteCtrl,
              maxLines: 2,
              decoration: const InputDecoration(
                labelText: 'Catatan Admin (opsional untuk Bot Digika)',
                border: OutlineInputBorder(),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: approve ? AppColors.emerald : AppColors.danger, foregroundColor: Colors.white),
            child: Text(approve ? 'Setujui & Kirim Bot' : 'Tolak & Kirim Bot'),
          ),
        ],
      ),
    );
    if (ok != true) return;

    try {
      final path = '/admin/edukasi/requests/${item.id}/${approve ? 'approve' : 'reject'}';
      final res = await widget.appState.api.patch(path, {'admin_note': noteCtrl.text.trim()});
      widget.appState.bumpContent();
      await _loadOverview();
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('${res['message'] ?? 'Berhasil dievaluasi.'}')));
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _toggleStarForum(ForumThread f) async {
    try {
      await widget.appState.api.patch('/admin/forum/${f.id}/star');
      widget.appState.bumpContent();
      await _loadOverview();
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _deleteForum(ForumThread f) async {
    try {
      await widget.appState.api.delete('/admin/forum/${f.id}');
      widget.appState.bumpContent();
      await _loadOverview();
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _toggleUserRole(UserProfile u) async {
    final nextRole = u.isAdmin ? 'user' : 'admin';
    try {
      final res = await widget.appState.api.patch('/admin/users/${u.id}/role', {'role': nextRole});
      await _loadOverview();
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('${res['message'] ?? 'Hak akses diperbarui'}')));
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final pendingCount = _requests.where((r) => r.status == 'pending').length;

    return Scaffold(
      backgroundColor: AppColors.softBg(isDark),
      appBar: AppBar(
        title: Text(state.tr('admin_panel'), style: AppTheme.heading(isDark, size: 16)),
        actions: [
          IconButton(icon: const Icon(Icons.refresh_rounded), onPressed: _loadOverview),
        ],
        bottom: TabBar(
          controller: _tabCtrl,
          isScrollable: true,
          indicatorColor: AppColors.sky,
          labelColor: AppColors.sky,
          tabs: [
            Tab(text: 'Edukasi & Req ($pendingCount)'),
            Tab(text: 'Minigame (${_questions.length})'),
            Tab(text: 'Forum (${_forums.length})'),
            Tab(text: 'Users (${_users.length})'),
          ],
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                if (_needsServerSync)
                  Container(
                    width: double.infinity,
                    color: const Color(0xFFFFFBEB),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    child: const Text(
                      'Sebagian aksi lanjutan membutuhkan upload ulang routes/api.php & AdminApiController.php terbaru ke RumahWeb.',
                      style: TextStyle(fontSize: 11, color: Color(0xFF92400E)),
                    ),
                  ),
                Expanded(
                  child: TabBarView(
                    controller: _tabCtrl,
                    children: [
                      // Tab 1: Edukasi + Requests
                      RefreshIndicator(
                        onRefresh: _loadOverview,
                        child: ListView(
                          padding: const EdgeInsets.all(14),
                          children: [
                            Row(
                              children: [
                                Expanded(child: _StatPill(label: 'Modul', val: '${_stats['modules_count'] ?? _modules.length}', isDark: isDark)),
                                const SizedBox(width: 8),
                                Expanded(child: _StatPill(label: 'Req Pending', val: '$pendingCount', isDark: isDark)),
                                const SizedBox(width: 8),
                                Expanded(child: _StatPill(label: 'Warga', val: '${_stats['users_count'] ?? _users.length}', isDark: isDark)),
                              ],
                            ),
                            const SizedBox(height: 16),
                            Text('Request Tema Edukasi Pengguna', style: AppTheme.heading(isDark, size: 15)),
                            const SizedBox(height: 8),
                            if (_requests.isEmpty)
                              Text('Belum ada request tema edukasi.', style: AppTheme.body(isDark, color: AppColors.textSecondary(isDark)))
                            else
                              ..._requests.map((r) => Container(
                                    margin: const EdgeInsets.only(bottom: 10),
                                    padding: const EdgeInsets.all(14),
                                    decoration: BoxDecoration(
                                      color: AppColors.cardBg(isDark),
                                      borderRadius: BorderRadius.circular(18),
                                      border: Border.all(color: AppColors.border(isDark)),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            Expanded(child: Text(r.tema, style: AppTheme.heading(isDark, size: 14))),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                              decoration: BoxDecoration(
                                                color: r.status == 'approved'
                                                    ? AppColors.emerald.withValues(alpha: 0.16)
                                                    : r.status == 'rejected'
                                                        ? AppColors.danger.withValues(alpha: 0.16)
                                                        : AppColors.amber.withValues(alpha: 0.18),
                                                borderRadius: BorderRadius.circular(99),
                                              ),
                                              child: Text(
                                                r.status.toUpperCase(),
                                                style: AppTheme.heading(false, size: 9.5, color: r.status == 'approved' ? AppColors.emerald : r.status == 'rejected' ? AppColors.danger : AppColors.amber),
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 4),
                                        Text('Oleh ${r.userName ?? '-'} • ${r.kategori}', style: AppTheme.body(isDark, size: 11, color: AppColors.sky)),
                                        const SizedBox(height: 4),
                                        Text(r.deskripsi, style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark))),
                                        if (r.status == 'pending') ...[
                                          const SizedBox(height: 10),
                                          Row(
                                            children: [
                                              Expanded(
                                                child: ElevatedButton(
                                                  onPressed: () => _evaluateRequest(r, true),
                                                  style: ElevatedButton.styleFrom(backgroundColor: AppColors.emerald, foregroundColor: Colors.white, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99))),
                                                  child: const Text('Setujui'),
                                                ),
                                              ),
                                              const SizedBox(width: 8),
                                              Expanded(
                                                child: OutlinedButton(
                                                  onPressed: () => _evaluateRequest(r, false),
                                                  style: OutlinedButton.styleFrom(foregroundColor: AppColors.danger, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99))),
                                                  child: const Text('Tolak'),
                                                ),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ],
                                    ),
                                  )),
                            const SizedBox(height: 16),
                            Text('Daftar Modul Edukasi (${_modules.length})', style: AppTheme.heading(isDark, size: 15)),
                            const SizedBox(height: 8),
                            ..._modules.map((m) => Card(
                                  color: AppColors.cardBg(isDark),
                                  child: ListTile(
                                    title: Text(m.title, style: AppTheme.heading(isDark, size: 13.5)),
                                    subtitle: Text('${m.tag} • ${m.type.toUpperCase()}', style: AppTheme.body(isDark, size: 11)),
                                    trailing: IconButton(
                                      icon: const Icon(Icons.delete_outline_rounded, color: AppColors.danger),
                                      onPressed: () async {
                                        await widget.appState.api.delete('/admin/edukasi/${m.id}');
                                        widget.appState.bumpContent();
                                        _loadOverview();
                                      },
                                    ),
                                  ),
                                )),
                          ],
                        ),
                      ),

                      // Tab 2: Minigame Levels & Bank Soal
                      RefreshIndicator(
                        onRefresh: _loadOverview,
                        child: ListView(
                          padding: const EdgeInsets.all(14),
                          children: [
                            Text('Level Dinamis (${_levels.length})', style: AppTheme.heading(isDark, size: 15)),
                            const SizedBox(height: 8),
                            ..._levels.map((l) => Card(
                                  color: AppColors.cardBg(isDark),
                                  child: ListTile(
                                    title: Text(l.namaLevel, style: AppTheme.heading(isDark, size: 13.5)),
                                    subtitle: Text('${l.waktuDetik}s • +${l.poinBenar} / -${l.penaltiSalah} • ${l.hpAwal} HP', style: AppTheme.body(isDark, size: 11)),
                                  ),
                                )),
                            const SizedBox(height: 16),
                            Text('Bank Soal (${_questions.length})', style: AppTheme.heading(isDark, size: 15)),
                            const SizedBox(height: 8),
                            ..._questions.map((q) => Card(
                                  color: AppColors.cardBg(isDark),
                                  child: ListTile(
                                    title: Text(q.pertanyaan, maxLines: 2, overflow: TextOverflow.ellipsis, style: AppTheme.body(isDark, size: 12.5, weight: FontWeight.w600)),
                                    subtitle: Text('${q.gameLevel} • ${q.level} • Kunci ${q.kunci}', style: AppTheme.body(isDark, size: 10.5, color: AppColors.sky)),
                                    trailing: IconButton(
                                      icon: const Icon(Icons.delete_outline_rounded, color: AppColors.danger),
                                      onPressed: () async {
                                        await widget.appState.api.delete('/admin/minigame/${q.id}');
                                        _loadOverview();
                                      },
                                    ),
                                  ),
                                )),
                          ],
                        ),
                      ),

                      // Tab 3: Forum Moderation
                      RefreshIndicator(
                        onRefresh: _loadOverview,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(14),
                          itemCount: _forums.length,
                          itemBuilder: (_, i) {
                            final f = _forums[i];
                            return Card(
                              color: AppColors.cardBg(isDark),
                              child: ListTile(
                                title: Text(f.judul, style: AppTheme.heading(isDark, size: 13.5)),
                                subtitle: Text('Oleh ${f.authorName} • ${f.jawabanCount} balasan', style: AppTheme.body(isDark, size: 11)),
                                trailing: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    IconButton(
                                      icon: Icon(f.isStarred ? Icons.star_rounded : Icons.star_border_rounded, color: AppColors.amber),
                                      onPressed: () => _toggleStarForum(f),
                                    ),
                                    IconButton(
                                      icon: const Icon(Icons.delete_outline_rounded, color: AppColors.danger),
                                      onPressed: () => _deleteForum(f),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                      ),

                      // Tab 4: Users & Proteksi Owner
                      RefreshIndicator(
                        onRefresh: _loadOverview,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(14),
                          itemCount: _users.length,
                          itemBuilder: (_, i) {
                            final u = _users[i];
                            final canToggle = !u.isOwner && (state.user?.isOwner == true || !u.isAdmin);
                            return Card(
                              color: AppColors.cardBg(isDark),
                              child: ListTile(
                                leading: UserAvatarWidget(avatarUrl: u.avatarUrl, initial: u.inisial, nameSeed: u.namaLengkap, isOwner: u.isOwner, size: 38),
                                title: Row(
                                  children: [
                                    Flexible(child: Text(u.namaLengkap, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 13.5))),
                                    const SizedBox(width: 6),
                                    RoleBadgeWidget(isOwner: u.isOwner, isAdmin: u.isAdmin, compact: true),
                                  ],
                                ),
                                subtitle: Text('@${u.username} • ${u.specialtyRole ?? u.role}', style: AppTheme.body(isDark, size: 11)),
                                trailing: u.isOwner
                                    ? const Icon(Icons.lock_rounded, color: AppColors.amber, size: 18)
                                    : canToggle
                                        ? TextButton(
                                            onPressed: () => _toggleUserRole(u),
                                            child: Text(u.isAdmin ? 'Turunkan' : 'Jadikan Admin', style: const TextStyle(fontSize: 11)),
                                          )
                                        : null,
                              ),
                            );
                          },
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

class _StatPill extends StatelessWidget {
  final String label;
  final String val;
  final bool isDark;

  const _StatPill({required this.label, required this.val, required this.isDark});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.cardBg(isDark),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border(isDark)),
      ),
      child: Column(
        children: [
          Text(val, style: AppTheme.heading(isDark, size: 18, color: AppColors.sky)),
          Text(label, style: AppTheme.body(isDark, size: 10.5, color: AppColors.textSecondary(isDark))),
        ],
      ),
    );
  }
}
