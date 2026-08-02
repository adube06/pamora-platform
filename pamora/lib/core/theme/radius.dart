import 'package:flutter/widgets.dart';

/// Pamora Design System v1 radius tokens.
class PamoraRadius {
  PamoraRadius._();

  static const double small = 8;
  static const double medium = 12;
  static const double large = 16;
  static const double xlarge = 24;
  static const double pill = 999;

  static const BorderRadius smallRadius = BorderRadius.all(Radius.circular(small));
  static const BorderRadius mediumRadius = BorderRadius.all(Radius.circular(medium));
  static const BorderRadius largeRadius = BorderRadius.all(Radius.circular(large));
  static const BorderRadius xlargeRadius = BorderRadius.all(Radius.circular(xlarge));
  static const BorderRadius pillRadius = BorderRadius.all(Radius.circular(pill));
}
