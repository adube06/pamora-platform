import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

import '../theme/colors.dart';
import '../theme/radius.dart';
import '../theme/spacing.dart';

/// A shimmering placeholder block, used instead of a bare spinner while a
/// list loads (Design System's "Skeleton Loader" component).
class SkeletonBox extends StatelessWidget {
  const SkeletonBox({super.key, this.width, this.height = 16, this.borderRadius});

  final double? width;
  final double height;
  final BorderRadius? borderRadius;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        color: PamoraColors.darkSurfaceRaised,
        borderRadius: borderRadius ?? PamoraRadius.smallRadius,
      ),
    );
  }
}

/// Wraps a column of [SkeletonBox]-shaped cards in a shimmer effect,
/// standing in for a loading list.
class SkeletonList extends StatelessWidget {
  const SkeletonList({super.key, this.itemCount = 3, this.itemHeight = 88});

  final int itemCount;
  final double itemHeight;

  @override
  Widget build(BuildContext context) {
    final reduceMotion = MediaQuery.disableAnimationsOf(context);

    final content = Column(
      children: List.generate(
        itemCount,
        (i) => Padding(
          padding: EdgeInsets.only(bottom: i == itemCount - 1 ? 0 : PamoraSpacing.md),
          child: SkeletonBox(width: double.infinity, height: itemHeight, borderRadius: PamoraRadius.largeRadius),
        ),
      ),
    );

    if (reduceMotion) return content;

    return Shimmer.fromColors(
      baseColor: PamoraColors.darkSurfaceRaised,
      highlightColor: PamoraColors.darkSurface,
      child: content,
    );
  }
}
