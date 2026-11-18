import 'dart:async';

import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../core/app_state.dart';
import '../core/theme.dart';
import '../models/digisafe_models.dart';
import '../widgets/shared_widgets.dart';
import 'auth_screens.dart';

class ChatInboxScreen extends StatefulWidget {
  final AppState appState;
  final int? initialPartnerId;

  const ChatInboxScreen({super.key, required this.appState, this.initialPartnerId});

  @override
  State<ChatInboxScreen> createState() => _ChatInboxScreenState();
}

class _ChatInboxScreenState extends State<ChatInboxScreen> with SingleTickerProviderStateMixin {
  late final TabController _tabCtrl;
  bool _loading = true;
  bool _serverUnsupported = false;

  List<ChatThreadItem> _threads = [];
  List<ChatRequestItem> _requests = [];
  List<Map<String, dynamic>> _searchUsers = [];
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 3, vsync: this);
    _loadSummary();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadSummary() async {
    if (!widget.appState.isAuthenticated) {
      setState(() => _loading = false);
      return;
    }

    setState(() => _loading = _threads.isEmpty);
    try {
      final res = await widget.appState.api.getOptional('/chat/summary');
      if (res == null) {
        if (mounted) setState(() => _serverUnsupported = true);
        return;
      }
      final rawThreads = res['threads'] as List? ?? [];
      final rawReqs = res['requests'] as List? ?? [];
      if (mounted) {
        setState(() {
          _serverUnsupported = false;
          _threads = rawThreads.map((e) => ChatThreadItem.fromJson(e as Map<String, dynamic>)).toList();
          _requests = rawReqs.map((e) => ChatRequestItem.fromJson(e as Map<String, dynamic>)).toList();
        });
      }
      await widget.appState.refreshUnreadCount();
      if (widget.initialPartnerId != null && mounted) {
        _openPartnerById(widget.initialPartnerId!);
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _searchPeople([String q = '']) async {
    try {
      final res = await widget.appState.api.getOptional('/chat/users?q=${Uri.encodeQueryComponent(q.trim())}');
      if (res == null) return;
      final list = (res['users'] as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
      if (mounted) setState(() => _searchUsers = list);
    } catch (_) {}
  }

  void _openPartnerById(int userId) {
    ChatUserCompact? partner;
    for (final t in _threads) {
      if (t.user.id == userId) {
        partner = t.user;
        break;
      }
    }
    if (partner != null) {
      _openChat(partner);
    }
  }

  void _openChat(ChatUserCompact partner) {
    Navigator.of(context)
        .push(MaterialPageRoute(builder: (_) => _ChatDetailScreen(appState: widget.appState, partner: partner)))
        .then((_) => _loadSummary());
  }

  Future<void> _respondFriend(int userId, bool accept) async {
    try {
      final ep = accept ? '/friends/accept/$userId' : '/friends/decline/$userId';
      await widget.appState.api.post(ep);
      await _loadSummary();
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final isEn = state.isEn;

    if (!state.isAuthenticated) {
      return Scaffold(
        backgroundColor: AppColors.softBg(isDark),
        appBar: AppBar(title: Text(state.tr('chat_inbox'), style: AppTheme.heading(isDark, size: 16))),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const CircleAvatar(radius: 38, backgroundImage: AssetImage('assets/images/bot-digika.jpg')),
                const SizedBox(height: 16),
                Text(isEn ? 'Sign in to access Social Chat & Bot Digika' : 'Masuk untuk mengakses Kotak Masuk & Bot Digika', textAlign: TextAlign.center, style: AppTheme.heading(isDark, size: 17)),
                const SizedBox(height: 14),
                ElevatedButton(
                  onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => LoginScreen(appState: state))),
                  style: ElevatedButton.styleFrom(backgroundColor: AppColors.cobalt, foregroundColor: Colors.white, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99))),
                  child: Text(state.tr('login')),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: AppColors.softBg(isDark),
      appBar: AppBar(
        title: Text(state.tr('chat_inbox'), style: AppTheme.heading(isDark, size: 16)),
        bottom: TabBar(
          controller: _tabCtrl,
          onTap: (idx) {
            if (idx == 2 && _searchUsers.isEmpty) _searchPeople();
          },
          indicatorColor: AppColors.sky,
          labelColor: AppColors.sky,
          tabs: [
            Tab(text: isEn ? 'Chats (${_threads.length})' : 'Obrolan (${_threads.length})'),
            Tab(text: isEn ? 'Requests (${_requests.length})' : 'Permintaan (${_requests.length})'),
            Tab(text: isEn ? 'Find Users' : 'Cari Teman'),
          ],
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _serverUnsupported
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Text(
                      isEn
                          ? 'Social chat API endpoint is waiting for latest server sync. Please refresh once hosted API routes are uploaded.'
                          : 'Endpoint API Social Chat & Bot Digika siap digunakan setelah file routes/api.php terbaru di-upload ke hosting.',
                      textAlign: TextAlign.center,
                      style: AppTheme.body(isDark, color: AppColors.textSecondary(isDark)),
                    ),
                  ),
                )
              : TabBarView(
                  controller: _tabCtrl,
                  children: [
                    // Tab 1: Threads + Bot Digika
                    RefreshIndicator(
                      onRefresh: _loadSummary,
                      child: _threads.isEmpty
                          ? ListView(
                              padding: const EdgeInsets.all(32),
                              children: [
                                const Center(child: CircleAvatar(radius: 36, backgroundImage: AssetImage('assets/images/bot-digika.jpg'))),
                                const SizedBox(height: 14),
                                Text(
                                  isEn ? 'No conversations yet. Find friends or check Bot Digika notifications here!' : 'Belum ada percakapan. Cari sesama warga siber atau terima notifikasi otomatis dari Bot Digika di sini!',
                                  textAlign: TextAlign.center,
                                  style: AppTheme.body(isDark, color: AppColors.textSecondary(isDark)),
                                ),
                              ],
                            )
                          : ListView.builder(
                              padding: const EdgeInsets.all(14),
                              itemCount: _threads.length,
                              itemBuilder: (_, i) {
                                final t = _threads[i];
                                final u = t.user;
                                return GestureDetector(
                                  onTap: () => _openChat(u),
                                  child: Container(
                                    margin: const EdgeInsets.only(bottom: 10),
                                    padding: const EdgeInsets.all(14),
                                    decoration: BoxDecoration(
                                      color: AppColors.cardBg(isDark),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: u.isBot ? AppColors.sky.withValues(alpha: 0.45) : AppColors.border(isDark)),
                                    ),
                                    child: Row(
                                      children: [
                                        UserAvatarWidget(
                                          avatarUrl: u.avatarUrl,
                                          initial: u.inisial,
                                          nameSeed: u.nama,
                                          isOwner: u.isOwner,
                                          isBot: u.isBot,
                                          size: 48,
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Row(
                                                children: [
                                                  Flexible(child: Text(u.nama, maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 14))),
                                                  const SizedBox(width: 6),
                                                  if (u.isBot)
                                                    Container(
                                                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                                      decoration: BoxDecoration(color: AppColors.skySoft, borderRadius: BorderRadius.circular(99)),
                                                      child: Text('BOT', style: AppTheme.heading(false, size: 9, color: AppColors.sky)),
                                                    ),
                                                ],
                                              ),
                                              const SizedBox(height: 3),
                                              Text(
                                                t.lastMessage ?? (isEn ? 'Connected' : 'Sudah berteman'),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                                style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark)),
                                              ),
                                            ],
                                          ),
                                        ),
                                        Column(
                                          crossAxisAlignment: CrossAxisAlignment.end,
                                          children: [
                                            Text(t.lastTime, style: AppTheme.body(isDark, size: 10, color: AppColors.textSecondary(isDark))),
                                            const SizedBox(height: 6),
                                            if (t.unreadCount > 0)
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                decoration: BoxDecoration(color: AppColors.cobalt, borderRadius: BorderRadius.circular(99)),
                                                child: Text('${t.unreadCount}', style: AppTheme.heading(false, size: 10, color: Colors.white)),
                                              ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                    ),

                    // Tab 2: Pending Friend Requests
                    _requests.isEmpty
                        ? Center(child: Text(isEn ? 'No pending requests.' : 'Tidak ada permintaan tertunda.', style: AppTheme.body(isDark, color: AppColors.textSecondary(isDark))))
                        : ListView.builder(
                            padding: const EdgeInsets.all(14),
                            itemCount: _requests.length,
                            itemBuilder: (_, i) {
                              final req = _requests[i];
                              return Container(
                                margin: const EdgeInsets.only(bottom: 10),
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: AppColors.cardBg(isDark),
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(color: AppColors.border(isDark)),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        UserAvatarWidget(avatarUrl: req.user.avatarUrl, initial: req.user.inisial, nameSeed: req.user.nama, size: 42),
                                        const SizedBox(width: 10),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(req.user.nama, style: AppTheme.heading(isDark, size: 13.5)),
                                              Text('@${req.user.username} • ${req.time}', style: AppTheme.body(isDark, size: 10.5, color: AppColors.textSecondary(isDark))),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                    if (req.messagePreview != null && req.messagePreview!.isNotEmpty) ...[
                                      const SizedBox(height: 8),
                                      Text('"${req.messagePreview}"', style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark))),
                                    ],
                                    const SizedBox(height: 10),
                                    Row(
                                      children: [
                                        Expanded(
                                          child: ElevatedButton(
                                            onPressed: () => _respondFriend(req.user.id, true),
                                            style: ElevatedButton.styleFrom(backgroundColor: AppColors.emerald, foregroundColor: Colors.white, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99))),
                                            child: Text(isEn ? 'Accept' : 'Konfirmasi'),
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: OutlinedButton(
                                            onPressed: () => _respondFriend(req.user.id, false),
                                            style: OutlinedButton.styleFrom(foregroundColor: AppColors.danger, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99))),
                                            child: Text(isEn ? 'Decline' : 'Tolak'),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),

                    // Tab 3: Search Citizens
                    Column(
                      children: [
                        Padding(
                          padding: const EdgeInsets.all(14),
                          child: TextField(
                            controller: _searchCtrl,
                            onSubmitted: _searchPeople,
                            decoration: InputDecoration(
                              hintText: isEn ? 'Search user or role...' : 'Cari nama, username, atau role...',
                              prefixIcon: const Icon(Icons.search_rounded),
                              suffixIcon: IconButton(icon: const Icon(Icons.send_rounded), onPressed: () => _searchPeople(_searchCtrl.text)),
                              filled: true,
                              fillColor: AppColors.cardBg(isDark),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide(color: AppColors.border(isDark))),
                            ),
                          ),
                        ),
                        Expanded(
                          child: ListView.builder(
                            padding: const EdgeInsets.symmetric(horizontal: 14),
                            itemCount: _searchUsers.length,
                            itemBuilder: (_, i) {
                              final row = _searchUsers[i];
                              final u = ChatUserCompact.fromJson(row['user'] as Map<String, dynamic>);
                              return ListTile(
                                leading: UserAvatarWidget(avatarUrl: u.avatarUrl, initial: u.inisial, nameSeed: u.nama, isOwner: u.isOwner, size: 40),
                                title: Text(u.nama, style: AppTheme.heading(isDark, size: 13.5)),
                                subtitle: Text('@${u.username}', style: AppTheme.body(isDark, size: 11, color: AppColors.textSecondary(isDark))),
                                trailing: IconButton(
                                  icon: const Icon(Icons.chat_bubble_outline_rounded, color: AppColors.sky),
                                  onPressed: () => _openChat(u),
                                ),
                                onTap: () => _openChat(u),
                              );
                            },
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
    );
  }
}

class _ChatDetailScreen extends StatefulWidget {
  final AppState appState;
  final ChatUserCompact partner;

  const _ChatDetailScreen({required this.appState, required this.partner});

  @override
  State<_ChatDetailScreen> createState() => _ChatDetailScreenState();
}

class _ChatDetailScreenState extends State<_ChatDetailScreen> {
  final _msgCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();
  List<ChatMessageItem> _messages = [];
  String _relation = 'none';
  bool _loading = true;
  bool _sending = false;
  Timer? _pollTimer;

  @override
  void initState() {
    super.initState();
    _loadMessages();
    _pollTimer = Timer.periodic(const Duration(seconds: 5), (_) => _loadMessages(silent: true));
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _msgCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadMessages({bool silent = false}) async {
    if (!silent) setState(() => _loading = true);
    try {
      final res = await widget.appState.api.get('/chat/messages/${widget.partner.id}');
      final list = (res['messages'] as List? ?? []).map((e) => ChatMessageItem.fromJson(e as Map<String, dynamic>)).toList();
      if (mounted) {
        setState(() {
          _messages = list;
          _relation = (res['relation'] ?? 'none').toString();
        });
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (_scrollCtrl.hasClients) {
            _scrollCtrl.jumpTo(_scrollCtrl.position.maxScrollExtent);
          }
        });
      }
      widget.appState.refreshUnreadCount();
    } catch (_) {
    } finally {
      if (mounted && !silent) setState(() => _loading = false);
    }
  }

  Future<void> _send() async {
    final text = _msgCtrl.text.trim();
    if (text.isEmpty || _sending) return;
    setState(() => _sending = true);
    try {
      await widget.appState.api.post('/chat/messages/${widget.partner.id}', {'message': text});
      _msgCtrl.clear();
      await _loadMessages(silent: true);
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = widget.appState.isDark;
    final isEn = widget.appState.isEn;
    final p = widget.partner;

    return Scaffold(
      backgroundColor: AppColors.softBg(isDark),
      appBar: AppBar(
        titleSpacing: 0,
        title: Row(
          children: [
            UserAvatarWidget(avatarUrl: p.avatarUrl, initial: p.inisial, nameSeed: p.nama, isOwner: p.isOwner, isBot: p.isBot, size: 36),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(p.nama, maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 14.5)),
                  Text(
                    p.isBot
                        ? (isEn ? 'Automated Notification Bot' : 'Sistem Notifikasi Otomatis')
                        : '@${p.username} • $_relation',
                    style: AppTheme.body(isDark, size: 10.5, color: AppColors.textSecondary(isDark)),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
      body: Column(
        children: [
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : ListView.builder(
                    controller: _scrollCtrl,
                    padding: const EdgeInsets.all(14),
                    itemCount: _messages.length,
                    itemBuilder: (_, i) {
                      final m = _messages[i];
                      return Align(
                        alignment: m.isMine ? Alignment.centerRight : Alignment.centerLeft,
                        child: Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                          constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.78),
                          decoration: BoxDecoration(
                            color: m.isMine ? AppColors.cobalt : AppColors.cardBg(isDark),
                            borderRadius: BorderRadius.circular(18),
                            border: m.isMine ? null : Border.all(color: AppColors.border(isDark)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                m.message,
                                style: AppTheme.body(isDark, size: 13, color: m.isMine ? Colors.white : AppColors.textPrimary(isDark), height: 1.45),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                m.time,
                                style: AppTheme.body(false, size: 9.5, color: m.isMine ? Colors.white70 : AppColors.textSecondary(isDark)),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ),

          if (p.isBot)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.cardBg(isDark),
                border: Border(top: BorderSide(color: AppColors.border(isDark))),
              ),
              child: SafeArea(
                top: false,
                child: Column(
                  children: [
                    Text(
                      isEn ? 'Bot Digika sends automated notifications and does not accept replies.' : 'Bot Digika adalah asisten otomatis sistem & tidak menerima balasan langsung.',
                      textAlign: TextAlign.center,
                      style: AppTheme.body(isDark, size: 11.5, color: AppColors.textSecondary(isDark)),
                    ),
                    const SizedBox(height: 8),
                    ElevatedButton.icon(
                      onPressed: () {
                        widget.appState.setTab(1); // Edukasi tab
                        Navigator.of(context).pop();
                        Navigator.of(context).maybePop();
                      },
                      icon: const Icon(Icons.menu_book_rounded, size: 16, color: AppColors.ink),
                      label: Text(isEn ? 'Open Education Center' : 'Buka Pusat Edukasi', style: AppTheme.heading(false, size: 12, color: AppColors.ink)),
                      style: ElevatedButton.styleFrom(backgroundColor: AppColors.lime, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99))),
                    ),
                  ],
                ),
              ),
            )
          else
            Container(
              padding: EdgeInsets.fromLTRB(12, 8, 12, MediaQuery.of(context).padding.bottom + 8),
              decoration: BoxDecoration(
                color: AppColors.cardBg(isDark),
                border: Border(top: BorderSide(color: AppColors.border(isDark))),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _msgCtrl,
                      decoration: InputDecoration(
                        hintText: isEn ? 'Type a message...' : 'Ketik pesan...',
                        filled: true,
                        fillColor: AppColors.softBg(isDark),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide.none),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  CircleAvatar(
                    backgroundColor: AppColors.cobalt,
                    child: IconButton(
                      onPressed: _sending ? null : _send,
                      icon: _sending
                          ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                          : const Icon(Icons.send_rounded, color: Colors.white, size: 18),
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
