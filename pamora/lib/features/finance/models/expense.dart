import 'budget_category.dart';

class Expense {
  const Expense({
    required this.id,
    required this.amount,
    required this.currency,
    required this.spentAt,
    required this.category,
    this.description,
  });

  final String id;
  final double amount;
  final String currency;
  final DateTime spentAt;
  final BudgetCategory category;
  final String? description;

  factory Expense.fromJson(Map<String, dynamic> json) {
    return Expense(
      id: json['id'].toString(),
      amount: double.tryParse(json['amount'].toString()) ?? 0,
      currency: json['currency'] as String? ?? '',
      spentAt: DateTime.tryParse(json['spent_at'] as String? ?? '') ?? DateTime.now(),
      category: BudgetCategory.fromJson(json['category'] as Map<String, dynamic>? ?? const {'id': 0, 'name': ''}),
      description: json['description'] as String?,
    );
  }
}
