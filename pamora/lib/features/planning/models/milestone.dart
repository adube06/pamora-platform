class Milestone {
  const Milestone({required this.id, required this.name, required this.isAchieved, required this.createdAt});

  final String id;
  final String name;
  final bool isAchieved;
  final DateTime createdAt;

  factory Milestone.fromJson(Map<String, dynamic> json) {
    return Milestone(
      id: json['id'].toString(),
      name: json['name'] as String? ?? '',
      isAchieved: json['is_achieved'] == true,
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ?? DateTime.now(),
    );
  }
}
