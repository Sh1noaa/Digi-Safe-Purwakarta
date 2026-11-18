import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

class ApiException implements Exception {
  final String message;
  final int statusCode;

  ApiException(this.message, [this.statusCode = 500]);

  @override
  String toString() => message;
}

class ApiClient {
  static const String baseHost = 'https://digisafepurwakarta.web.id';
  static const String apiBase = '$baseHost/api';

  String? token;
  String locale;

  ApiClient({this.token, this.locale = 'id'});

  Map<String, String> _headers({bool jsonBody = true}) {
    final headers = <String, String>{
      'Accept': 'application/json',
      'X-App-Locale': locale,
    };
    if (jsonBody) {
      headers['Content-Type'] = 'application/json';
    }
    if (token != null && token!.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  Future<Map<String, dynamic>> get(String path) async {
    final uri = Uri.parse('$apiBase$path');
    final resp = await http.get(uri, headers: _headers()).timeout(const Duration(seconds: 18));
    return _decode(resp);
  }

  /// Mengambil endpoint opsional tanpa melempar exception jika server hosting belum diperbarui (404).
  Future<Map<String, dynamic>?> getOptional(String path) async {
    try {
      return await get(path);
    } on ApiException catch (e) {
      if (e.statusCode == 404 || e.statusCode == 405) return null;
      rethrow;
    }
  }

  Future<Map<String, dynamic>> post(String path, [Map<String, dynamic>? body]) async {
    final uri = Uri.parse('$apiBase$path');
    final resp = await http
        .post(uri, headers: _headers(), body: jsonEncode(body ?? {}))
        .timeout(const Duration(seconds: 20));
    return _decode(resp);
  }

  Future<Map<String, dynamic>> put(String path, [Map<String, dynamic>? body]) async {
    final uri = Uri.parse('$apiBase$path');
    final resp = await http
        .put(uri, headers: _headers(), body: jsonEncode(body ?? {}))
        .timeout(const Duration(seconds: 20));
    return _decode(resp);
  }

  Future<Map<String, dynamic>> patch(String path, [Map<String, dynamic>? body]) async {
    final uri = Uri.parse('$apiBase$path');
    final resp = await http
        .patch(uri, headers: _headers(), body: jsonEncode(body ?? {}))
        .timeout(const Duration(seconds: 20));
    return _decode(resp);
  }

  Future<Map<String, dynamic>> delete(String path) async {
    final uri = Uri.parse('$apiBase$path');
    final resp = await http.delete(uri, headers: _headers()).timeout(const Duration(seconds: 20));
    return _decode(resp);
  }

  Future<Map<String, dynamic>> postMultipart(
    String path,
    Map<String, String> fields, {
    File? avatarFile,
    File? bannerFile,
  }) async {
    final uri = Uri.parse('$apiBase$path');
    final req = http.MultipartRequest('POST', uri);
    req.headers.addAll(_headers(jsonBody: false));
    req.fields.addAll(fields);

    if (avatarFile != null) {
      req.files.add(await http.MultipartFile.fromPath('avatar', avatarFile.path));
    }
    if (bannerFile != null) {
      req.files.add(await http.MultipartFile.fromPath('banner', bannerFile.path));
    }

    final streamed = await req.send().timeout(const Duration(seconds: 30));
    final resp = await http.Response.fromStream(streamed);
    return _decode(resp);
  }

  Map<String, dynamic> _decode(http.Response resp) {
    final bodyText = utf8.decode(resp.bodyBytes);
    dynamic parsed;
    try {
      parsed = jsonDecode(bodyText);
    } catch (_) {
      throw ApiException(
        'Server mengembalikan respons non-JSON (${resp.statusCode}). Pastikan koneksi internet stabil atau API backend terbaru aktif.',
        resp.statusCode,
      );
    }

    if (parsed is! Map<String, dynamic>) {
      throw ApiException('Format respons API tidak sesuai.', resp.statusCode);
    }

    if (resp.statusCode >= 400) {
      String errMsg = (parsed['message'] ?? 'Permintaan gagal (${resp.statusCode})').toString();
      if (parsed['errors'] is Map && (parsed['errors'] as Map).isNotEmpty) {
        final firstErr = (parsed['errors'] as Map).values.first;
        if (firstErr is List && firstErr.isNotEmpty) {
          errMsg = firstErr.first.toString();
        }
      }
      throw ApiException(errMsg, resp.statusCode);
    }

    return parsed;
  }
}
