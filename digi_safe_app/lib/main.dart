import 'package:flutter/material.dart';

import 'core/app_state.dart';
import 'core/theme.dart';
import 'screens/splash_onboarding_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  final appState = AppState();
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
          home: OrbitalSplashScreen(appState: appState),
        );
      },
    );
  }
}
