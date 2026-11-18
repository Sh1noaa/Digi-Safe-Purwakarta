import 'package:flutter/material.dart';

import 'core/app_state.dart';
import 'core/theme.dart';
import 'screens/main_shell.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final appState = AppState();
  await appState.init();
  runApp(DigiSafeApp(appState: appState));
}

class DigiSafeApp extends StatelessWidget {
  final AppState appState;

  const DigiSafeApp({super.key, required this.appState});

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: appState,
      builder: (context, _) {
        return MaterialApp(
          title: 'Digi-Safe Purwakarta',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.build(false),
          darkTheme: AppTheme.build(true),
          themeMode: appState.isDark ? ThemeMode.dark : ThemeMode.light,
          home: MainShell(appState: appState),
        );
      },
    );
  }
}
