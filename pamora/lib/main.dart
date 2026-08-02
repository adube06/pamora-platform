import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core/router/app_router.dart';
import 'core/theme/theme.dart';

void main() {
  runApp(const ProviderScope(child: PamoraApp()));
}

class PamoraApp extends ConsumerWidget {
  const PamoraApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: 'Pamora',
      debugShowCheckedModeBanner: false,
      theme: PamoraTheme.dark,
      darkTheme: PamoraTheme.dark,
      themeMode: ThemeMode.dark,
      routerConfig: router,
    );
  }
}
