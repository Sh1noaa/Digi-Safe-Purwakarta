class PrivacySettings {
  final bool isPrivateProfile;
  final bool hideLocation;
  final bool hideEmail;
  final bool hideMinigameStats;
  final bool hideBadges;
  final bool hideActivity;

  const PrivacySettings({
    this.isPrivateProfile = false,
    this.hideLocation = false,
    this.hideEmail = false,
    this.hideMinigameStats = false,
    this.hideBadges = false,
    this.hideActivity = false,
  });

  factory PrivacySettings.fromJson(Map<String, dynamic>? json) {
    if (json == null) return const PrivacySettings();
    return PrivacySettings(
      isPrivateProfile: json['is_private_profile'] == true,
      hideLocation: json['hide_location'] == true,
      hideEmail: json['hide_email'] == true,
      hideMinigameStats: json['hide_minigame_stats'] == true,
      hideBadges: json['hide_badges'] == true,
      hideActivity: json['hide_activity'] == true,
    );
  }

  Map<String, dynamic> toJson() => {
        'is_private_profile': isPrivateProfile,
        'hide_location': hideLocation,
        'hide_email': hideEmail,
        'hide_minigame_stats': hideMinigameStats,
        'hide_badges': hideBadges,
        'hide_activity': hideActivity,
      };
}

class UserStats {
  final int? totalSkor;
  final int? topSkor;
  final int? totalMain;
  final int? totalUlasan;
  final int? totalDiskusi;
  final int? totalThread;
  final int? totalJawaban;
  final bool minigameHidden;
  final bool activityHidden;

  const UserStats({
    this.totalSkor,
    this.topSkor,
    this.totalMain,
    this.totalUlasan,
    this.totalDiskusi,
    this.totalThread,
    this.totalJawaban,
    this.minigameHidden = false,
    this.activityHidden = false,
  });

  factory UserStats.fromJson(Map<String, dynamic>? json) {
    if (json == null) return const UserStats();
    return UserStats(
      totalSkor: _toNullableInt(json['total_skor']),
      topSkor: _toNullableInt(json['top_skor']),
      totalMain: _toNullableInt(json['total_main']),
      totalUlasan: _toNullableInt(json['total_ulasan']),
      totalDiskusi: _toNullableInt(json['total_diskusi']),
      totalThread: _toNullableInt(json['total_thread']),
      totalJawaban: _toNullableInt(json['total_jawaban']),
      minigameHidden: json['minigame_hidden'] == true,
      activityHidden: json['activity_hidden'] == true,
    );
  }
}

class UserProfile {
  final int id;
  final String username;
  final String namaLengkap;
  final String inisial;
  final String role;
  final String? specialtyRole;
  final String roleLabel;
  final bool isOwner;
  final bool isAdmin;
  final bool hasAdminAccess;
  final String? avatarUrl;
  final String? bannerUrl;
  final String? email;
  final String? bio;
  final bool locationHidden;
  final bool badgesHidden;
  final bool isPrivateProfile;
  final bool isPrivateForVisitor;
  final bool isOwnProfile;
  final String locale;
  final String theme;
  final List<String> badges;
  final PrivacySettings privacySettings;
  final UserStats stats;

  const UserProfile({
    required this.id,
    required this.username,
    required this.namaLengkap,
    required this.inisial,
    required this.role,
    this.specialtyRole,
    required this.roleLabel,
    required this.isOwner,
    required this.isAdmin,
    required this.hasAdminAccess,
    this.avatarUrl,
    this.bannerUrl,
    this.email,
    this.bio,
    this.locationHidden = false,
    this.badgesHidden = false,
    this.isPrivateProfile = false,
    this.isPrivateForVisitor = false,
    this.isOwnProfile = false,
    this.locale = 'id',
    this.theme = 'light',
    this.badges = const [],
    this.privacySettings = const PrivacySettings(),
    this.stats = const UserStats(),
  });

