import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/occasions_repository.dart';
import '../models/occasion.dart';

final occasionsListProvider = FutureProvider.autoDispose<List<Occasion>>((ref) {
  return ref.watch(occasionsRepositoryProvider).list();
});

final occasionDetailProvider = FutureProvider.autoDispose.family<Occasion, String>((ref, id) {
  return ref.watch(occasionsRepositoryProvider).show(id);
});
