class TimelineEvent {
  const TimelineEvent({required this.id, required this.name, required this.scheduledAt, required this.createdAt});

  final String id;
  final String name;
  final DateTime? scheduledAt;
  final DateTime createdAt;

  factory TimelineEvent.fromJson(Map<String, dynamic> json) {
    return TimelineEvent(
      id: json['id'].toString(),
      name: json['name'] as String? ?? '',
      scheduledAt: json['scheduled_at'] != null ? DateTime.tryParse(json['scheduled_at'] as String) : null,
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ?? DateTime.now(),
    );
  }
}
