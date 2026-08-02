import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/app_routes.dart';
import '../../../core/storage/onboarding_storage.dart';
import '../../../core/theme/colors.dart';
import '../../../core/theme/spacing.dart';
import '../application/auth_provider.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _resolve());
  }

  Future<void> _resolve() async {
    final user = await ref.read(authProvider.future);
    if (!mounted) return;

    if (user != null) {
      context.go(AppRoutes.home);
      return;
    }

    final seenOnboarding = await ref.read(onboardingStorageProvider).hasSeenOnboarding();
    if (!mounted) return;
    context.go(seenOnboarding ? AppRoutes.login : AppRoutes.onboarding);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: DecoratedBox(
        decoration: const BoxDecoration(color: PamoraColors.darkBackground),
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ShaderMask(
                shaderCallback: (bounds) => PamoraColors.heroGradient.createShader(bounds),
                child: Text(
                  'Pamora',
                  style: Theme.of(context).textTheme.displayLarge?.copyWith(color: Colors.white),
                ),
              ),
              const SizedBox(height: PamoraSpacing.md),
              const SizedBox(
                width: 22,
                height: 22,
                child: CircularProgressIndicator(strokeWidth: 2.4),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
