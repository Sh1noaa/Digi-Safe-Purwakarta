import 'package:flutter/material.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../widgets/shared_widgets.dart';
import 'auth_screens.dart';
import 'beranda_screen.dart';
import 'edukasi_screen.dart';
import 'forum_screen.dart';
import 'minigame_hub_screen.dart';
import 'profile_screens.dart';
import 'tentang_screen.dart';
import 'testimoni_screen.dart';

class MainShell extends StatefulWidget {
  final AppState appState;

  const MainShell({super.key, required this.appState});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  bool _drawerOpen = false;

  @override
  void initState() {
    super.initState();
    widget.appState.addListener(_onStateChange);
  }

  @override
  void dispose() {
    widget.appState.removeListener(_onStateChange);
    super.dispose();
  }

  void _onStateChange() {
    final msg = widget.appState.oauthStatusMessage;
    if (msg != null && msg.isNotEmpty && mounted) {
      widget.appState.clearOauthStatus();
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
      });
    }
    if (mounted) setState(() {});
  }

  void _openAuth() {
    setState(() => _drawerOpen = false);
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => LoginScreen(appState: widget.appState)),
    );
  }

  void _openProfile() {
    setState(() => _drawerOpen = false);
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => ProfileScreen(appState: widget.appState)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final currentIdx = state.currentTabIndex;

    final navItems = [
      (label: state.tr('nav_beranda'), icon: Icons.home_rounded),
      (label: state.tr('nav_edukasi'), icon: Icons.menu_book_rounded),
      (label: state.tr('nav_minigame'), icon: Icons.sports_esports_rounded),
      (label: state.tr('nav_forum'), icon: Icons.forum_rounded),
      (label: state.tr('nav_testimoni'), icon: Icons.star_rate_rounded),
      (label: state.tr('nav_tentang'), icon: Icons.info_outline_rounded),
    ];

    return Scaffold(
      backgroundColor: AppColors.scaffoldBg(isDark),
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            // Sticky Navbar identik dengan header web
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: AppColors.cardBg(isDark),
                border: Border(bottom: BorderSide(color: AppColors.border(isDark))),
              ),
              child: Row(
                children: [
                  GestureDetector(
                    onTap: () => state.setTab(0),
                    child: Row(
                      children: [
                        Container(
                          width: 38,
                          height: 34,
                          padding: const EdgeInsets.all(8.5),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: const Color(0xFFE5E7EB)),
                          ),
                          child: Image.asset('assets/images/logo.png', fit: BoxFit.contain),
                        ),
                        const SizedBox(width: 9),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Digi-Safe', style: AppTheme.heading(isDark, size: 15.5, height: 1)),
                            Text(
                              'PURWAKARTA',
                              style: AppTheme.body(isDark, size: 9, weight: FontWeight.w600, color: AppColors.textSecondary(isDark)),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const Spacer(),

                  // Quick ID / EN pill untuk tamu maupun user
                  GestureDetector(
                    onTap: () => state.toggleGuestLocale(),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
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
                  const SizedBox(width: 7),

                  // Quick Dark / Light icon
                  GestureDetector(
                    onTap: () => state.toggleTheme(),
                    child: Container(
                      width: 32,
                      height: 32,
                      decoration: BoxDecoration(
                        color: AppColors.softBg(isDark),
                        shape: BoxShape.circle,
                        border: Border.all(color: AppColors.border(isDark)),
                      ),
                      child: Icon(
                        isDark ? Icons.light_mode_rounded : Icons.dark_mode_rounded,
                        size: 16,
                        color: isDark ? AppColors.lime : AppColors.ink,
                      ),
                    ),
                  ),
                  const SizedBox(width: 7),

                  if (state.isAuthenticated)
                    GestureDetector(
                      onTap: _openProfile,
                      child: Container(
                        padding: const EdgeInsets.fromLTRB(3, 3, 9, 3),
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
                              size: 26,
                              ringColor: AppColors.lime,
                            ),
                            const SizedBox(width: 6),
                            ConstrainedBox(
                              constraints: const BoxConstraints(maxWidth: 68),
                              child: Text(
                                state.user!.namaLengkap.split(' ').first,
                                overflow: TextOverflow.ellipsis,
                                style: AppTheme.heading(isDark, size: 11),
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
                        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 8),
                        decoration: BoxDecoration(
                          color: AppColors.lime,
                          borderRadius: BorderRadius.circular(99),
                        ),
                        child: Text(
                          state.tr('join'),
                          style: AppTheme.heading(false, size: 11, color: AppColors.ink),
                        ),
                      ),
                    ),
                  const SizedBox(width: 6),

                  GestureDetector(
                    onTap: () => setState(() => _drawerOpen = !_drawerOpen),
                    child: Container(
                      width: 34,
                      height: 34,
                      decoration: BoxDecoration(
                        color: AppColors.softBg(isDark),
                        shape: BoxShape.circle,
                        border: Border.all(color: AppColors.border(isDark)),
                      ),
                      child: Icon(
                        _drawerOpen ? Icons.close_rounded : Icons.menu_rounded,
                        size: 18,
                        color: AppColors.textPrimary(isDark),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // Body + Optional Floating Mobile Menu Overlay
            Expanded(
              child: Stack(
                children: [
                  IndexedStack(
                    index: currentIdx,
                    children: [
                      BerandaScreen(appState: state),
                      EdukasiScreen(appState: state),
                      MinigameHubScreen(appState: state),
                      ForumScreen(appState: state),
                      TestimoniScreen(appState: state),
                      TentangScreen(appState: state),
                    ],
                  ),
                  if (_drawerOpen)
                    GestureDetector(
                      onTap: () => setState(() => _drawerOpen = false),
                      child: Container(
                        color: Colors.black.withValues(alpha: 0.35),
                        alignment: Alignment.topCenter,
                        child: GestureDetector(
                          onTap: () {},
                          child: Container(
                            margin: const EdgeInsets.all(14),
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: AppColors.cardBg(isDark),
                              borderRadius: BorderRadius.circular(24),
                              border: Border.all(color: AppColors.border(isDark)),
                            ),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                ...List.generate(navItems.length, (idx) {
                                  final item = navItems[idx];
                                  final active = currentIdx == idx;
                                  return ListTile(
                                    dense: true,
                                    onTap: () {
                                      setState(() => _drawerOpen = false);
                                      state.setTab(idx);
                                    },
                                    leading: Icon(
                                      item.icon,
                                      size: 20,
                                      color: active ? AppColors.sky : AppColors.textSecondary(isDark),
                                    ),
                                    title: Text(
                                      item.label.toUpperCase(),
                                      style: AppTheme.heading(
                                        isDark,
                                        size: 12,
                                        color: active ? AppColors.sky : AppColors.textPrimary(isDark),
                                      ).copyWith(letterSpacing: 1.4),
                                    ),
                                    trailing: active
                                        ? Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                            decoration: BoxDecoration(color: AppColors.lime, borderRadius: BorderRadius.circular(99)),
                                            child: Text(state.tr('active'), style: AppTheme.heading(false, size: 9.5, color: AppColors.ink)),
                                          )
                                        : null,
                                  );
                                }),
                                const Divider(),
                                if (state.isAuthenticated)
                                  ListTile(
                                    dense: true,
                                    onTap: _openProfile,
                                    leading: UserAvatarWidget(
                                      avatarUrl: state.user!.avatarUrl,
                                      initial: state.user!.inisial,
                                      nameSeed: state.user!.namaLengkap,
                                      isOwner: state.user!.isOwner,
                                      size: 32,
                                    ),
                                    title: Text(state.tr('my_profile'), style: AppTheme.heading(isDark, size: 12.5)),
                                    trailing: const Icon(Icons.chevron_right_rounded),
                                  )
                                else
                                  SizedBox(
                                    width: double.infinity,
                                    child: ElevatedButton(
                                      onPressed: _openAuth,
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: AppColors.lime,
                                        foregroundColor: AppColors.ink,
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                                      ),
                                      child: Text(state.tr('join'), style: AppTheme.heading(false, size: 12, color: AppColors.ink)),
                                    ),
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
          ],
        ),
      ),

      // 6-Tab Bottom Navbar sesuai urutan website: Beranda, Edukasi, Minigame, Forum, Testimoni, Tentang
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: AppColors.cardBg(isDark),
          border: Border(top: BorderSide(color: AppColors.border(isDark))),
        ),
        child: SafeArea(
          top: false,
          child: SizedBox(
            height: 62,
            child: Row(
              children: List.generate(navItems.length, (idx) {
                final item = navItems[idx];
                final active = currentIdx == idx;
                return Expanded(
                  child: InkWell(
                    onTap: () => state.setTab(idx),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: active
                                ? (isDark ? AppColors.sky.withValues(alpha: 0.22) : AppColors.skySoft)
                                : Colors.transparent,
                            borderRadius: BorderRadius.circular(99),
                          ),
                          child: Icon(
                            item.icon,
                            size: 19,
                            color: active ? AppColors.sky : AppColors.textSecondary(isDark),
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          item.label,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: AppTheme.heading(
                            isDark,
                            size: 9.5,
                            weight: active ? FontWeight.w800 : FontWeight.w600,
                            color: active ? AppColors.sky : AppColors.textSecondary(isDark),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }),
            ),
          ),
        ),
      ),
    );
  }
}