  factory UserProfile.fromJson(Map<String, dynamic> json) {
    final badgesRaw = json['badges'];
    final badges = badgesRaw is List ? badgesRaw.map((e) => e.toString()).toList() : <String>[];
    final spec = json['specialty_role']?.toString().trim();
    return UserProfile(
      id: _toInt(json['id']),
      username: (json['username'] ?? '').toString(),
      namaLengkap: (json['nama_lengkap'] ?? json['username'] ?? 'User').toString(),
      inisial: (json['inisial'] ?? 'U').toString(),
      role: (json['role'] ?? 'user').toString(),
      specialtyRole: (spec == null || spec.isEmpty) ? null : spec,
      roleLabel: (json['role_label'] ?? '').toString(),
      isOwner: json['is_owner'] == true,
      isAdmin: json['is_admin'] == true,
      hasAdminAccess: json['has_admin_access'] == true,
      avatarUrl: json['avatar_url']?.toString(),
      bannerUrl: json['banner_url']?.toString(),
      email: json['email']?.toString(),
      bio: json['bio']?.toString(),
      locationHidden: json['location_hidden'] == true,
      badgesHidden: json['badges_hidden'] == true,
      isPrivateProfile: json['is_private_profile'] == true,
      isPrivateForVisitor: json['is_private_for_visitor'] == true,
      isOwnProfile: json['is_own_profile'] == true,
      locale: (json['locale'] ?? 'id').toString(),
      theme: (json['theme'] ?? 'light').toString(),
      badges: badges,
      privacySettings: PrivacySettings.fromJson(json['privacy_settings'] as Map<String, dynamic>?),
      stats: UserStats.fromJson(json['stats'] as Map<String, dynamic>?),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'username': username,
        'nama_lengkap': namaLengkap,
        'inisial': inisial,
        'role': role,
        'specialty_role': specialtyRole,
        'role_label': roleLabel,
        'is_owner': isOwner,
        'is_admin': isAdmin,
        'has_admin_access': hasAdminAccess,
        'avatar_url': avatarUrl,
        'banner_url': bannerUrl,
        'email': email,
        'bio': bio,
        'location_hidden': locationHidden,
        'badges_hidden': badgesHidden,
        'is_private_profile': isPrivateProfile,
        'is_private_for_visitor': isPrivateForVisitor,
        'is_own_profile': isOwnProfile,
        'locale': locale,
        'theme': theme,
        'badges': badges,
        'privacy_settings': privacySettings.toJson(),
      };
}

class EdukasiModule {
  final int id;
  final String title;
  final String description;
  final String konten;
  final String type;
  final String url;
  final String? gambarUrl;
  final String stat;
  final String tag;
  final String variant;
  final String icon;

  const EdukasiModule({
    required this.id,
    required this.title,
    required this.description,
    required this.konten,
    required this.type,
    required this.url,
    this.gambarUrl,
    required this.stat,
    required this.tag,
    required this.variant,
    required this.icon,
  });

  bool get isTextModule => type == 'teks';

  factory EdukasiModule.fromJson(Map<String, dynamic> json) => EdukasiModule(
        id: _toInt(json['id']),
        title: (json['title'] ?? '').toString(),
        description: (json['description'] ?? '').toString(),
        konten: (json['konten'] ?? '').toString(),
        type: (json['type'] ?? 'link').toString(),
        url: (json['url'] ?? '').toString(),
        gambarUrl: json['gambar_url']?.toString(),
        stat: (json['stat'] ?? '8').toString(),
        tag: (json['tag'] ?? 'Literasi Siber').toString(),
        variant: (json['variant'] ?? 'solid').toString(),
        icon: (json['icon'] ?? 'journal').toString(),
      );
}

class EdukasiRequestItem {
  final int id;
  final int? userId;
  final String? userName;
  final String? username;
  final String? userAvatar;
  final String tema;
  final String kategori;
  final String preferredType;
  final String deskripsi;
  final String status; // pending | approved | rejected
  final String? adminNote;
  final bool userNotifiedRead;
  final String createdAt;

  const EdukasiRequestItem({
    required this.id,
    this.userId,
    this.userName,
    this.username,
    this.userAvatar,
    required this.tema,
    required this.kategori,
    required this.preferredType,
    required this.deskripsi,
    required this.status,
    this.adminNote,
    this.userNotifiedRead = true,
    required this.createdAt,
  });

