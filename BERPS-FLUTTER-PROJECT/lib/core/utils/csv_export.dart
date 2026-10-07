import 'dart:convert';
import 'dart:io';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';

/// Desktop CSV export: quotes fields, prompts for a save location via the
/// native file dialog and writes UTF-8 (with BOM so Excel opens it cleanly).
class CsvExport {
  static String _cell(String value) {
    final escaped = value.replaceAll('"', '""');
    return '"$escaped"';
  }

  /// Builds a CSV document from a header row and data rows.
  static String build(List<String> header, List<List<String>> rows) {
    final buffer = StringBuffer();
    buffer.writeln(header.map(_cell).join(','));
    for (final row in rows) {
      buffer.writeln(row.map(_cell).join(','));
    }
    return buffer.toString();
  }

  /// Opens the native save dialog and writes [csv] to the chosen path.
  /// Returns the saved path, or null when the user cancels / on web.
  static Future<String?> save({
    required String fileName,
    required String csv,
  }) async {
    if (kIsWeb) return null;
    final bytes = utf8.encode('\uFEFF$csv');
    final path = await FilePicker.platform.saveFile(
      dialogTitle: 'Export CSV',
      fileName: fileName,
      bytes: Uint8List.fromList(bytes),
      allowedExtensions: const ['csv'],
      type: FileType.custom,
    );
    if (path == null) return null;
    // saveFile already writes `bytes` on desktop; writing again is harmless
    // and covers platforms that return the path without persisting.
    if (!path.toLowerCase().endsWith('.csv')) {
      await File('$path.csv').writeAsBytes(bytes);
      return '$path.csv';
    }
    await File(path).writeAsBytes(bytes);
    return path;
  }
}
