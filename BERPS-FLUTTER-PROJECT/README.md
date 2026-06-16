# berps_mobile

BERPS Staff mobile application.

## Server URL

Native release builds go straight to the login screen when the BERPS API base
URL is provided at build time. The URL entry welcome screen remains available
only for builds that do not include a URL.

Build the production Android App Bundle with the single tenant-aware BERPS URL:

```bash
flutter build appbundle --release --target-platform android-arm64 --dart-define=BERPS_BASE_URL=https://your-domain.com
```

APK and iOS builds use the same define:

```bash
flutter build apk --release --dart-define=BERPS_BASE_URL=https://your-domain.com
flutter build ios --release --dart-define=BERPS_BASE_URL=https://your-domain.com
```

Web builds still auto-detect from the website URL they are served from.

## Getting Started

This project is a starting point for a Flutter application.

A few resources to get you started if this is your first Flutter project:

- [Learn Flutter](https://docs.flutter.dev/get-started/learn-flutter)
- [Write your first Flutter app](https://docs.flutter.dev/get-started/codelab)
- [Flutter learning resources](https://docs.flutter.dev/reference/learning-resources)

For help getting started with Flutter development, view the
[online documentation](https://docs.flutter.dev/), which offers tutorials,
samples, guidance on mobile development, and a full API reference.
