import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../../../core/network/api_exception.dart';
import '../domain/admin_models.dart';

/// Talks to application/controllers/api/MobileAdmin.php. Mirrors the networking
/// conventions used by StaffApi (bearer token, JSON body, `ok` envelope).
class AdminApi {
  AdminApi({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  // ── Dashboard ──────────────────────────────────────────────────────────────

  Future<AdminDashboard> fetchDashboard({
    required String baseUrl,
    required String token,
  }) async {
    final res = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/admin/dashboard'),
        headers: _headers(token),
      ),
    );
    return AdminDashboard.fromJson(_decode(res));
  }

  // ── Tasks (projectAddTask) ───────────────────────────────────────────────────

  Future<AdminTasksData> fetchTasks({
    required String baseUrl,
    required String token,
    String status = 'open',
  }) async {
    final res = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/admin/tasks', {'status': status}),
        headers: _headers(token),
      ),
    );
    return AdminTasksData.fromJson(_decode(res));
  }

  Future<int> createTask({
    required String baseUrl,
    required String token,
    required String task,
    required int projectId,
    required int assignedPersonId,
    String priority = '2',
    String? reportedDate,
    String? dueDate,
    String? attachmentLink,
    int points = 1,
    List<String> checklistItems = const [],
  }) async {
    final res = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/admin/tasks/create'),
        headers: _headers(token),
        body: jsonEncode({
          'task': task,
          'project_id': projectId,
          'assigned_person_id': assignedPersonId,
          'priority': priority,
          'reported_date': ?reportedDate,
          'due_date': ?dueDate,
          if (attachmentLink != null && attachmentLink.isNotEmpty)
            'attachment_link': attachmentLink,
          'points': points,
          'checklist_items': checklistItems,
        }),
      ),
    );
    return int.tryParse('${_decode(res)['task_id'] ?? 0}') ?? 0;
  }

  // ── Employee tasks ───────────────────────────────────────────────────────────

  Future<List<EmployeeTaskGroup>> fetchEmployeeTasks({
    required String baseUrl,
    required String token,
    String taskFilter = 'all',
  }) async {
    final res = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/admin/employee-tasks', {
          'task_filter': taskFilter,
        }),
        headers: _headers(token),
      ),
    );
    final data = _decode(res);
    return ((data['employees'] as List?) ?? [])
        .map((e) => EmployeeTaskGroup.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  // ── Accomplishments ──────────────────────────────────────────────────────────

  Future<List<AdminAccomplishment>> fetchAccomplishments({
    required String baseUrl,
    required String token,
    required int month,
    required int year,
  }) async {
    final res = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/admin/accomplishments', {
          'month': '$month',
          'year': '$year',
        }),
        headers: _headers(token),
      ),
    );
    final data = _decode(res);
    return ((data['data'] as List?) ?? [])
        .map((e) => AdminAccomplishment.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<List<EmployeeOption>> fetchEmployeeOptions({
    required String baseUrl,
    required String token,
  }) async {
    final res = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/admin/employee-accomplishments'),
        headers: _headers(token),
      ),
    );
    final data = _decode(res);
    return ((data['employees'] as List?) ?? [])
        .map((e) => EmployeeOption.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<List<AdminAccomplishment>> fetchEmployeeAccomplishmentData({
    required String baseUrl,
    required String token,
    required String userId,
    String? reportPeriod,
    String? endDate,
  }) async {
    final res = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/admin/employee-accomplishments/data', {
          'user_id': userId,
          if (reportPeriod != null && reportPeriod.isNotEmpty)
            'report_period': reportPeriod,
          if (endDate != null && endDate.isNotEmpty) 'end_date': endDate,
        }),
        headers: _headers(token),
      ),
    );
    final data = _decode(res);
    return ((data['data'] as List?) ?? [])
        .map((e) => AdminAccomplishment.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  // ── Attendance ───────────────────────────────────────────────────────────────

  Future<AdminAttendanceData> fetchAttendance({
    required String baseUrl,
    required String token,
    String? from,
    String? to,
  }) async {
    final query = <String, String>{};
    if ((from ?? '').isNotEmpty) query['from'] = from!;
    if ((to ?? '').isNotEmpty) query['to'] = to!;
    final res = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/admin/attendance', query),
        headers: _headers(token),
      ),
    );
    return AdminAttendanceData.fromJson(_decode(res));
  }

  // ── Employee DTR ─────────────────────────────────────────────────────────────

  Future<AdminDtrData> fetchDtr({
    required String baseUrl,
    required String token,
    String? employeeId,
    int? month,
    int? year,
  }) async {
    final query = <String, String>{};
    if ((employeeId ?? '').isNotEmpty) query['id'] = employeeId!;
    if (month != null) query['month'] = '$month';
    if (year != null) query['year'] = '$year';
    final res = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/admin/dtr', query),
        headers: _headers(token),
      ),
    );
    return AdminDtrData.fromJson(_decode(res));
  }

  // ── Clients ──────────────────────────────────────────────────────────────────

  Future<AdminClientsData> fetchClients({
    required String baseUrl,
    required String token,
  }) async {
    final res = await _request(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/admin/clients'),
        headers: _headers(token),
      ),
    );
    return AdminClientsData.fromJson(_decode(res));
  }

  Future<String> createClient({
    required String baseUrl,
    required String token,
    required Map<String, dynamic> payload,
  }) async {
    final res = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/admin/clients/create'),
        headers: _headers(token),
        body: jsonEncode(payload),
      ),
    );
    return (_decode(res)['cust_id'] ?? '').toString();
  }

  Future<void> updateClient({
    required String baseUrl,
    required String token,
    required Map<String, dynamic> payload,
  }) async {
    final res = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/admin/clients/update'),
        headers: _headers(token),
        body: jsonEncode(payload),
      ),
    );
    _decode(res);
  }

  Future<void> deleteClient({
    required String baseUrl,
    required String token,
    required String custId,
  }) async {
    final res = await _request(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/admin/clients/delete'),
        headers: _headers(token),
        body: jsonEncode({'CustID': custId}),
      ),
    );
    _decode(res);
  }

  // ── Internals ──────────────────────────────────────────────────────────────

  Uri _uri(String baseUrl, String path, [Map<String, String>? query]) {
    final normalized = baseUrl.replaceFirst(RegExp(r'/+$'), '');
    final uri = Uri.parse('$normalized$path');
    if (query == null || query.isEmpty) return uri;
    return uri.replace(queryParameters: query);
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
