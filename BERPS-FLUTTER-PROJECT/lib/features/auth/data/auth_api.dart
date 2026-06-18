import 'dart:convert';
import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

import '../../../core/network/api_exception.dart';
import '../domain/mobile_config.dart';
import '../domain/staff_session.dart';

class DetectedWorkspace {
  const DetectedWorkspace({required this.baseUrl, required this.config});

  final String baseUrl;
  final MobileConfig config;
}

class AuthApi {
  AuthApi({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  String normalizeBaseUrl(String value) {
    var normalized = value.trim();
    if (normalized.isEmpty) return '';

    if (!normalized.startsWith('http://') &&
        !normalized.startsWith('https://')) {
      normalized = 'https://$normalized';
    }
    return normalized.replaceFirst(RegExp(r'/+$'), '');
  }

  String configuredBaseUrl() {
    final explicit = normalizeBaseUrl(
      const String.fromEnvironment('BERPS_BASE_URL'),
    );
    if (explicit.isNotEmpty) return explicit;

    return normalizeBaseUrl(
      const String.fromEnvironment('BERPS_DEFAULT_BASE_URL'),
    );
  }

  Future<MobileConfig> fetchConfig(String baseUrl) async {
    final candidates = <String>[baseUrl];
    final normalized = normalizeBaseUrl(baseUrl);

    // If the user typed a bare domain, also try https://
    if (!normalized.startsWith('https://')) {
      candidates.add(normalized.replaceFirst('http://', 'https://'));
    }

    ApiException? lastError;
    for (final url in candidates) {
      try {
        var response = await _safeRequest(
          () => _client.get(_uri(url, '/api/mobile/config'), headers: _headers),
        );

        // cPanel redirects often return HTML instead of HTTP 301.
        final redirectUrl = _extractRedirectUrl(response);
        if (redirectUrl != null) {
          response = await _safeRequest(
            () => _client.get(
              _uri(redirectUrl, '/api/mobile/config'),
              headers: _headers,
            ),
          );
        }

        return MobileConfig.fromJson(_decode(response));
      } on ApiException catch (e) {
        lastError = e;
      }
    }

    throw lastError ?? const ApiException('Unable to connect to the server.');
  }

  Future<DetectedWorkspace> detectWorkspace() async {
    final candidates = _baseUrlCandidates().toList(growable: false);
    if (candidates.isEmpty) {
      throw const ApiException(
        'Auto-detect needs the app to run from the BERPS website, or BERPS_BASE_URL to be set at build time. Native Android/iOS apps cannot infer the server URL from uploaded web files.',
      );
    }

    ApiException? lastError;
    for (final candidate in candidates) {
      try {
        final config = await fetchConfig(candidate);
        final resolvedBaseUrl = normalizeBaseUrl(
          config.baseUrl.isNotEmpty ? config.baseUrl : candidate,
        );
        return DetectedWorkspace(baseUrl: resolvedBaseUrl, config: config);
      } on ApiException catch (error) {
        lastError = error;
      }
    }

    final tried = candidates.take(4).join(', ');
    throw ApiException(
      lastError?.message ??
          'Unable to detect the workspace URL automatically. Tried: $tried',
    );
  }

  Future<StaffSession> login({
    required String baseUrl,
    required String username,
    required String password,
  }) async {
    final response = await _safeRequest(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/auth/login'),
        headers: _headers,
        body: jsonEncode({'username': username, 'password': password}),
      ),
    );

    return StaffSession.fromApi(
      _decode(response),
      baseUrl: normalizeBaseUrl(baseUrl),
    );
  }

  /// Creates a new self-service company account. Returns the server message
  /// (e.g. whether the confirmation email was sent).
  Future<String> signup({
    required String baseUrl,
    required String firstName,
    required String lastName,
    required String email,
    required String password,
  }) async {
    final response = await _safeRequest(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/auth/signup'),
        headers: _headers,
        body: jsonEncode({
          'first_name': firstName,
          'last_name': lastName,
          'email': email,
          'password': password,
        }),
      ),
    );

    final data = _decode(response);
    return (data['message'] ?? 'Account created.').toString();
  }

  Future<StaffSession> fetchCurrentSession({
    required String baseUrl,
    required String token,
  }) async {
    final response = await _safeRequest(
      () => _client.get(
        _uri(baseUrl, '/api/mobile/auth/me'),
        headers: {
          ..._headers,
          HttpHeaders.authorizationHeader: 'Bearer $token',
        },
      ),
    );

    return StaffSession.fromApi(
      _decode(response),
      baseUrl: normalizeBaseUrl(baseUrl),
      fallbackToken: token,
    );
  }

