enum PledgeStatus {
  pending('pending', 'Pending'),
  confirmed('confirmed', 'Confirmed'),
  cancelled('cancelled', 'Cancelled'),
  expired('expired', 'Expired');

  const PledgeStatus(this.value, this.label);
  final String value;
  final String label;

  static PledgeStatus fromValue(String value) =>
      PledgeStatus.values.firstWhere((e) => e.value == value, orElse: () => PledgeStatus.pending);
}

class Pledge {
  const Pledge({
    required this.id,
    required this.pledgorName,
    required this.amount,
    required this.currency,
    required this.status,
    required this.pledgedAt,
    this.pledgorPhone,
    this.message,
  });

  final String id;
  final String pledgorName;
  final double amount;
  final String currency;
  final PledgeStatus status;
  final DateTime pledgedAt;
  final String? pledgorPhone;
  final String? message;

  factory Pledge.fromJson(Map<String, dynamic> json) {
    return Pledge(
      id: json['id'].toString(),
      pledgorName: json['pledgor_name'] as String? ?? '',
      amount: double.tryParse(json['amount'].toString()) ?? 0,
      currency: json['currency'] as String? ?? '',
      status: PledgeStatus.fromValue(json['status'] as String? ?? 'pending'),
      pledgedAt: DateTime.tryParse(json['pledged_at'] as String? ?? '') ?? DateTime.now(),
      pledgorPhone: json['pledgor_phone'] as String?,
      message: json['message'] as String?,
    );
  }
}
