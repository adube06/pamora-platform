import 'package:intl/intl.dart';

final _dateFormat = DateFormat('MMM d, yyyy');
final _dateTimeFormat = DateFormat('MMM d, yyyy • h:mm a');

String formatDate(DateTime? date) => date == null ? '—' : _dateFormat.format(date);

String formatDateTime(DateTime? date) => date == null ? '—' : _dateTimeFormat.format(date);

String formatCurrency(double amount, String currency) {
  final symbol = currency.isEmpty ? '' : '$currency ';
  return '$symbol${NumberFormat('#,##0.00').format(amount)}';
}

String formatRelative(DateTime date) {
  final diff = DateTime.now().difference(date);
  if (diff.inMinutes < 1) return 'just now';
  if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
  if (diff.inHours < 24) return '${diff.inHours}h ago';
  if (diff.inDays < 7) return '${diff.inDays}d ago';
  return formatDate(date);
}
