const List<String> _months = <String>[
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December',
];

String formatDisplayDate(String value, {bool includeYear = true}) {
  final date = DateTime.tryParse(value);
  if (date == null) return value;
  final month = _months[date.month - 1];
  if (includeYear) {
    return '$month ${date.day}, ${date.year}';
  }
  return '$month ${date.day}';
}

String formatCompactDate(String value) {
  final date = DateTime.tryParse(value);
  if (date == null) return value;
  final month = _months[date.month - 1].substring(0, 3);
  return '$month ${date.day}, ${date.year}';
}

String formatRangeLabel(String from, String to) {
  if (from == to) return formatCompactDate(from);
  return '${formatCompactDate(from)} to ${formatCompactDate(to)}';
}
