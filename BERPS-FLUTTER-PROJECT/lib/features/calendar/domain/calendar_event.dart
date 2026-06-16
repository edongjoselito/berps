class CalendarEvent {
  const CalendarEvent({
    required this.id,
    required this.title,
    required this.description,
    required this.notes,
    required this.start,
    required this.end,
    required this.allDay,
    required this.eventType,
    required this.color,
    required this.location,
    required this.isPublic,
    required this.reminderEmailEnabled,
    required this.reminderEmail,
    required this.canEdit,
    required this.canDelete,
    required this.own,
  });

  final int id;
  final String title;
  final String description;
  final String notes;
  final DateTime start;
  final DateTime end;
  final bool allDay;
  final String eventType;
  final String color;
  final String location;
  final bool isPublic;
  final bool reminderEmailEnabled;
  final String reminderEmail;
  final bool canEdit;
  final bool canDelete;
  final bool own;

  static DateTime _parse(String value) {
    if (value.isEmpty) return DateTime.now();
    return DateTime.tryParse(value.replaceFirst(' ', 'T')) ?? DateTime.now();
  }

  factory CalendarEvent.fromJson(Map<String, dynamic> json) {
    return CalendarEvent(
      id: int.tryParse((json['id'] ?? '0').toString()) ?? 0,
      title: (json['title'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      notes: (json['notes'] ?? '').toString(),
      start: _parse((json['start'] ?? '').toString()),
      end: _parse((json['end'] ?? '').toString()),
      allDay: json['all_day'] == true,
      eventType: (json['event_type'] ?? 'default').toString(),
      color: (json['color'] ?? '#3788d8').toString(),
      location: (json['location'] ?? '').toString(),
      isPublic: json['is_public'] == true,
      reminderEmailEnabled: json['reminder_email_enabled'] == true,
      reminderEmail: (json['reminder_email'] ?? '').toString(),
      canEdit: json['can_edit'] == true,
      canDelete: json['can_delete'] == true,
      own: json['own'] == true,
    );
  }
}
