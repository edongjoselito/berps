class Reminder {
  const Reminder({
    required this.id,
    required this.title,
    required this.description,
    required this.remindAt,
    required this.remindAtLabel,
    required this.recurrence,
  });

  final int id;
  final String title;
  final String description;

  /// Raw "yyyy-MM-dd HH:mm:ss" from the server.
  final String remindAt;
  final String remindAtLabel;

  /// once | monthly | yearly
  final String recurrence;

  DateTime? get remindAtDate => DateTime.tryParse(remindAt.replaceFirst(' ', 'T'));

  String get recurrenceLabel {
    switch (recurrence) {
      case 'monthly':
        return 'Monthly';
      case 'yearly':
        return 'Yearly';
      default:
        return 'One-time';
    }
  }

  factory Reminder.fromJson(Map<String, dynamic> json) {
    return Reminder(
      id: int.tryParse((json['id'] ?? '0').toString()) ?? 0,
      title: (json['title'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      remindAt: (json['remind_at'] ?? '').toString(),
      remindAtLabel: (json['remind_at_label'] ?? '').toString(),
      recurrence: ((json['recurrence'] ?? 'once').toString()).isEmpty
          ? 'once'
          : (json['recurrence'] ?? 'once').toString(),
    );
  }
}
