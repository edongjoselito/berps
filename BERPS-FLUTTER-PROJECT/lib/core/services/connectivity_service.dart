import 'dart:async';
import 'dart:io';

import 'package:flutter/foundation.dart';

/// Simple connectivity monitor that pings a lightweight endpoint
/// every few seconds to determine if the device has a working internet
/// connection (not just Wi-Fi / mobile data).
class ConnectivityService extends ChangeNotifier {
  ConnectivityService._();
  static final ConnectivityService _instance = ConnectivityService._();
  factory ConnectivityService() => _instance;

  bool _isOnline = true;
  bool get isOnline => _isOnline;

  Timer? _timer;
  bool _disposed = false;

  /// Start monitoring. Pass a URL that is fast and reliable
  /// (e.g. your own API health endpoint or a public CDN).
  void startMonitoring({
    String probeUrl = 'https://www.google.com',
    Duration interval = const Duration(seconds: 5),
  }) {
    _timer?.cancel();
    _check(probeUrl);
    _timer = Timer.periodic(interval, (_) => _check(probeUrl));
  }

  void stopMonitoring() {
    _timer?.cancel();
    _timer = null;
  }

  Future<void> _check(String url) async {
    try {
      final uri = Uri.parse(url);
      final client = HttpClient();
      client.badCertificateCallback = (cert, host, port) => true;
      final request = await client
          .getUrl(uri)
          .timeout(const Duration(seconds: 4));
      final response = await request.close().timeout(
        const Duration(seconds: 4),
      );
      await response.drain();
      client.close();
      _setOnline(true);
    } catch (_) {
      _setOnline(false);
    }
  }

  void _setOnline(bool value) {
    if (_isOnline == value) return;
    _isOnline = value;
    if (!_disposed) notifyListeners();
  }

  @override
  void dispose() {
    _disposed = true;
    stopMonitoring();
    super.dispose();
  }
}
