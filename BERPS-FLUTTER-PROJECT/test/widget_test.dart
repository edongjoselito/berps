// Smoke test for BerpsMobileApp — verifies the splash renders without crashing.
//
// The full auth flow boots SharedPreferences asynchronously, so this test only
// checks that the root widget builds successfully on first frame.

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:berps_mobile/app/app.dart';

void main() {
  testWidgets('App boots into splash without crashing',
      (WidgetTester tester) async {
    await tester.pumpWidget(const BerpsMobileApp());
    expect(find.byType(MaterialApp), findsOneWidget);
  });
}