  factory EdukasiRequestItem.fromJson(Map<String, dynamic> json) => EdukasiRequestItem(
        id: _toInt(json['id']),
        userId: _toNullableInt(json['user_id']),
        userName: json['user_name']?.toString(),
        username: json['username']?.toString(),
        userAvatar: json['user_avatar']?.toString(),
        tema: (json['tema'] ?? '').toString(),
        kategori: (json['kategori'] ?? 'Literasi Siber').toString(),
        preferredType: (json['preferred_type'] ?? 'teks').toString(),
        deskripsi: (json['deskripsi'] ?? '').toString(),
        status: (json['status'] ?? 'pending').toString(),
        adminNote: json['admin_note']?.toString(),
        userNotifiedRead: json['user_notified_read'] == true,
        createdAt: (json['created_at'] ?? '').toString(),
      );
}

class MinigameLevel {
  final int id;
  final String slug;
  final String namaLevel;
  final String badgeLabel;
  final String deskripsi;
  final int waktuDetik;
  final int poinBenar;
  final int penaltiSalah;
  final int hpAwal;
  final int urutan;
  final String warna;

  const MinigameLevel({
    required this.id,
    required this.slug,
    required this.namaLevel,
    required this.badgeLabel,
    required this.deskripsi,
    required this.waktuDetik,
    required this.poinBenar,
    required this.penaltiSalah,
    required this.hpAwal,
    required this.urutan,
    required this.warna,
  });

  factory MinigameLevel.fromJson(Map<String, dynamic> json) => MinigameLevel(
        id: _toInt(json['id']),
        slug: (json['slug'] ?? 'easy').toString(),
        namaLevel: (json['nama_level'] ?? 'Level').toString(),
        badgeLabel: (json['badge_label'] ?? 'LEVEL').toString(),
        deskripsi: (json['deskripsi'] ?? '').toString(),
        waktuDetik: _toInt(json['waktu_detik'], 15),
        poinBenar: _toInt(json['poin_benar'], 10),
        penaltiSalah: _toInt(json['penalti_salah'], 5),
        hpAwal: _toInt(json['hp_awal'], 3),
        urutan: _toInt(json['urutan'], 1),
        warna: (json['warna'] ?? 'emerald').toString(),
      );
}

class DbMinigameQuestion {
  final int id;
  final String pertanyaan;
  final Map<String, String> opsi;
  final String kunci;
  final String kunciTeks;
  final String level;
  final String gameLevel;

  const DbMinigameQuestion({
    required this.id,
    required this.pertanyaan,
    required this.opsi,
    required this.kunci,
    required this.kunciTeks,
    required this.level,
    this.gameLevel = 'phishing',
  });

  factory DbMinigameQuestion.fromJson(Map<String, dynamic> json) {
    final rawOpsi = json['opsi'];
    final opsiMap = <String, String>{};
    if (rawOpsi is Map) {
      rawOpsi.forEach((k, v) {
        opsiMap[k.toString()] = v.toString();
      });
    }
    return DbMinigameQuestion(
      id: _toInt(json['id']),
      pertanyaan: (json['pertanyaan'] ?? '').toString(),
      opsi: opsiMap,
      kunci: (json['kunci'] ?? 'A').toString(),
      kunciTeks: (json['kunci_teks'] ?? '').toString(),
      level: (json['level'] ?? json['tingkat'] ?? 'easy').toString(),
      gameLevel: (json['game_level'] ?? 'phishing').toString(),
    );
  }
}

class ScoreHistoryItem {
  final int id;
  final String gameType;
  final String gameName;
  final int skor;
  final int skorTertinggi;
  final String playedAt;

  const ScoreHistoryItem({
    required this.id,
    required this.gameType,
    required this.gameName,
    required this.skor,
    required this.skorTertinggi,
    required this.playedAt,
  });

