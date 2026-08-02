enum OccasionType {
  wedding('wedding', 'Wedding'),
  funeral('funeral', 'Funeral'),
  birthday('birthday', 'Birthday');

  const OccasionType(this.value, this.label);
  final String value;
  final String label;

  static OccasionType fromValue(String value) =>
      OccasionType.values.firstWhere((e) => e.value == value, orElse: () => OccasionType.wedding);
}

enum OccasionVisibility {
  private_('private', 'Private'),
  invitationOnly('invitation_only', 'Invitation Only'),
  public_('public', 'Public');

  const OccasionVisibility(this.value, this.label);
  final String value;
  final String label;

  static OccasionVisibility fromValue(String value) =>
      OccasionVisibility.values.firstWhere((e) => e.value == value, orElse: () => OccasionVisibility.private_);
}

enum OccasionStatus {
  draft('draft', 'Draft'),
  planning('planning', 'Planning'),
  active('active', 'Active'),
  completed('completed', 'Completed'),
  archived('archived', 'Archived'),
  cancelled('cancelled', 'Cancelled');

  const OccasionStatus(this.value, this.label);
  final String value;
  final String label;

  static OccasionStatus fromValue(String value) =>
      OccasionStatus.values.firstWhere((e) => e.value == value, orElse: () => OccasionStatus.draft);
}

class Occasion {
  const Occasion({
    required this.id,
    required this.slug,
    required this.title,
    required this.type,
    required this.visibility,
    required this.status,
    required this.createdAt,
    this.description,
    this.primaryDate,
    this.timezone,
    this.location,
  });

  final String id;
  final String slug;
  final String title;
  final OccasionType type;
  final OccasionVisibility visibility;
  final OccasionStatus status;
  final DateTime createdAt;
  final String? description;
  final DateTime? primaryDate;
  final String? timezone;
  final String? location;

  factory Occasion.fromJson(Map<String, dynamic> json) {
    return Occasion(
      id: json['id'].toString(),
      slug: json['slug'] as String? ?? '',
      title: json['title'] as String? ?? '',
      type: OccasionType.fromValue(json['type'] as String? ?? 'wedding'),
      visibility: OccasionVisibility.fromValue(json['visibility'] as String? ?? 'private'),
      status: OccasionStatus.fromValue(json['status'] as String? ?? 'draft'),
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ?? DateTime.now(),
      description: json['description'] as String?,
      primaryDate: json['primary_date'] != null ? DateTime.tryParse(json['primary_date'] as String) : null,
      timezone: json['timezone'] as String?,
      location: json['location'] as String?,
    );
  }
}
