import 'package:flutter/foundation.dart';

import '../../../core/network/api_exception.dart';
import '../data/auth_api.dart';
import '../data/session_store.dart';
import '../domain/mobile_config.dart';
import '../domain/staff_session.dart';

enum AuthStatus { loading, unpaired, signedOut, signedIn, error }

class AuthController extends ChangeNotifier {
  AuthController({required AuthApi api, required SessionStore store})
    : _api = api,
      _store = store;

  final AuthApi _api;
  final SessionStore _store;

  AuthStatus _status = AuthStatus.loading;
  StaffSession? _session;
  MobileConfig? _config;
  String _baseUrl = '';
  bool _isPaired = false;
  String? _lastError;

  AuthStatus get status => _status;
  StaffSession? get session => _session;
  MobileConfig? get config => _config;
  String get baseUrl => _baseUrl;
  bool get isPaired => _isPaired;
  String? get lastError => _lastError;
  AuthApi get api => _api;
  SessionStore get store => _store;
  bool get usesConfiguredBaseUrl => _api.configuredBaseUrl().isNotEmpty;

  Future<void> bootstrap() async {
    final configuredBaseUrl = _api.configuredBaseUrl();
    _baseUrl = configuredBaseUrl.isNotEmpty
        ? configuredBaseUrl
        : _store.readBaseUrl();
    _isPaired = configuredBaseUrl.isNotEmpty || _store.readIsPaired();

    if (_baseUrl.isNotEmpty) {
      try {
        await _loadConfigForBaseUrl(_baseUrl);
      } on ApiException {
        if (configuredBaseUrl.isEmpty) {
          await _detectWorkspace(silent: true);
        } else {
          _config = null;
          _isPaired = true;
        }
      }
    } else {
      await _detectWorkspace(silent: true);
    }

    final saved = _store.readSession();
    if (saved == null || saved.token.isEmpty) {
      _status = _isPaired ? AuthStatus.signedOut : AuthStatus.unpaired;
      notifyListeners();
      return;
    }

    try {
      _session = await _api.fetchCurrentSession(
        baseUrl: configuredBaseUrl.isNotEmpty ? _baseUrl : saved.baseUrl,
        token: saved.token,
      );
      await _store.saveSession(_session!);
      _status = AuthStatus.signedIn;
    } on ApiException {
      await _store.clearSession();
      _session = null;
      _status = AuthStatus.signedOut;
    }
    notifyListeners();
  }

  Future<void> ensureWorkspaceReady() async {
    if (_baseUrl.isNotEmpty && _config != null) {
      return;
    }

    final configuredBaseUrl = _api.configuredBaseUrl();
    if (_baseUrl.isEmpty && configuredBaseUrl.isNotEmpty) {
      _baseUrl = configuredBaseUrl;
      _isPaired = true;
    }

    if (_baseUrl.isNotEmpty) {
      try {
        await _loadConfigForBaseUrl(_baseUrl);
        _status = AuthStatus.signedOut;
        notifyListeners();
        return;
      } on ApiException {
        if (configuredBaseUrl.isNotEmpty) {
          _config = null;
          _isPaired = true;
          _status = AuthStatus.signedOut;
          notifyListeners();
          rethrow;
        }
        _baseUrl = '';
        _config = null;
        _isPaired = false;
      }
    }

    await _detectWorkspace();
  }

  Future<void> savePairing(String baseUrl) async {
    final normalized = _api.normalizeBaseUrl(baseUrl);
    if (normalized.isEmpty) {
      throw const ApiException('Please enter a valid workspace URL.');
    }
    await _loadConfigForBaseUrl(normalized);
    _status = AuthStatus.signedOut;
    notifyListeners();
  }

  Future<void> signIn({
    required String username,
    required String password,
  }) async {
    if (_baseUrl.isEmpty) {
      await ensureWorkspaceReady();
    }
    if (_baseUrl.isEmpty) {
      throw const ApiException('Unable to detect the workspace automatically.');
    }
    _lastError = null;
    try {
      _session = await _api.login(
        baseUrl: _baseUrl,
        username: username,
        password: password,
      );
      await _store.saveSession(_session!);
      _status = AuthStatus.signedIn;
    } on ApiException catch (e) {
      _lastError = e.message;
      rethrow;
    } finally {
      notifyListeners();
    }
  }

  Future<void> signOut() async {
    final current = _session;
    if (current != null && current.token.isNotEmpty) {
      try {
        await _api.logout(baseUrl: current.baseUrl, token: current.token);
      } on ApiException {
        // Ignore — we still clear local state.
      }
    }
    await _store.clearSession();
    _session = null;
    _status = AuthStatus.signedOut;
    notifyListeners();
  }

  Future<void> updateSessionAvatar(String avatarUrl) async {
    final current = _session;
    if (current == null) return;
    _session = current.copyWith(avatarUrl: avatarUrl);
    await _store.saveSession(_session!);
    notifyListeners();
  }

  Future<void> resetPairing() async {
    final configuredBaseUrl = _api.configuredBaseUrl();
    if (configuredBaseUrl.isNotEmpty) {
      await _store.clearSession();
      _session = null;
      _baseUrl = configuredBaseUrl;
      _isPaired = true;
      try {
        await _loadConfigForBaseUrl(configuredBaseUrl);
      } on ApiException {
        _config = null;
      }
      _status = AuthStatus.signedOut;
      notifyListeners();
      return;
    }

    await _store.clearPairing();
    _session = null;
    _config = null;
    _baseUrl = '';
    _isPaired = false;
    _status = AuthStatus.unpaired;
    notifyListeners();
  }

  Future<void> _detectWorkspace({bool silent = false}) async {
    try {
      final detected = await _api.detectWorkspace();
      _config = detected.config;
      _baseUrl = detected.baseUrl;
      _isPaired = true;
      await _store.savePairing(_baseUrl);
      _status = AuthStatus.signedOut;
      notifyListeners();
    } on ApiException {
      if (!silent) rethrow;
    }
  }

  Future<void> _loadConfigForBaseUrl(String baseUrl) async {
    // Probe first; fetchConfig throws if the URL is unreachable or not BERPS.
    final config = await _api.fetchConfig(baseUrl);

    // Use the server's authoritative base_url, which may upgrade http to https
    // or normalize a redirected host.
    final resolved = _api.normalizeBaseUrl(
      config.baseUrl.isNotEmpty ? config.baseUrl : baseUrl,
    );

    _config = config;
    _baseUrl = resolved;
    _isPaired = true;
    await _store.savePairing(resolved);
  }
}