  factory ScoreHistoryItem.fromJson(Map<String, dynamic> json) => ScoreHistoryItem(
        id: _toInt(json['id']),
        gameType: (json['game_type'] ?? 'phishing').toString(),
        gameName: (json['game_name'] ?? 'Minigame').toString(),
        skor: _toInt(json['skor']),
        skorTertinggi: _toInt(json['skor_tertinggi']),
        playedAt: (json['played_at'] ?? '-').toString(),
      );
}

class LeaderboardEntry {
  final int id;
  final String username;
  final String namaLengkap;
  final String inisial;
  final String? avatarUrl;
  final int topScore;
  final int totalScore;
  final int matchesPlayed;

  const LeaderboardEntry({
    required this.id,
    required this.username,
    required this.namaLengkap,
    required this.inisial,
    this.avatarUrl,
    required this.topScore,
    required this.totalScore,
    required this.matchesPlayed,
  });

  factory LeaderboardEntry.fromJson(Map<String, dynamic> json) => LeaderboardEntry(
        id: _toInt(json['id']),
        username: (json['username'] ?? '').toString(),
        namaLengkap: (json['nama_lengkap'] ?? '').toString(),
        inisial: (json['inisial'] ?? 'U').toString(),
        avatarUrl: json['avatar_url']?.toString(),
        topScore: _toInt(json['top_score']),
        totalScore: _toInt(json['total_score']),
        matchesPlayed: _toInt(json['matches_played']),
      );
}

class LeaderboardRowModel {
  final int id;
  final int rank;
  final String nama;
  final String username;
  final String inisial;
  final String? avatarUrl;
  final String? bannerUrl;
  final String? specialtyRole;
  final String bio;
  final String metricMain;
  final String metricMobile;
  final String subtitle;
  final int stars;
  final int accuracy;
  final List<String> badges;

  const LeaderboardRowModel({
    required this.id,
    required this.rank,
    required this.nama,
    required this.username,
    required this.inisial,
    this.avatarUrl,
    this.bannerUrl,
    this.specialtyRole,
    required this.bio,
    required this.metricMain,
    required this.metricMobile,
    required this.subtitle,
    required this.stars,
    required this.accuracy,
    this.badges = const [],
  });

  factory LeaderboardRowModel.fromJson(Map<String, dynamic> json) => LeaderboardRowModel(
        id: _toInt(json['id']),
        rank: _toInt(json['rank'], 1),
        nama: (json['nama'] ?? 'User').toString(),
        username: (json['username'] ?? '').toString(),
        inisial: (json['inisial'] ?? 'U').toString(),
        avatarUrl: json['avatar_url']?.toString(),
        bannerUrl: json['banner_url']?.toString(),
        specialtyRole: json['specialty_role']?.toString(),
        bio: (json['bio'] ?? '').toString(),
        metricMain: (json['metric_main'] ?? '').toString(),
        metricMobile: (json['metric_mobile'] ?? '').toString(),
        subtitle: (json['subtitle'] ?? '').toString(),
        stars: _toInt(json['stars'], 5),
        accuracy: _toInt(json['accuracy']),
        badges: (json['badges'] as List? ?? []).map((e) => e.toString()).toList(),
      );
}

class LeaderboardCategoryModel {
  final String key;
  final String label;
  final String shortLabel;
  final List<LeaderboardRowModel> rows;

  const LeaderboardCategoryModel({
    required this.key,
    required this.label,
    required this.shortLabel,
    required this.rows,
  });

