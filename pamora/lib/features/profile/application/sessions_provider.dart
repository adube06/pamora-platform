import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/sessions_repository.dart';
import '../models/session.dart';

final sessionsProvider = FutureProvider.autoDispose<List<Session>>((ref) {
  return ref.watch(sessionsRepositoryProvider).list();
});