  Future<void> logout({required String baseUrl, required String token}) async {
    await _safeRequest(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/auth/logout'),
        headers: {
          ..._headers,
          HttpHeaders.authorizationHeader: 'Bearer $token',
        },
      ),
    ).then(_decode);
  }

  Future<void> requestPasswordReset({
    required String baseUrl,
    required String email,
  }) async {
    final response = await _safeRequest(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/auth/forgot-password'),
        headers: _headers,
        body: jsonEncode({'email': email}),
      ),
    );
    _decode(response);
  }

  Future<String> verifyResetOtp({
    required String baseUrl,
    required String email,
    required String otp,
  }) async {
    final response = await _safeRequest(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/auth/verify-otp'),
        headers: _headers,
        body: jsonEncode({'email': email, 'otp': otp}),
      ),
    );
    final data = _decode(response);
    final token = (data['reset_token'] ?? '').toString();
    if (token.isEmpty) {
      throw const ApiException('Server did not return a reset token.');
    }
    return token;
  }

  Future<void> resetPasswordWithToken({
    required String baseUrl,
    required String email,
    required String resetToken,
    required String newPassword,
  }) async {
    final response = await _safeRequest(
      () => _client.post(
        _uri(baseUrl, '/api/mobile/auth/reset-password'),
        headers: _headers,
        body: jsonEncode({
          'email': email,
          'reset_token': resetToken,
          'new_password': newPassword,
        }),
      ),
    );
    _decode(response);
  }

  // ── Internals ──────────────────────────────────────────────────────────────

  Uri _uri(String baseUrl, String path) {
    final normalized = normalizeBaseUrl(baseUrl);
    if (normalized.isEmpty) {
      throw const ApiException('Workspace URL is required.');
    }
    return Uri.parse('$normalized$path');
  }

  Iterable<String> _baseUrlCandidates() sync* {
    final seen = <String>{};

    final configured = configuredBaseUrl();
    if (configured.isNotEmpty && seen.add(configured)) {
      yield configured;
    }

    final runtimeBase = Uri.base;
    final isHttpRuntime =
        runtimeBase.scheme == 'http' || runtimeBase.scheme == 'https';

    // For web builds, derive the default from the current origin since the
    // API lives in the same shared folder / deployment. No hardcoded domain.
    final webOrigin = (kIsWeb && isHttpRuntime)
        ? normalizeBaseUrl(
            '${runtimeBase.scheme}://${runtimeBase.host}${runtimeBase.hasPort ? ':${runtimeBase.port}' : ''}',
          )
        : '';

    if (!isHttpRuntime && webOrigin.isNotEmpty && seen.add(webOrigin)) {
      yield webOrigin;
    }

    if (!isHttpRuntime) {
      return;
    }

    final runtimeHost = runtimeBase.host.toLowerCase();
    final runtimeScheme = runtimeBase.scheme == 'https' ? 'https' : 'http';

    // `flutter run -d chrome` serves the app from a temporary localhost port,
    // while BERPS usually lives under `/berps` on Apache/XAMPP.
    if (runtimeHost == 'localhost' || runtimeHost == '127.0.0.1') {
      final localhostWorkspace = normalizeBaseUrl(
        '$runtimeScheme://$runtimeHost/berps',
      );
      if (localhostWorkspace.isNotEmpty && seen.add(localhostWorkspace)) {
        yield localhostWorkspace;
      }
    }

    final rawSegments = runtimeBase.pathSegments
        .where((segment) => segment.isNotEmpty && segment != 'index.html')
        .toList();

    final trimmedSegments = List<String>.from(rawSegments);
    while (trimmedSegments.isNotEmpty &&
        (trimmedSegments.last == 'web' ||
            trimmedSegments.last == 'build' ||
            trimmedSegments.last.endsWith('.html'))) {
      trimmedSegments.removeLast();
    }

    final candidatePaths = <List<String>>[trimmedSegments, rawSegments];

    for (final segments in candidatePaths) {
      for (var length = segments.length; length >= 0; length--) {
        final path = length == 0 ? '' : '/${segments.take(length).join('/')}';
        final candidate = normalizeBaseUrl(
          '${runtimeBase.scheme}://${runtimeBase.authority}$path',
        );
        if (candidate.isNotEmpty && seen.add(candidate)) {
          yield candidate;
        }
      }
    }
  }

  Future<http.Response> _safeRequest(
    Future<http.Response> Function() request,
  ) async {
    try {
      return await request();
    } on SocketException {
      throw const ApiException(
        'Cannot reach the server. Check the URL and your network.',
        isNetworkError: true,
      );
    } on HttpException {
      throw const ApiException(
        'Unexpected HTTP error while contacting the server.',
        isNetworkError: true,
      );
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
        'The server returned an empty response (HTTP ${response.statusCode}).',
      );
    }

    late final Object? decoded;
    try {
      decoded = jsonDecode(
        utf8.decode(response.bodyBytes, allowMalformed: true),
      );
    } on FormatException {
      final body = utf8.decode(response.bodyBytes, allowMalformed: true);
      if (body.trim().startsWith('<')) {
        throw const ApiException(
          'The server returned HTML instead of JSON. '
          'This usually means the URL redirects to a web page. '
          'Try using the new domain directly.',
        );
      }
      throw const ApiException('Invalid server response format.');
    }
    if (decoded is! Map) {
      throw const ApiException('Invalid server response.');
    }

    final data = Map<String, dynamic>.from(decoded);
    final message = (data['message'] ?? data['error'] ?? 'Request failed.')
        .toString();

    if (response.statusCode < 200 || response.statusCode >= 300) {
      throw ApiException(message);
    }
    if (data['ok'] == false) {
      throw ApiException(message);
    }
    return data;
  }

  /// Tries to extract a redirect target URL from an HTML response body.
  /// Handles cPanel-style meta-refresh and JavaScript redirects.
  String? _extractRedirectUrl(http.Response response) {
    if (response.statusCode >= 300 && response.statusCode < 400) {
      final location = response.headers['location'];
      if (location != null && location.isNotEmpty) {
        return location;
      }
    }

    final body = utf8.decode(response.bodyBytes, allowMalformed: true);
    if (!body.trim().startsWith('<')) return null;

    final lower = body.toLowerCase();

    // Meta refresh: content="0;url=https://..." or content="0;url=https://..."
    final metaIdx = lower.indexOf('url=');
    if (metaIdx != -1) {
      var urlStart = metaIdx + 4;
      final quote = body[urlStart];
      if (quote == '"' || quote == "'") {
        urlStart += 1;
        final end = body.indexOf(quote, urlStart);
        if (end != -1) {
          final url = body.substring(urlStart, end).trim();
          if (url.startsWith('http')) return url;
        }
      } else {
        // Unquoted URL: url=https://...
        final end = body.indexOf('"', urlStart);
        final url = end == -1
            ? body.substring(urlStart).trim()
            : body.substring(urlStart, end).trim();
        if (url.startsWith('http')) return url;
      }
    }

    // JavaScript redirect
    final jsIdx = lower.indexOf('window.location');
    if (jsIdx != -1) {
      final eqIdx = body.indexOf('=', jsIdx);
      if (eqIdx != -1) {
        var urlStart = eqIdx + 1;
        final quote = body[urlStart];
        if (quote == '"' || quote == "'") {
          urlStart += 1;
          final end = body.indexOf(quote, urlStart);
          if (end != -1) {
            final url = body.substring(urlStart, end).trim();
            if (url.startsWith('http')) return url;
          }
        } else {
          // Unquoted
          final end = body.indexOf(';', urlStart);
          final url = end == -1
              ? body.substring(urlStart).trim()
              : body.substring(urlStart, end).trim();
          if (url.startsWith('http')) return url;
        }
      }
    }

    // Plain link fallback: <a href="https://...">
    final hrefIdx = lower.indexOf('href=');
    if (hrefIdx != -1) {
      var urlStart = hrefIdx + 5;
      final quote = body[urlStart];
      if (quote == '"' || quote == "'") {
        urlStart += 1;
        final end = body.indexOf(quote, urlStart);
        if (end != -1) {
          final url = body.substring(urlStart, end).trim();
          if (url.startsWith('http')) return url;
        }
      }
    }

    return null;
  }

  static const Map<String, String> _headers = {
    HttpHeaders.acceptHeader: 'application/json',
    HttpHeaders.contentTypeHeader: 'application/json',
  };
}
