enum Responsibility {
  chairperson('chairperson', 'Chairperson'),
  treasurer('treasurer', 'Treasurer'),
  secretary('secretary', 'Secretary'),
  cateringLead('catering_lead', 'Catering Lead'),
  logisticsLead('logistics_lead', 'Logistics Lead'),
  decorationLead('decoration_lead', 'Decoration Lead'),
  transportCoordinator('transport_coordinator', 'Transport Coordinator');

  const Responsibility(this.value, this.label);
  final String value;
  final String label;

  static Responsibility? fromValue(String value) {
    for (final r in Responsibility.values) {
      if (r.value == value) return r;
    }
    return null;
  }
}
