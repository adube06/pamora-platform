import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import '../models/budget.dart';
import '../models/contribution.dart';
import '../models/expense.dart';
import '../models/pledge.dart';

final financeRepositoryProvider = Provider<FinanceRepository>((ref) {
  return FinanceRepository(ref.read(dioProvider));
});

class FinanceRepository {
  FinanceRepository(this._dio);

  final Dio _dio;

  String _date(DateTime d) => d.toIso8601String().split('T').first;

  // Budget
  Future<Budget?> budget(String occasionId) {
    return guarded(() async {
      final response = await _dio.get('/occasions/$occasionId/budget');
      final data = unwrapData(response);
      return data == null ? null : Budget.fromJson(data as Map<String, dynamic>);
    });
  }

  Future<Budget> createBudget(String occasionId,
      {required String name, required double plannedAmount, String? currency}) {
    return guarded(() async {
      final response = await _dio.post('/occasions/$occasionId/budget', data: {
        'name': name,
        'planned_amount': plannedAmount,
        if (currency != null && currency.isNotEmpty) 'currency': currency,
      });
      return Budget.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  Future<void> createBudgetItem(
    String occasionId, {
    required String name,
    required double estimatedCost,
    required int categoryId,
    String? currency,
  }) {
    return guarded(() => _dio.post('/occasions/$occasionId/budget-items', data: {
          'name': name,
          'estimated_cost': estimatedCost,
          'budget_category_id': categoryId,
          if (currency != null && currency.isNotEmpty) 'currency': currency,
        }));
  }

  // Contributions
  Future<(List<Contribution>, ContributionSummary)> contributions(String occasionId) {
    return guarded(() async {
      final response = await _dio.get('/occasions/$occasionId/contributions');
      final body = response.data as Map<String, dynamic>;
      final list = (body['data'] as List).map((e) => Contribution.fromJson(e as Map<String, dynamic>)).toList();
      final summary =
          ContributionSummary.fromJson((body['meta'] as Map<String, dynamic>?)?['summary'] as Map<String, dynamic>?);
      return (list, summary);
    });
  }

  Future<Contribution> createContribution(
    String occasionId, {
    required String contributorName,
    required double amount,
    required ContributionMethod method,
    required DateTime contributedAt,
    String? contributorPhone,
    String? message,
  }) {
    return guarded(() async {
      final response = await _dio.post('/occasions/$occasionId/contributions', data: {
        'contributor_name': contributorName,
        'amount': amount,
        'method': method.value,
        'contributed_at': _date(contributedAt),
        if (contributorPhone != null && contributorPhone.isNotEmpty) 'contributor_phone': contributorPhone,
        if (message != null && message.isNotEmpty) 'message': message,
      });
      return Contribution.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  // Expenses
  Future<List<Expense>> expenses(String occasionId) {
    return guarded(() async {
      final response = await _dio.get('/occasions/$occasionId/expenses');
      return (unwrapData(response) as List).map((e) => Expense.fromJson(e as Map<String, dynamic>)).toList();
    });
  }

  Future<Expense> createExpense(
    String occasionId, {
    required int budgetCategoryId,
    required double amount,
    required DateTime spentAt,
    String? description,
  }) {
    return guarded(() async {
      final response = await _dio.post('/occasions/$occasionId/expenses', data: {
        'budget_category_id': budgetCategoryId,
        'amount': amount,
        'spent_at': _date(spentAt),
        if (description != null && description.isNotEmpty) 'description': description,
      });
      return Expense.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  // Pledges
  Future<List<Pledge>> pledges(String occasionId) {
    return guarded(() async {
      final response = await _dio.get('/occasions/$occasionId/pledges');
      return (unwrapData(response) as List).map((e) => Pledge.fromJson(e as Map<String, dynamic>)).toList();
    });
  }

  Future<Pledge> createPledge(
    String occasionId, {
    required String pledgorName,
    required double amount,
    required DateTime pledgedAt,
    String? pledgorPhone,
    String? message,
    PledgeStatus status = PledgeStatus.pending,
  }) {
    return guarded(() async {
      final response = await _dio.post('/occasions/$occasionId/pledges', data: {
        'pledgor_name': pledgorName,
        'amount': amount,
        'pledged_at': _date(pledgedAt),
        'status': status.value,
        if (pledgorPhone != null && pledgorPhone.isNotEmpty) 'pledgor_phone': pledgorPhone,
        if (message != null && message.isNotEmpty) 'message': message,
      });
      return Pledge.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }
}
