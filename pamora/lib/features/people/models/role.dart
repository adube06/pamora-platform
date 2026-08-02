enum Role {
  host('host', 'Host'),
  chairperson('chairperson', 'Chairperson'),
  treasurer('treasurer', 'Treasurer'),
  secretary('secretary', 'Secretary'),
  coordinator('coordinator', 'Coordinator'),
  member('member', 'Member'),
  observer('observer', 'Observer'),
  guest('guest', 'Guest');

  const Role(this.value, this.label);
  final String value;
  final String label;

  static Role fromValue(String value) => Role.values.firstWhere((e) => e.value == value, orElse: () => Role.guest);

  /// Host is assigned automatically on Occasion creation — never an
  /// invitable Role (matches InviteMemberRequest's own exclusion).
  static List<Role> get invitable => values.where((r) => r != Role.host).toList();
}
