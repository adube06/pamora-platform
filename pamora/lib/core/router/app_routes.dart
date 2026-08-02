/// Centralized route paths so screens and the router agree on names.
class AppRoutes {
  AppRoutes._();

  static const splash = '/splash';
  static const onboarding = '/onboarding';
  static const login = '/login';
  static const register = '/register';
  static const forgotPassword = '/forgot-password';
  static const resetPassword = '/reset-password';

  static const home = '/home';
  static const occasions = '/occasions';
  static const occasionCreate = '/occasions/new';
  static const notifications = '/notifications';
  static const profile = '/profile';
  static const profileSessions = '/profile/sessions';
  static const profilePreferences = '/profile/preferences';

  static String occasionDetail(String id) => '/occasions/$id';
  static String occasionEdit(String id) => '/occasions/$id/edit';

  static String planningTasks(String occasionId) => '/occasions/$occasionId/planning/tasks';
  static String taskCreate(String occasionId) => '/occasions/$occasionId/planning/tasks/new';
  static String planningMilestones(String occasionId) => '/occasions/$occasionId/planning/milestones';
  static String milestoneCreate(String occasionId) => '/occasions/$occasionId/planning/milestones/new';
  static String planningTimeline(String occasionId) => '/occasions/$occasionId/planning/timeline';
  static String timelineEventCreate(String occasionId) => '/occasions/$occasionId/planning/timeline/new';
  static String planningChecklists(String occasionId) => '/occasions/$occasionId/planning/checklists';
  static String checklistCreate(String occasionId) => '/occasions/$occasionId/planning/checklists/new';

  static String financeBudgetSetup(String occasionId) => '/occasions/$occasionId/finance/budget/setup';
  static String financeContributions(String occasionId) => '/occasions/$occasionId/finance/contributions';
  static String contributionCreate(String occasionId) => '/occasions/$occasionId/finance/contributions/new';
  static String financeExpenses(String occasionId) => '/occasions/$occasionId/finance/expenses';
  static String expenseCreate(String occasionId) => '/occasions/$occasionId/finance/expenses/new';
  static String financePledges(String occasionId) => '/occasions/$occasionId/finance/pledges';
  static String pledgeCreate(String occasionId) => '/occasions/$occasionId/finance/pledges/new';

  static String members(String occasionId) => '/occasions/$occasionId/guests/members';
  static String inviteMember(String occasionId) => '/occasions/$occasionId/guests/invite';
  static String rsvp(String occasionId) => '/occasions/$occasionId/guests/rsvp';
}
