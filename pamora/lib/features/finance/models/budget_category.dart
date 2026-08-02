class BudgetCategory {
  const BudgetCategory({required this.id, required this.uuid, required this.name});

  final int id;
  final String uuid;
  final String name;

  factory BudgetCategory.fromJson(Map<String, dynamic> json) {
    return BudgetCategory(
      id: json['id'] as int,
      uuid: json['uuid'] as String? ?? '',
      name: json['name'] as String? ?? '',
    );
  }
}
