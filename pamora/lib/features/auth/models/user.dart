class User {
  const User({
    required this.id,
    required this.name,
    required this.email,
    this.isAdmin = false,
    this.emailVerifiedAt,
    this.notificationPreferences = const {},
  });

  final String id;
  final String name;
  final String email;
  final bool isAdmin;
  final DateTime? emailVerifiedAt;
  final Map<String, dynamic> notificationPreferences;

  bool get isEmailVerified => emailVerifiedAt != null;

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'].toString(),
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      isAdmin: json['is_admin'] == true,
      emailVerifiedAt:
          json['email_verified_at'] != null ? DateTime.tryParse(json['email_verified_at'] as String) : null,
      notificationPreferences: (json['notification_preferences'] as Map?)?.cast<String, dynamic>() ?? {},
    );
  }
}
