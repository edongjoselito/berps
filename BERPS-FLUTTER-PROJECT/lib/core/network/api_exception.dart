class ApiException implements Exception {
  const ApiException(this.message, {this.isNetworkError = false});

  final String message;
  final bool isNetworkError;

  @override
  String toString() => message;
}
