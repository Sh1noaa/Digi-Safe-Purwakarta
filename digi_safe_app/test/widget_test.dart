import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:digi_safe_app/core/app_state.dart';
import 'package:digi_safe_app/models/digisafe_models.dart';
import 'package:digi_safe_app/screens/auth_screens.dart';
import 'package:digi_safe_app/screens/main_shell.dart';
import 'package:digi_safe_app/screens/onboarding_wizard_screen.dart';
import 'package:digi_safe_app/screens/splash_onboarding_screen.dart';

void main() {
  testWidgets('OrbitalSplashScreen radial wave loads and transitions to AuthWelcomeScreen', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(412 * 2, 915 * 2);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.reset);

    SharedPreferences.setMockInitialValues({});
    final appState = AppState();

    await tester.pumpWidget(
      MaterialApp(home: OrbitalSplashScreen(appState: appState)),
    );
    await tester.pump(const Duration(milliseconds: 1450));

    expect(find.text('DIGI•SAFE'), findsOneWidget);
    expect(find.text('Mulai Sekarang'), findsOneWidget);

    await tester.tap(find.text('Mulai Sekarang'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 550));

    expect(find.text('DIGI•SAFE'), findsOneWidget);
    expect(find.text('Lanjutkan dengan Email'), findsOneWidget);
    expect(find.text('Buat Akun Baru'), findsOneWidget);
    expect(find.text('Google'), findsOneWidget);
    expect(find.text('Jelajahi'), findsOneWidget);

    await tester.pumpWidget(const SizedBox.shrink());
    appState.dispose();
  });

  testWidgets('Register sheet asks only Email + Password + Accept Password', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(412 * 2, 915 * 2);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.reset);

    SharedPreferences.setMockInitialValues({});
    final appState = AppState();
    await appState.init();

    await tester.pumpWidget(
      MaterialApp(home: AuthWelcomeScreen(appState: appState)),
    );
    await tester.pump();

    await tester.tap(find.text('Buat Akun Baru'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 450));

    expect(find.text('Gmail / Email'), findsOneWidget);
    expect(find.text('Accept / Konfirmasi Password'), findsOneWidget);

    await tester.pumpWidget(const SizedBox.shrink());
    appState.dispose();
  });

  testWidgets('OnboardingWizard celebrates YEAY after Nickname & Username then shows Bio & Role', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(412 * 2, 915 * 2);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.reset);

    SharedPreferences.setMockInitialValues({});
    final appState = AppState();
    await appState.init();

    await tester.pumpWidget(
      MaterialApp(home: OnboardingWizardScreen(appState: appState)),
    );
    await tester.pump();

    expect(find.text('Atur Identitas Warga Sibermu'), findsOneWidget);
    await tester.enterText(find.byType(TextField).at(0), 'Razief');
    await tester.enterText(find.byType(TextField).at(1), 'razief_pwk');
    await tester.pump();

    await tester.tap(find.text('Lanjut Sekarang'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 500));

    expect(find.text('Yeay! Keren Banget!'), findsOneWidget);
    expect(find.text('🎉 YEAY! LANJUT YUK!'), findsOneWidget);

    await tester.tap(find.text('Isi Bio & Role →'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 500));

    expect(find.text('Select Your Role'), findsOneWidget);
    expect(find.text('Cyber Guardian'), findsOneWidget);
    expect(find.text('Phishing Hunter'), findsOneWidget);

    await tester.pumpWidget(const SizedBox.shrink());
    appState.dispose();
  });

  testWidgets('MainShell renders 5-tab curved nav without hamburger, supports ID/EN and Dark Mode', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(412 * 2, 915 * 2);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.reset);

    SharedPreferences.setMockInitialValues({});
    final appState = AppState();
    await appState.init();

    await tester.pumpWidget(
      MaterialApp(home: MainShell(appState: appState)),
    );
    await tester.pump();

    expect(find.byIcon(Icons.menu_rounded), findsNothing);
    expect(find.text('Digi-Safe'), findsOneWidget);
    expect(find.text('Beranda'), findsOneWidget);
    expect(find.text('Edukasi'), findsOneWidget);
    expect(find.text('Minigame'), findsOneWidget);
    expect(find.text('Forum'), findsOneWidget);
    expect(find.text('Profil'), findsOneWidget);

    await tester.tap(find.text('ID'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 200));

    expect(find.text('EN'), findsOneWidget);
    expect(find.text('Home'), findsOneWidget);
    expect(find.text('Education'), findsOneWidget);
    expect(find.text('Profile'), findsOneWidget);

    await tester.tap(find.byIcon(Icons.dark_mode_rounded));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 200));
    expect(appState.isDark, isTrue);

    await tester.pumpWidget(const SizedBox.shrink());
    appState.dispose();
  });

  test('API models parse Laravel JSON payloads cleanly including specialtyRole and Bot Digika chat', () {
    final user = UserProfile.fromJson({
      'id': 1,
      'username': 'Razief Afkar Rusli',
      'nama_lengkap': 'Razief Afkar Rusli',
      'inisial': 'R',
      'role': 'owner',
      'specialty_role': 'Cyber Guardian',
      'role_label': 'Cyber Guardian',
      'is_owner': true,
      'is_admin': false,
      'has_admin_access': true,
      'badges': ['New Player', 'Master Siber'],
    });
    expect(user.isOwner, isTrue);
    expect(user.specialtyRole, 'Cyber Guardian');
    expect(user.badges.length, 2);
  });
}
