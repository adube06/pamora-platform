import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/application/auth_provider.dart';
import '../../features/auth/presentation/forgot_password_screen.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/auth/presentation/onboarding_screen.dart';
import '../../features/auth/presentation/register_screen.dart';
import '../../features/auth/presentation/reset_password_screen.dart';
import '../../features/auth/presentation/splash_screen.dart';
import '../../features/finance/presentation/budget_setup_screen.dart';
import '../../features/finance/presentation/contribution_create_screen.dart';
import '../../features/finance/presentation/contribution_list_screen.dart';
import '../../features/finance/presentation/expense_create_screen.dart';
import '../../features/finance/presentation/expense_list_screen.dart';
import '../../features/finance/presentation/pledge_create_screen.dart';
import '../../features/finance/presentation/pledge_list_screen.dart';
import '../../features/home/presentation/home_screen.dart';
import '../../features/notifications/presentation/notifications_screen.dart';
import '../../features/occasions/application/occasions_provider.dart';
import '../../features/occasions/presentation/occasion_create_screen.dart';
import '../../features/occasions/presentation/occasion_detail_screen.dart';
import '../../features/occasions/presentation/occasion_edit_screen.dart';
import '../../features/occasions/presentation/occasion_list_screen.dart';
import '../../features/planning/presentation/checklist_create_screen.dart';
import '../../features/planning/presentation/checklist_list_screen.dart';
import '../../features/planning/presentation/milestone_create_screen.dart';
import '../../features/planning/presentation/milestone_list_screen.dart';
import '../../features/planning/presentation/task_create_screen.dart';
import '../../features/planning/presentation/task_list_screen.dart';
import '../../features/planning/presentation/timeline_event_create_screen.dart';
import '../../features/planning/presentation/timeline_screen.dart';
import '../../features/profile/presentation/notification_preferences_screen.dart';
import '../../features/profile/presentation/profile_screen.dart';
import '../../features/profile/presentation/sessions_screen.dart';
import '../widgets/empty_state.dart';
import 'app_routes.dart';
import 'app_shell.dart';

const _publicRoutes = {
  AppRoutes.splash,
  AppRoutes.onboarding,
  AppRoutes.login,
  AppRoutes.register,
  AppRoutes.forgotPassword,
  AppRoutes.resetPassword,
};

/// Bridges Riverpod's [authProvider] into a [Listenable] go_router can use
/// for `refreshListenable`, so route guards re-evaluate on login/logout
/// without recreating the router (which would blow away navigation state).
class _AuthRefreshNotifier extends ChangeNotifier {
  _AuthRefreshNotifier(Ref ref) {
    ref.listen(authProvider, (_, __) => notifyListeners());
  }
}

final routerProvider = Provider<GoRouter>((ref) {
  final refreshNotifier = _AuthRefreshNotifier(ref);

  return GoRouter(
    initialLocation: AppRoutes.splash,
    refreshListenable: refreshNotifier,
    redirect: (context, state) {
      final authState = ref.read(authProvider);
      final isResolved = authState.hasValue || authState.hasError;
      final isLoggedIn = authState.value != null;
      final isPublic = _publicRoutes.contains(state.matchedLocation);

      if (!isResolved) return null; // splash owns the initial resolution

      if (!isLoggedIn && !isPublic) return AppRoutes.login;
      if (isLoggedIn && (state.matchedLocation == AppRoutes.login || state.matchedLocation == AppRoutes.register)) {
        return AppRoutes.home;
      }
      return null;
    },
    routes: [
      GoRoute(path: AppRoutes.splash, builder: (context, state) => const SplashScreen()),
      GoRoute(path: AppRoutes.onboarding, builder: (context, state) => const OnboardingScreen()),
      GoRoute(path: AppRoutes.login, builder: (context, state) => const LoginScreen()),
      GoRoute(path: AppRoutes.register, builder: (context, state) => const RegisterScreen()),
      GoRoute(path: AppRoutes.forgotPassword, builder: (context, state) => const ForgotPasswordScreen()),
      GoRoute(
        path: AppRoutes.resetPassword,
        builder: (context, state) => ResetPasswordScreen(initialEmail: state.uri.queryParameters['email']),
      ),
      StatefulShellRoute.indexedStack(
        builder: (context, state, shell) => AppShell(navigationShell: shell),
        branches: [
          StatefulShellBranch(routes: [
            GoRoute(path: AppRoutes.home, builder: (context, state) => const HomeScreen()),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: AppRoutes.occasions, builder: (context, state) => const OccasionListScreen()),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: AppRoutes.notifications, builder: (context, state) => const NotificationsScreen()),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(
              path: AppRoutes.profile,
              builder: (context, state) => const ProfileScreen(),
              routes: [
                GoRoute(path: 'sessions', builder: (context, state) => const SessionsScreen()),
                GoRoute(path: 'preferences', builder: (context, state) => const NotificationPreferencesScreen()),
              ],
            ),
          ]),
        ],
      ),
      GoRoute(path: AppRoutes.occasionCreate, builder: (context, state) => const OccasionCreateScreen()),
      GoRoute(
        path: '/occasions/:id',
        builder: (context, state) => OccasionDetailScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/edit',
        builder: (context, state) {
          final id = state.pathParameters['id']!;
          return Consumer(
            builder: (context, ref, _) {
              final occasionAsync = ref.watch(occasionDetailProvider(id));
              return occasionAsync.when(
                data: (occasion) => OccasionEditScreen(occasion: occasion),
                loading: () => const Scaffold(body: Center(child: CircularProgressIndicator())),
                error: (_, __) => Scaffold(
                  appBar: AppBar(),
                  body: const Center(
                    child: EmptyState(icon: Icons.error_outline, title: 'Couldn\'t load', message: ''),
                  ),
                ),
              );
            },
          );
        },
      ),
      GoRoute(
        path: '/occasions/:id/planning/tasks',
        builder: (context, state) => TaskListScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/planning/tasks/new',
        builder: (context, state) => TaskCreateScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/planning/milestones',
        builder: (context, state) => MilestoneListScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/planning/milestones/new',
        builder: (context, state) => MilestoneCreateScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/planning/timeline',
        builder: (context, state) => TimelineScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/planning/timeline/new',
        builder: (context, state) => TimelineEventCreateScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/planning/checklists',
        builder: (context, state) => ChecklistListScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/planning/checklists/new',
        builder: (context, state) => ChecklistCreateScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/finance/budget/setup',
        builder: (context, state) => BudgetSetupScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/finance/contributions',
        builder: (context, state) => ContributionListScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/finance/contributions/new',
        builder: (context, state) => ContributionCreateScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/finance/expenses',
        builder: (context, state) => ExpenseListScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/finance/expenses/new',
        builder: (context, state) => ExpenseCreateScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/finance/pledges',
        builder: (context, state) => PledgeListScreen(occasionId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: '/occasions/:id/finance/pledges/new',
        builder: (context, state) => PledgeCreateScreen(occasionId: state.pathParameters['id']!),
      ),
    ],
  );
});
