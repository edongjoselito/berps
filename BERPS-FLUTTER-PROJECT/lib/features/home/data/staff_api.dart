import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../../../core/network/api_exception.dart';
import '../../attendance/domain/staff_attendance.dart';
import '../../notifications/domain/staff_notification.dart';
import '../../support/domain/support_issue.dart';
import '../../calendar/domain/calendar_event.dart';
import '../../goals/domain/annual_goal.dart';
import '../../support_dashboard/domain/support_dashboard.dart';
import '../domain/my_dtr.dart';
import '../domain/staff_dashboard.dart';
import '../domain/staff_profile.dart';
import '../domain/staff_ranking.dart';
import '../../tasks/domain/staff_tasks.dart';

class StaffApi {
  StaffApi({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  Future<StaffDashboard> fetchDashboard({
    required String baseUrl,
    required String token,
  }) async {
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/dashboard'),
        headers: _authorizedHeaders(token),
      ),
    );
    return StaffDashboard.fromJson(_decode(response));
  }

  Future<StaffAttendanceData> fetchAttendance({
    required String baseUrl,
    required String token,
    String? from,
    String? to,
  }) async {
    final query = <String, String>{};
    if ((from ?? '').isNotEmpty) query['from'] = from!;
    if ((to ?? '').isNotEmpty) query['to'] = to!;

    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/attendance', query),
        headers: _authorizedHeaders(token),
      ),
    );
    return StaffAttendanceData.fromJson(_decode(response));
  }

  Future<String> timeIn({
    required String baseUrl,
    required String token,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/attendance/time-in'),
        headers: _authorizedHeaders(token),
      ),
    );
    return (_decode(response)['message'] ?? 'Time-in recorded.').toString();
  }

  Future<String> timeOut({
    required String baseUrl,
    required String token,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/attendance/time-out'),
        headers: _authorizedHeaders(token),
      ),
    );
    return (_decode(response)['message'] ?? 'Time-out recorded.').toString();
  }

  Future<StaffTasksData> fetchTasks({
    required String baseUrl,
    required String token,
    String status = 'open',
    String scope = '',
  }) async {
    final query = <String, String>{'status': status};
    if (scope.isNotEmpty) query['scope'] = scope;

    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/tasks', query),
        headers: _authorizedHeaders(token),
      ),
    );
    return StaffTasksData.fromJson(_decode(response));
  }

  Future<StaffTaskDetail> fetchTaskDetail({
    required String baseUrl,
    required String token,
    required int taskId,
  }) async {
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/tasks/$taskId'),
        headers: _authorizedHeaders(token),
      ),
    );
    return StaffTaskDetail.fromJson(_decode(response));
  }

  Future<void> createTask({
    required String baseUrl,
    required String token,
    required Map<String, dynamic> payload,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/tasks/create'),
        headers: _authorizedHeaders(token),
        body: jsonEncode(payload),
      ),
    );
    _decode(response);
  }

  Future<void> updateTask({
    required String baseUrl,
    required String token,
    required int taskId,
    required Map<String, dynamic> payload,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/tasks/$taskId/update'),
        headers: _authorizedHeaders(token),
        body: jsonEncode(payload),
      ),
    );
    _decode(response);
  }

  Future<void> saveTaskChecklist({
    required String baseUrl,
    required String token,
    required int taskId,
    required List<Map<String, dynamic>> checklistItems,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/tasks/$taskId/checklist'),
        headers: _authorizedHeaders(token),
        body: jsonEncode({'checklist_items': checklistItems}),
      ),
    );
    _decode(response);
  }

  Future<void> updateTaskStatus({
    required String baseUrl,
    required String token,
    required int taskId,
    required String taskStatus,
    String note = '',
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/tasks/$taskId/status'),
        headers: _authorizedHeaders(token),
        body: jsonEncode({'task_status': taskStatus, 'note': note}),
      ),
    );
    _decode(response);
  }

  Future<void> forwardTask({
    required String baseUrl,
    required String token,
    required int taskId,
    required int forwardTo,
    String note = '',
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/tasks/$taskId/forward'),
        headers: _authorizedHeaders(token),
        body: jsonEncode({'forward_to': forwardTo, 'forward_note': note}),
      ),
    );
    _decode(response);
  }

  Future<void> deleteTask({
    required String baseUrl,
    required String token,
    required int taskId,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/tasks/$taskId/delete'),
        headers: _authorizedHeaders(token),
      ),
    );
    _decode(response);
  }

  Future<StaffRanking> fetchRanking({
    required String baseUrl,
    required String token,
    int? year,
    int? month,
  }) async {
    final query = <String, String>{};
    if (year != null) query['year'] = year.toString();
    if (month != null) query['month'] = month.toString();

    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/ranking', query),
        headers: _authorizedHeaders(token),
      ),
    );
    return StaffRanking.fromJson(_decode(response));
  }

  Future<SupportIssuesData> fetchSupportIssues({
    required String baseUrl,
    required String token,
    String scope = 'open',
  }) async {
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/support/issues', {'scope': scope}),
        headers: _authorizedHeaders(token),
      ),
    );
    return SupportIssuesData.fromJson(_decode(response));
  }

  Future<SupportIssueDetail> fetchSupportIssue({
    required String baseUrl,
    required String token,
    required int issueId,
  }) async {
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/support/issues/$issueId'),
        headers: _authorizedHeaders(token),
      ),
    );
    return SupportIssueDetail.fromJson(_decode(response));
  }

  Future<void> postSupportComment({
    required String baseUrl,
    required String token,
    required int issueId,
    required String comment,
    String? attachmentName,
    List<int>? attachmentBytes,
  }) async {
    if (attachmentName != null &&
        attachmentName.isNotEmpty &&
        attachmentBytes != null &&
        attachmentBytes.isNotEmpty) {
      final request =
          http.MultipartRequest(
              'POST',
              _uri(
                baseUrl,
                '/api/mobile/staff/support/issues/$issueId/comment',
              ),
            )
            ..headers.addAll(_authorizedHeaders(token, json: false))
            ..fields['comment'] = comment
            ..files.add(
              http.MultipartFile.fromBytes(
                'attachment',
                attachmentBytes,
                filename: attachmentName,
              ),
            );

      final streamed = await _sendMultipart(request);
      _decode(streamed);
      return;
    }

    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/support/issues/$issueId/comment'),
        headers: _authorizedHeaders(token),
        body: jsonEncode({'comment': comment}),
      ),
    );
    _decode(response);
  }

  Future<void> closeSupportIssue({
    required String baseUrl,
    required String token,
    required int issueId,
    required String closeMessage,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/support/issues/$issueId/close'),
        headers: _authorizedHeaders(token),
        body: jsonEncode({'close_message': closeMessage}),
      ),
    );
    _decode(response);
  }

  Future<void> forwardSupportIssue({
    required String baseUrl,
    required String token,
    required int issueId,
    required int forwardTo,
    String note = '',
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/support/issues/$issueId/forward'),
        headers: _authorizedHeaders(token),
        body: jsonEncode({'forward_to': forwardTo, 'forward_note': note}),
      ),
    );
    _decode(response);
  }

  Future<StaffNotificationsData> fetchNotifications({
    required String baseUrl,
    required String token,
    int limit = 30,
  }) async {
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/notifications', {
          'limit': limit.toString(),
        }),
        headers: _authorizedHeaders(token),
      ),
    );
    return StaffNotificationsData.fromJson(_decode(response));
  }

  Future<void> markNotificationsSeen({
    required String baseUrl,
    required String token,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/notifications/mark-seen'),
        headers: _authorizedHeaders(token),
      ),
    );
    _decode(response);
  }

  Future<void> tagSupportIssue({
    required String baseUrl,
    required String token,
    required int issueId,
    required List<int> tagUserIds,
    String note = '',
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/support/issues/$issueId/tag'),
        headers: _authorizedHeaders(token),
        body: jsonEncode({'tag_user_ids': tagUserIds, 'tag_note': note}),
      ),
    );
    _decode(response);
  }

  Future<StaffProfile> fetchProfile({
    required String baseUrl,
    required String token,
  }) async {
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/profile'),
        headers: _authorizedHeaders(token),
      ),
    );
    final data = _decode(response);
    final raw = data['profile'];
    if (raw is! Map) {
      throw const ApiException('Profile payload missing.');
    }
    return StaffProfile.fromJson(Map<String, dynamic>.from(raw));
  }

  Future<StaffProfile> uploadAvatar({
    required String baseUrl,
    required String token,
    required String filename,
    required List<int> bytes,
  }) async {
    final request =
        http.MultipartRequest(
            'POST',
            _uri(baseUrl, '/api/mobile/staff/profile/avatar'),
          )
          ..headers.addAll(_authorizedHeaders(token, json: false))
          ..files.add(
            http.MultipartFile.fromBytes('avatar', bytes, filename: filename),
          );

    final response = await _sendMultipart(request);
    final data = _decode(response);
    final raw = data['profile'];
    if (raw is! Map) {
      throw const ApiException('Profile payload missing.');
    }
    return StaffProfile.fromJson(Map<String, dynamic>.from(raw));
  }

  Future<StaffProfile> updateProfile({
    required String baseUrl,
    required String token,
    required Map<String, dynamic> body,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/profile/update'),
        headers: _authorizedHeaders(token),
        body: jsonEncode(body),
      ),
    );
    final data = _decode(response);
    final raw = data['profile'];
    if (raw is! Map) {
      throw const ApiException('Profile payload missing.');
    }
    return StaffProfile.fromJson(Map<String, dynamic>.from(raw));
  }

  Future<MyDtrData> fetchMyDTR({
    required String baseUrl,
    required String token,
    int? month,
    int? year,
  }) async {
    final query = <String, String>{};
    if (month != null) query['month'] = month.toString();
    if (year != null) query['year'] = year.toString();
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/my-dtr', query),
        headers: _authorizedHeaders(token),
      ),
    );
    final data = _decode(response);
    return MyDtrData.fromJson(Map<String, dynamic>.from(data));
  }

  // ── Calendar ────────────────────────────────────────────────────────────

  Future<List<CalendarEvent>> fetchCalendarEvents({
    required String baseUrl,
    required String token,
    String? from,
    String? to,
  }) async {
    final query = <String, String>{};
    if ((from ?? '').isNotEmpty) query['from'] = from!;
    if ((to ?? '').isNotEmpty) query['to'] = to!;
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/calendar/events', query),
        headers: _authorizedHeaders(token),
      ),
    );
    final data = _decode(response);
    final list = (data['events'] as List?) ?? const [];
    return list
        .whereType<Map>()
        .map((e) => CalendarEvent.fromJson(Map<String, dynamic>.from(e)))
        .toList();
  }

  Future<CalendarEvent> createCalendarEvent({
    required String baseUrl,
    required String token,
    required Map<String, dynamic> body,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/calendar/events/create'),
        headers: _authorizedHeaders(token),
        body: jsonEncode(body),
      ),
    );
    final data = _decode(response);
    final raw = data['event'];
    if (raw is! Map) {
      throw const ApiException('Event payload missing.');
    }
    return CalendarEvent.fromJson(Map<String, dynamic>.from(raw));
  }

  Future<CalendarEvent> updateCalendarEvent({
    required String baseUrl,
    required String token,
    required int id,
    required Map<String, dynamic> body,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/calendar/events/$id/update'),
        headers: _authorizedHeaders(token),
        body: jsonEncode(body),
      ),
    );
    final data = _decode(response);
    final raw = data['event'];
    if (raw is! Map) {
      throw const ApiException('Event payload missing.');
    }
    return CalendarEvent.fromJson(Map<String, dynamic>.from(raw));
  }

  Future<void> deleteCalendarEvent({
    required String baseUrl,
    required String token,
    required int id,
  }) async {
    final response = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/staff/calendar/events/$id/delete'),
        headers: _authorizedHeaders(token),
      ),
    );
    _decode(response);
  }

  // ── Annual Goals ────────────────────────────────────────────────────────

  Future<AnnualGoalsData> fetchAnnualGoals({
    required String baseUrl,
    required String token,
  }) async {
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/annual-goals'),
        headers: _authorizedHeaders(token),
      ),
    );
    return AnnualGoalsData.fromJson(_decode(response));
  }

  Future<AnnualGoalDetail> fetchAnnualGoalDetail({
    required String baseUrl,
    required String token,
    required int year,
  }) async {
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/annual-goals/$year'),
        headers: _authorizedHeaders(token),
      ),
    );
    return AnnualGoalDetail.fromJson(_decode(response));
  }

  // ── Support Dashboard ──────────────────────────────────────────────────

  Future<SupportDashboard> fetchSupportDashboard({
    required String baseUrl,
    required String token,
  }) async {
    final response = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/staff/support/dashboard'),
        headers: _authorizedHeaders(token),
      ),
    );
    return SupportDashboard.fromJson(_decode(response));
  }

  Uri _uri(String baseUrl, String path, [Map<String, String>? query]) {
    final normalized = baseUrl.replaceFirst(RegExp(r'/+$'), '');
    final uri = Uri.parse('$normalized$path');
    if (query == null || query.isEmpty) return uri;
    return uri.replace(queryParameters: query);
  }

  Map<String, String> _authorizedHeaders(String token, {bool json = true}) {
    final headers = <String, String>{
      HttpHeaders.acceptHeader: 'application/json',
      HttpHeaders.authorizationHeader: 'Bearer $token',
    };
    if (json) {
      headers[HttpHeaders.contentTypeHeader] = 'application/json';
    }
    return headers;
  }

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

  Future<http.Response> _sendMultipart(http.MultipartRequest request) async {
    try {
      final streamed = await request.send();
      return http.Response.fromStream(streamed);
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
      decoded = jsonDecode(
        utf8.decode(response.bodyBytes, allowMalformed: true),
      );
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
