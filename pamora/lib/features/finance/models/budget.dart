import 'budget_category.dart';

enum BudgetStatus {
  active('active', 'Active'),
  closed('closed', 'Closed');

  const BudgetStatus(this.value, this.label);
  final String value;
  final String label;

  static BudgetStatus fromValue(String value) =>
      BudgetStatus.values.firstWhere((e) => e.value == value, orElse: () => BudgetStatus.active);
}

class Budget {
  const Budget({
    required this.id,
    required this.name,
    required this.currency,
    required this.plannedAmount,
    required this.status,
    required this.categories,
  });

  final String id;
  final String name;
  final String currency;
  final double plannedAmount;
  final BudgetStatus status;
  final List<BudgetCategory> categories;

  factory Budget.fromJson(Map<String, dynamic> json) {
    return Budget(
      id: json['id'].toString(),
      name: json['name'] as String? ?? '',
      currency: json['currency'] as String? ?? '',
      plannedAmount: double.tryParse(json['planned_amount'].toString()) ?? 0,
      status: BudgetStatus.fromValue(json['status'] as String? ?? 'active'),
      categories:
          (json['categories'] as List? ?? []).map((c) => BudgetCategory.fromJson(c as Map<String, dynamic>)).toList(),
    );
  }
}
