import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/app_routes.dart';
import '../../../core/theme/colors.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../auth/application/auth_provider.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  Future<void> _confirmLogout(BuildContext context, WidgetRef ref) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Log out?'),
        content: const Text('You\'ll need to log in again to access your occasions.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text('Log Out', style: TextStyle(color: Theme.of(context).colorScheme.error)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    await ref.read(authProvider.notifier).logout();
    if (context.mounted) context.go(AppRoutes.login);
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider).value;
    final textTheme = Theme.of(context).textTheme;
    final initials = (user?.name.isNotEmpty == true ? user!.name.trim()[0] : '?').toUpperCase();

    return Scaffold(
      appBar: AppBar(title: const Text('Profile')),
      body: ListView(
        padding: const EdgeInsets.all(PamoraSpacing.md),
        children: [
          Center(
            child: Column(
              children: [
                Container(
                  width: 76,
                  height: 76,
                  decoration: const BoxDecoration(shape: BoxShape.circle, gradient: PamoraColors.heroGradient),
                  alignment: Alignment.center,
                  child: Text(
                    initials,
                    style: textTheme.headlineMedium?.copyWith(color: Colors.white),
                  ),
                ),
                const SizedBox(height: PamoraSpacing.md),
                Text(user?.name ?? '', style: textTheme.titleLarge),
                const SizedBox(height: 2),
                Text(
                  user?.email ?? '',
                  style: textTheme.bodyMedium?.copyWith(color: Theme.of(context).colorScheme.onSurfaceVariant),
                ),
              ],
            ),
          ),
          const SizedBox(height: PamoraSpacing.xl),
          PamoraCard(
            padding: EdgeInsets.zero,
            child: Column(
              children: [
                _ProfileTile(
                  icon: Icons.notifications_outlined,
                  label: 'Notification Preferences',
                  onTap: () => context.push(AppRoutes.profilePreferences),
                ),
                const Divider(height: 1),
                _ProfileTile(
                  icon: Icons.devices_outlined,
                  label: 'Active Sessions',
                  onTap: () => context.push(AppRoutes.profileSessions),
                ),
              ],
            ),
          ),
          const SizedBox(height: PamoraSpacing.xl),
          PamoraCard(
            padding: EdgeInsets.zero,
            child: _ProfileTile(
              icon: Icons.logout,
              label: 'Log Out',
              destructive: true,
              onTap: () => _confirmLogout(context, ref),
            ),
          ),
        ],
      ),
    );
  }
}

class _ProfileTile extends StatelessWidget {
  const _ProfileTile({required this.icon, required this.label, required this.onTap, this.destructive = false});

  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final bool destructive;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final color = destructive ? scheme.error : scheme.onSurface;

    return ListTile(
      leading: Icon(icon, color: color),
      title: Text(label, style: TextStyle(color: color)),
      trailing: destructive ? null : const Icon(Icons.chevron_right, size: 20),
      onTap: onTap,
    );
  }
}
