import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:digi_safe_app/core/app_state.dart';
import 'package:digi_safe_app/main.dart';
import 'package:digi_safe_app/models/digisafe_models.dart';

void main() {
  testWidgets('Digi-Safe shell renders navbar order, language toggle & dark mode', (WidgetTester tester) async {
    SharedPreferences.setMockInitialValues({});
    final appState = AppState();
    await appState.init();

    await tester.pumpWidget(DigiSafeApp(appState: appState));
    await tester.pump();

    expect(find.text('Digi-Safe'), findsOneWidget);
    expect(find.text('Beranda'), findsOneWidget);
    expect(find.text('Edukasi'), findsOneWidget);
    expect(find.text('Minigame'), findsOneWidget);
    expect(find.text('Forum'), findsOneWidget);
    expect(find.text('Testimoni'), findsOneWidget);
    expect(find.text('Tentang'), findsOneWidget);

    // Toggle language from ID to EN
    await tester.tap(find.text('ID'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 200));

    expect(find.text('EN'), findsOneWidget);
    expect(find.text('Home'), findsOneWidget);
    expect(find.text('Education'), findsOneWidget);
    expect(find.text('Testimonials'), findsOneWidget);
    expect(find.text('About'), findsOneWidget);

    // Toggle dark mode
    await tester.tap(find.byIcon(Icons.dark_mode_rounded));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 200));
    expect(appState.isDark, isTrue);
  });

  test('API models parse Laravel JSON payloads cleanly', () {
    final modul = EdukasiModule.fromJson({
      'id': 7,
      'title': 'Waspada Phishing APK',
      'description': 'Materi ringkas...',
      'konten': 'Isi lengkap modul.',
      'type': 'teks',
      'url': '',
      'stat': '32',
      'tag': 'Phishing',
      'variant': 'solid',
      'icon': 'shield',
    });
    expect(modul.isTextModule, isTrue);
    expect(modul.title, 'Waspada Phishing APK');

    final user = UserProfile.fromJson({
      'id': 1,
      'username': 'Razief Afkar Rusli',
      'nama_lengkap': 'Razief Afkar Rusli',
      'inisial': 'R',
      'role': 'owner',
      'role_label': 'Platform Owner & Lead Developer',
      'is_owner': true,
      'is_admin': false,
      'has_admin_access': true,
      'badges': ['New Player', 'Master Siber'],
    });
    expect(user.isOwner, isTrue);
    expect(user.badges.length, 2);

    final thread = ForumThread.fromJson({
      'id': 10,
      'judul': 'Waspada Link Palsu',
      'pertanyaan': 'Bagaimana cek domain?',
      'kategori': 'phishing',
      'is_starred': true,
      'has_most_helpful': true,
      'jawaban': [
        {
          'id': 20,
          'jawaban': 'Periksa ekstensi .go.id resmi.',
          'is_owner': true,
          'is_admin': false,
          'user_name': 'Razief Afkar Rusli',
          'user_initial': 'R',
          'up_count': 5,
          'down_count': 0,
          'net_score': 5,
          'user_vote': 'up',
          'is_most_helpful': true,
        },
      ],
    });
    expect(thread.isStarred, isTrue);
    expect(thread.jawaban.first.isMostHelpful, isTrue);
  });
}
