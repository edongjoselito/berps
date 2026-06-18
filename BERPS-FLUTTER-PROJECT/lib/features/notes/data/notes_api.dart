import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../../../core/network/api_exception.dart';
import '../domain/note.dart';

class NotesApi {
  NotesApi({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  Future<List<Note>> fetchNotes({
    required String baseUrl,
    required String token,
  }) async {
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/notes'),
        headers: _headers(token),
      ),
    );
    final data = _decode(response);
    final list = data['notes'];
    if (list is! List) return const <Note>[];
    return list
        .whereType<Map>()
        .map((e) => Note.fromJson(Map<String, dynamic>.from(e)))
        .toList(growable: false);
  }

  Future<void> createNote({
    required String baseUrl,
    required String token,
    required String title,
    required String description,
    required List<String> tags,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/notes/create'),
        headers: _headers(token),
        body: jsonEncode({
          'title': title,
          'description': description,
          'tags': tags,
        }),
      ),
    );
    _decode(response);
  }

  Future<void> updateNote({
    required String baseUrl,
    required String token,
    required int noteId,
    required String title,
    required String description,
    required List<String> tags,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/notes/$noteId/update'),
        headers: _headers(token),
        body: jsonEncode({
          'title': title,
          'description': description,
          'tags': tags,
        }),
      ),
    );
    _decode(response);
  }

  Future<void> deleteNote({
    required String baseUrl,
    required String token,
    required int noteId,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/notes/$noteId/delete'),
        headers: _headers(token),
      ),
    );
    _decode(response);
  }

  Future<bool> toggleFavorite({
    required String baseUrl,
    required String token,
    required int noteId,
    required bool isFavorite,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/notes/$noteId/favorite'),
        headers: _headers(token),
        body: jsonEncode({'is_favorite': isFavorite ? 1 : 0}),
      ),
    );
    final data = _decode(response);
    return data['is_favorite'] == true || data['is_favorite'] == 1;
  }

  // ── Internals ──────────────────────────────────────────────────────────────

  Uri _uri(String baseUrl, String path) {
    final normalized = baseUrl.replaceFirst(RegExp(r'/+$'), '');
    return Uri.parse('$normalized$path');
  }

  Map<String, String> _headers(String token) => {
        HttpHeaders.acceptHeader: 'application/json',
        HttpHeaders.contentTypeHeader: 'application/json',
        HttpHeaders.authorizationHeader: 'Bearer $token',
      };

  Future<http.Response> _request(
    Future<http.Response> Function() action,
  ) async {
    try {
      return await action();
    } on SocketException {
      throw const ApiException(
        'Cannot reach the server. Check the URL and your network.',
        isNetworkError: true,
      );
    } on HttpException {
      throw const ApiException('Unexpected HTTP error.', isNetworkError: true);
    } on FormatException {
      throw const ApiException('Invalid server response format.');
    }
  }

  Map<String, dynamic> _decode(http.Response response) {
    if (response.bodyBytes.isEmpty) {
      if (response.statusCode >= 200 && response.statusCode < 300) {
        return <String, dynamic>{'ok': true};
      }
      throw ApiException(
        'Empty response from server (HTTP ${response.statusCode}).',
      );
    }

    late final Object? decoded;
    try {
      decoded = jsonDecode(utf8.decode(response.bodyBytes, allowMalformed: true));
    } on FormatException {
      throw const ApiException('Invalid server response format.');
    }
    if (decoded is! Map) {
      throw const ApiException('Invalid server response.');
    }
    final data = Map<String, dynamic>.from(decoded);

    if (response.statusCode < 200 || response.statusCode >= 300) {
      throw ApiException((data['message'] ?? 'Request failed.').toString());
    }
    if (data['ok'] == false) {
      throw ApiException((data['message'] ?? 'Request failed.').toString());
    }
    return data;
  }
}