  factory LeaderboardCategoryModel.fromJson(Map<String, dynamic> json) => LeaderboardCategoryModel(
        key: (json['key'] ?? 'minigame_benar').toString(),
        label: (json['label'] ?? '').toString(),
        shortLabel: (json['short'] ?? '').toString(),
        rows: (json['rows'] as List? ?? [])
            .map((e) => LeaderboardRowModel.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}

class ForumReply {
  final int id;
  final int? userId;
  final String jawaban;
  final bool isOwner;
  final bool isAdmin;
  final String waktu;
  final String userName;
  final String userInitial;
  final String? userAvatar;
  final int upCount;
  final int downCount;
  final int netScore;
  final String? userVote;
  final bool isMostHelpful;

  const ForumReply({
    required this.id,
    this.userId,
    required this.jawaban,
    required this.isOwner,
    required this.isAdmin,
    required this.waktu,
    required this.userName,
    required this.userInitial,
    this.userAvatar,
    required this.upCount,
    required this.downCount,
    required this.netScore,
    this.userVote,
    required this.isMostHelpful,
  });

  factory ForumReply.fromJson(Map<String, dynamic> json) => ForumReply(
        id: _toInt(json['id']),
        userId: _toNullableInt(json['user_id']),
        jawaban: (json['jawaban'] ?? '').toString(),
        isOwner: json['is_owner'] == true,
        isAdmin: json['is_admin'] == true,
        waktu: (json['waktu'] ?? '').toString(),
        userName: (json['user_name'] ?? 'Anggota').toString(),
        userInitial: (json['user_initial'] ?? 'A').toString(),
        userAvatar: json['user_avatar']?.toString(),
        upCount: _toInt(json['up_count']),
        downCount: _toInt(json['down_count']),
        netScore: _toInt(json['net_score']),
        userVote: json['user_vote']?.toString(),
        isMostHelpful: json['is_most_helpful'] == true,
      );
}

class ForumThread {
  final int id;
  final int? userId;
  final String judul;
  final String pertanyaan;
  final String kategori;
  final String status;
  final bool isStarred;
  final int views;
  final String waktu;
  final String authorName;
  final String authorInitial;
  final String? authorAvatar;
  final bool authorIsOwner;
  final bool authorIsAdmin;
  final int jawabanCount;
  final int totalUp;
  final int totalDown;
  final bool hasMostHelpful;
  final List<ForumReply> jawaban;

  const ForumThread({
    required this.id,
    this.userId,
    required this.judul,
    required this.pertanyaan,
    required this.kategori,
    required this.status,
    required this.isStarred,
    required this.views,
    required this.waktu,
    required this.authorName,
    required this.authorInitial,
    this.authorAvatar,
    required this.authorIsOwner,
    required this.authorIsAdmin,
    required this.jawabanCount,
    required this.totalUp,
    required this.totalDown,
    required this.hasMostHelpful,
    required this.jawaban,
  });

  factory ForumThread.fromJson(Map<String, dynamic> json) {
    final repliesRaw = json['jawaban'];
    final replies = repliesRaw is List
        ? repliesRaw.map((e) => ForumReply.fromJson(e as Map<String, dynamic>)).toList()
        : <ForumReply>[];

    return ForumThread(
      id: _toInt(json['id']),
      userId: _toNullableInt(json['user_id']),
      judul: (json['judul'] ?? '').toString(),
      pertanyaan: (json['pertanyaan'] ?? '').toString(),
      kategori: (json['kategori'] ?? 'umum').toString(),
      status: (json['status'] ?? 'terbuka').toString(),
      isStarred: json['is_starred'] == true,
      views: _toInt(json['views'], 1),
      waktu: (json['waktu'] ?? '').toString(),
      authorName: (json['author_name'] ?? 'Warga').toString(),
      authorInitial: (json['author_initial'] ?? 'W').toString(),
      authorAvatar: json['author_avatar']?.toString(),
      authorIsOwner: json['author_is_owner'] == true,
      authorIsAdmin: json['author_is_admin'] == true,
      jawabanCount: _toInt(json['jawaban_count'], replies.length),
      totalUp: _toInt(json['total_up']),
      totalDown: _toInt(json['total_down']),
      hasMostHelpful: json['has_most_helpful'] == true,
      jawaban: replies,
    );
  }
}

class TestimoniItem {
  final int id;
  final int? userId;
  final String nama;
  final String inisial;
  final String? avatar;
  final bool isOwner;
  final bool isAnonymous;
  final int rating;
  final String cerita;
  final String waktu;

  const TestimoniItem({
    required this.id,
    this.userId,
    required this.nama,
    required this.inisial,
    this.avatar,
    required this.isOwner,
    required this.isAnonymous,
    required this.rating,
    required this.cerita,
    required this.waktu,
  });

  factory TestimoniItem.fromJson(Map<String, dynamic> json) => TestimoniItem(
        id: _toInt(json['id']),
        userId: _toNullableInt(json['user_id']),
        nama: (json['nama'] ?? 'Warga').toString(),
        inisial: (json['inisial'] ?? 'W').toString(),
        avatar: json['avatar']?.toString(),
        isOwner: json['is_owner'] == true,
        isAnonymous: json['is_anonymous'] == true,
        rating: _toInt(json['rating'], 5),
        cerita: (json['cerita'] ?? '').toString(),
        waktu: (json['waktu'] ?? '').toString(),
      );
}

class ChatUserCompact {
  final int id;
  final String nama;
  final String username;
  final String inisial;
  final String? avatarUrl;
  final String? specialtyRole;
  final bool isBot;
  final bool isOwner;
  final bool isAdmin;

  const ChatUserCompact({
    required this.id,
    required this.nama,
    required this.username,
    required this.inisial,
    this.avatarUrl,
    this.specialtyRole,
    this.isBot = false,
    this.isOwner = false,
    this.isAdmin = false,
  });

  factory ChatUserCompact.fromJson(Map<String, dynamic> json) {
    final uname = (json['username'] ?? '').toString();
    return ChatUserCompact(
      id: _toInt(json['id']),
      nama: (json['nama'] ?? json['nama_lengkap'] ?? uname).toString(),
      username: uname,
      inisial: (json['inisial'] ?? 'U').toString(),
      avatarUrl: json['avatar_url']?.toString(),
      specialtyRole: json['specialty_role']?.toString(),
      isBot: json['is_bot'] == true || uname == 'bot_digika',
      isOwner: json['is_owner'] == true,
      isAdmin: json['is_admin'] == true,
    );
  }
}

class ChatThreadItem {
  final ChatUserCompact user;
  final String relation;
  final String? lastMessage;
  final bool lastSenderMe;
  final String lastTime;
  final int unreadCount;

  const ChatThreadItem({
    required this.user,
    required this.relation,
    this.lastMessage,
    this.lastSenderMe = false,
    required this.lastTime,
    this.unreadCount = 0,
  });

  factory ChatThreadItem.fromJson(Map<String, dynamic> json) => ChatThreadItem(
        user: ChatUserCompact.fromJson(json['user'] as Map<String, dynamic>),
        relation: (json['relation'] ?? 'none').toString(),
        lastMessage: json['last_message']?.toString(),
        lastSenderMe: json['last_sender_me'] == true,
        lastTime: (json['last_time'] ?? '').toString(),
        unreadCount: _toInt(json['unread_count']),
      );
}

class ChatRequestItem {
  final int friendshipId;
  final ChatUserCompact user;
  final String? messagePreview;
  final String time;

  const ChatRequestItem({
    required this.friendshipId,
    required this.user,
    this.messagePreview,
    required this.time,
  });

  factory ChatRequestItem.fromJson(Map<String, dynamic> json) => ChatRequestItem(
        friendshipId: _toInt(json['friendship_id']),
        user: ChatUserCompact.fromJson(json['user'] as Map<String, dynamic>),
        messagePreview: json['message_preview']?.toString(),
        time: (json['time'] ?? '').toString(),
      );
}

class ChatMessageItem {
  final int id;
  final int senderId;
  final bool isMine;
  final String message;
  final String time;

  const ChatMessageItem({
    required this.id,
    required this.senderId,
    required this.isMine,
    required this.message,
    required this.time,
  });

  factory ChatMessageItem.fromJson(Map<String, dynamic> json) => ChatMessageItem(
        id: _toInt(json['id']),
        senderId: _toInt(json['sender_id']),
        isMine: json['is_mine'] == true,
        message: (json['message'] ?? '').toString(),
        time: (json['time'] ?? '').toString(),
      );
}

int _toInt(dynamic v, [int fallback = 0]) {
  if (v is int) return v;
  if (v is double) return v.round();
  if (v is String) return int.tryParse(v) ?? fallback;
  return fallback;
}

int? _toNullableInt(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  if (v is double) return v.round();
  if (v is String) return int.tryParse(v);
  return null;
}
