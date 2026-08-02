class Session {
  const Session({
    required this.id,
    required this.isCurrentDevice,
    this.ipAddress,
    this.userAgent,
    this.lastActiveAt,
  });

  final String id;
  final bool isCurrentDevice;
  final String? ipAddress;
  final String? userAgent;
  final DateTime? lastActiveAt;

  factory Session.fromJson(Map<String, dynamic> json) {
    return Session(
      id: json['id'].toString(),
      isCurrentDevice: json['is_current_device'] == true,
      ipAddress: json['ip_address'] as String?,
      userAgent: json['user_agent'] as String?,
      lastActiveAt: json['last_active_at'] != null ? DateTime.tryParse(json['last_active_at'] as String) : null,
    );
  }
}
