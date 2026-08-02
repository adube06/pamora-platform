import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/people_repository.dart';
import '../models/members_overview.dart';

final membersProvider = FutureProvider.autoDispose.family<MembersOverview, String>((ref, occasionId) {
  return ref.watch(peopleRepositoryProvider).members(occasionId);
});
