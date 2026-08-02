enum ContributionMethod {
  cash('cash', 'Cash'),
  mobileMoney('mobile_money', 'Mobile Money'),
  bankTransfer('bank_transfer', 'Bank Transfer'),
  other('other', 'Other');

  const ContributionMethod(this.value, this.label);
  final String value;
  final String label;

  static ContributionMethod fromValue(String value) =>
      ContributionMethod.values.firstWhere((e) => e.value == value, orElse: () => ContributionMethod.other);
}

class Contribution {
  const Contribution({
    required this.id,
    required this.contributorName,
    required this.amount,
    required this.currency,
    required this.method,
    required this.contributedAt,
    this.contributorPhone,
    this.message,
  });

  final String id;
  final String contributorName;
  final double amount;
  final String currency;
  final ContributionMethod method;
  final DateTime contributedAt;
  final String? contributorPhone;
  final String? message;

  factory Contribution.fromJson(Map<String, dynamic> json) {
    return Contribution(
      id: json['id'].toString(),
      contributorName: json['contributor_name'] as String? ?? '',
      amount: double.tryParse(json['amount'].toString()) ?? 0,
      currency: json['currency'] as String? ?? '',
      method: ContributionMethod.fromValue(json['method'] as String? ?? 'other'),
      contributedAt: DateTime.tryParse(json['contributed_at'] as String? ?? '') ?? DateTime.now(),
      contributorPhone: json['contributor_phone'] as String?,
      message: json['message'] as String?,
    );
  }
}

class ContributionSummary {
  const ContributionSummary({required this.totalReceived, required this.count});

  final double totalReceived;
  final int count;

  factory ContributionSummary.fromJson(Map<String, dynamic>? json) {
    if (json == null) return const ContributionSummary(totalReceived: 0, count: 0);
    return ContributionSummary(
      totalReceived: double.tryParse(json['total_received'].toString()) ?? 0,
      count: json['contribution_count'] as int? ?? 0,
    );
  }
}
