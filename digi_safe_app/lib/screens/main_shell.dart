import 'package:flutter/material.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../widgets/curved_nav_bar.dart';
import '../widgets/shared_widgets.dart';
import 'auth_screens.dart';
import 'beranda_screen.dart';
import 'chat_screen.dart';
import 'edukasi_screen.dart';
import 'forum_screen.dart';
import 'minigame_hub_screen.dart';
import 'profile_screens.dart';

class MainShell extends StatefulWidget {
  final AppState appState;

  const MainShell({super.key, required this.appState});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> with WidgetsBindingObserver {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    widget.appState.addListener(_onStateChange);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    widget.appState.removeListener(_onStateChange);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      // Sinkronisasi otomatis dengan pembaruan Admin website saat kembali membuka APK
      widget.appState.bumpContent();
      widget.appState.refreshMe();
      widget.appState.refreshUnreadCount();
    }
  }

  void _onStateChange() {
    if (!mounted) return;
    final msg = widget.appState.oauthStatusMessage;
    if (msg != null && msg.isNotEmpty) {
      widget.appState.clearOauthStatus();
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
      });
    }

    final alert = widget.appState.latestChatAlert;
    if (alert != null) {
      widget.appState.consumeLatestAlert();
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        final isBot = alert['is_bot'] == true;
        final sender = (alert['sender_name'] ?? 'Pesan Baru').toString();
        final preview = (alert['preview'] ?? '').toString();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            behavior: SnackBarBehavior.floating,
            backgroundColor: isBot ? AppColors.cobalt : AppColors.ink,
            content: Row(
              children: [
                if (isBot)
                  const CircleAvatar(
                    radius: 14,
                    backgroundImage: AssetImage('assets/images/bot-digika.jpg'),
                  )
                else
                  const Icon(Icons.mark_chat_unread_rounded, color: AppColors.lime, size: 18),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(sender, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12, color: Colors.white)),
                      Text(preview, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 11, color: Colors.white70)),
                    ],
                  ),
                ),
              ],
            ),
            action: SnackBarAction(
              label: widget.appState.isEn ? 'Open' : 'Buka',
              textColor: AppColors.lime,
              onPressed: () {
                final pid = (alert['sender_id'] as num?)?.toInt();
                Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => ChatInboxScreen(appState: widget.appState, initialPartnerId: pid)),
                );
              },
            ),
          ),
        );
      });
    }
    setState(() {});
  }

  void _openAuth() {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => AuthWelcomeScreen(appState: widget.appState, popOnSuccess: true)),
    );
  }

  void _openInbox() {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => ChatInboxScreen(appState: widget.appState)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final currentIdx = state.currentTabIndex.clamp(0, 4);

    final navItems = [
      CurvedNavItem(
        label: state.tr('nav_beranda'),
        activeIcon: Icons.home_rounded,
        inactiveIcon: Icons.home_outlined,
      ),
      CurvedNavItem(
        label: state.tr('nav_edukasi'),
        activeIcon: Icons.menu_book_rounded,
        inactiveIcon: Icons.menu_book_outlined,
      ),
      CurvedNavItem(
        label: state.tr('nav_minigame'),
        activeIcon: Icons.sports_esports_rounded,
        inactiveIcon: Icons.sports_esports_outlined,
      ),
      CurvedNavItem(
        label: state.tr('nav_forum'),
        activeIcon: Icons.forum_rounded,
        inactiveIcon: Icons.forum_outlined,
      ),
      CurvedNavItem(
        label: state.tr('nav_profil'),
        activeIcon: Icons.person_rounded,
        inactiveIcon: Icons.person_outline_rounded,
      ),
    ];

    return Scaffold(
      extendBody: true,
      backgroundColor: AppColors.scaffoldBg(isDark),
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            // Sticky Header Bersih (TANPA tombol hamburger kanan paling pojok sesuai instruksi APK!)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              decoration: BoxDecoration(
                color: AppColors.cardBg(isDark),
                border: Border(bottom: BorderSide(color: AppColors.border(isDark))),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: GestureDetector(
                      onTap: () => state.setTab(0),
                      child: Row(
                        children: [
                          Container(
                            width: 34,
                            height: 34,
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(10),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.05),
                                  blurRadius: 8,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Image.asset('assets/images/logo.png', fit: BoxFit.contain),
                          ),
                          const SizedBox(width: 8),
                          Flexible(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Digi-Safe', maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 15, height: 1)),
                                Text(
                                  'PURWAKARTA',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: AppTheme.body(
                                    isDark,
                                    size: 8.5,
                                    weight: FontWeight.w700,
                                    color: AppColors.textSecondary(isDark),
                                  ).copyWith(letterSpacing: 0.9),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 6),

                  // Quick ID / EN pill
                  GestureDetector(
                    onTap: () => state.toggleGuestLocale(),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                      decoration: BoxDecoration(
                        color: AppColors.softBg(isDark),
                        borderRadius: BorderRadius.circular(99),
                        border: Border.all(color: AppColors.border(isDark)),
                      ),
                      child: Text(
                        state.locale.toUpperCase(),
                        style: AppTheme.heading(isDark, size: 10, color: AppColors.sky),
                      ),
                    ),
                  ),
                  const SizedBox(width: 5),

                  // Quick Dark / Light toggle
                  GestureDetector(
                    onTap: () => state.toggleTheme(),
                    child: Container(
                      width: 30,
                      height: 30,
                      decoration: BoxDecoration(
                        color: AppColors.softBg(isDark),
                        shape: BoxShape.circle,
                        border: Border.all(color: AppColors.border(isDark)),
                      ),
                      child: Icon(
                        isDark ? Icons.light_mode_rounded : Icons.dark_mode_rounded,
                        size: 15,
                        color: isDark ? AppColors.lime : AppColors.ink,
                      ),
                    ),
                  ),
                  const SizedBox(width: 5),

                  // Chat / Kotak Masuk & Bot Digika
                  GestureDetector(
                    onTap: _openInbox,
                    child: Stack(
                      clipBehavior: Clip.none,
                      children: [
                        Container(
                          width: 30,
                          height: 30,
                          decoration: BoxDecoration(
                            color: AppColors.softBg(isDark),
                            shape: BoxShape.circle,
                            border: Border.all(color: AppColors.border(isDark)),
                          ),
                          child: Icon(
                            Icons.chat_bubble_outline_rounded,
                            size: 14.5,
                            color: AppColors.textPrimary(isDark),
                          ),
                        ),
                        if (state.unreadChatBadge > 0)
                          Positioned(
                            top: -3,
                            right: -3,
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                              decoration: BoxDecoration(
                                color: AppColors.cobalt,
                                borderRadius: BorderRadius.circular(99),
                              ),
                              child: Text(
                                '${state.unreadChatBadge}',
                                style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.w800),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 5),

                  if (state.isAuthenticated)
                    GestureDetector(
                      onTap: () => state.setTab(4),
                      child: Container(
                        padding: const EdgeInsets.fromLTRB(3, 3, 8, 3),
                        decoration: BoxDecoration(
                          color: AppColors.softBg(isDark),
                          borderRadius: BorderRadius.circular(99),
                          border: Border.all(color: AppColors.border(isDark)),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            UserAvatarWidget(
                              avatarUrl: state.user!.avatarUrl,
                              initial: state.user!.inisial,
                              nameSeed: state.user!.namaLengkap,
                              isOwner: state.user!.isOwner,
                              size: 24,
                              ringColor: AppColors.lime,
                            ),
                            const SizedBox(width: 5),
                            ConstrainedBox(
                              constraints: const BoxConstraints(maxWidth: 56),
                              child: Text(
                                state.user!.namaLengkap.split(' ').first,
                                overflow: TextOverflow.ellipsis,
                                style: AppTheme.heading(isDark, size: 10.5),
                              ),
                            ),
                          ],
                        ),
                      ),
                    )
                  else
                    GestureDetector(
                      onTap: _openAuth,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 7),
                        decoration: BoxDecoration(
                          color: AppColors.lime,
                          borderRadius: BorderRadius.circular(99),
                        ),
                        child: Text(
                          state.tr('join'),
                          style: AppTheme.heading(false, size: 10.5, color: AppColors.ink),
                        ),
                      ),
                    ),
                ],
              ),
            ),

            // Body Content Area (extra bottom padding disediakan oleh masing-masing layar atau wrapper agar tidak tertutup CurvedNotchBottomBar)
            Expanded(
              child: IndexedStack(
                index: currentIdx,
                children: [
                  BerandaScreen(appState: state),
                  EdukasiScreen(appState: state),
                  MinigameHubScreen(appState: state),
                  ForumScreen(appState: state),
                  ProfileHubTabScreen(appState: state),
                ],
              ),
            ),
          ],
        ),
      ),

      // Curved Notched Mobile Navigation Bar (Persis Gambar 1, Khusus APK!)
      bottomNavigationBar: CurvedNotchBottomBar(
        items: navItems,
        currentIndex: currentIdx,
        isDark: isDark,
        onTap: (idx) => state.setTab(idx),
      ),
    );
  }
}
