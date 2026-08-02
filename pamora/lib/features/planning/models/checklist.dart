class Checklist {
  const Checklist({required this.id, required this.name, required this.createdAt});

  final String id;
  final String name;
  final DateTime createdAt;

  factory Checklist.fromJson(Map<String, dynamic> json) {
    return Checklist(
      id: json['id'].toString(),
      name: json['name'] as String? ?? '',
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ?? DateTime.now(),
    );
  }
}
