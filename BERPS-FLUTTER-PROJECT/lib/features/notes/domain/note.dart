class Note {
  const Note({
    required this.id,
    required this.title,
    required this.description,
    required this.tags,
    required this.isFavorite,
    required this.date,
    required this.dateLabel,
  });

  final int id;
  final String title;
  final String description;
  final List<String> tags;
  final bool isFavorite;
  final String date;
  final String dateLabel;

  String get displayTitle => title.trim().isEmpty ? 'Untitled note' : title;

  factory Note.fromJson(Map<String, dynamic> json) {
    final rawTags = json['tags'];
    return Note(
      id: int.tryParse((json['id'] ?? '0').toString()) ?? 0,
      title: (json['title'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      tags: rawTags is List
          ? rawTags
              .map((e) => e.toString().trim())
              .where((e) => e.isNotEmpty)
              .toList(growable: false)
          : const <String>[],
      isFavorite: json['is_favorite'] == true || json['is_favorite'] == 1,
      date: (json['date'] ?? '').toString(),
      dateLabel: (json['date_label'] ?? '').toString(),
    );
  }
}
