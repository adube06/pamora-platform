import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/finance_repository.dart';
import '../models/budget.dart';
import '../models/contribution.dart';
import '../models/expense.dart';
import '../models/pledge.dart';

final budgetProvider = FutureProvider.autoDispose.family<Budget?, String>((ref, occasionId) {
  return ref.watch(financeRepositoryProvider).budget(occasionId);
});

final contributionsProvider =
    FutureProvider.autoDispose.family<(List<Contribution>, ContributionSummary), String>((ref, occasionId) {
  return ref.watch(financeRepositoryProvider).contributions(occasionId);
});

final expensesProvider = FutureProvider.autoDispose.family<List<Expense>, String>((ref, occasionId) {
  return ref.watch(financeRepositoryProvider).expenses(occasionId);
});

final pledgesProvider = FutureProvider.autoDispose.family<List<Pledge>, String>((ref, occasionId) {
  return ref.watch(financeRepositoryProvider).pledges(occasionId);
});
