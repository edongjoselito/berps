import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../../../core/network/api_exception.dart';
import '../domain/reminder.dart';

class RemindersData {
  const RemindersData({required this.reminders, required this.dueTodayCount});
  final List<Reminder> reminders;
  final int dueTodayCount;
}

class RemindersApi {
  RemindersApi({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  Future<RemindersData> fetchReminders({
    required String baseUrl,
    required String token,
  }) async {
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/reminders'),
        headers: _headers(token),
      ),
    );
    final data = _decode(response);
    final list = data['reminders'];
    final reminders = list is List
        ? list
            .whereType<Map>()
            .map((e) => Reminder.fromJson(Map<String, dynamic>.from(e)))
            .toList(growable: false)
        : const <Reminder>[];
    return RemindersData(
      reminders: reminders,
      dueTodayCount:
          int.tryParse((data['due_today_count'] ?? '0').toString()) ?? 0,
    );
  }

  Future<void> createReminder({
    required String baseUrl,
    required String token,
    required String title,
    required String description,
    required String remindAt,
    required String recurrence,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/reminders/create'),
        headers: _headers(token),
        body: jsonEncode({
          'title': title,
          'description': description,
          'remind_at': remindAt,
          'recurrence': recurrence,
        }),
      ),
    );
    _decode(response);
  }

  Future<void> updateReminder({
    required String baseUrl,
    required String token,
    required int reminderId,
    required String title,
    required String description,
    required String remindAt,
    required String recurrence,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/reminders/$reminderId/update'),
        headers: _headers(token),
        body: jsonEncode({
          'title': title,
          'description': description,
          'remind_at': remindAt,
          'recurrence': recurrence,
        }),
      ),
    );
    _decode(response);
  }

  Future<void> deleteReminder({
    required String baseUrl,
    required String token,
    required int reminderId,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/reminders/$reminderId/delete'),
        headers: _headers(token),
      ),
    );
    _decode(response);
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
