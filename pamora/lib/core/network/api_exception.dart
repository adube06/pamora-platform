/// A typed exception carrying a user-facing message plus any per-field
/// validation errors from a Laravel 422 response, so forms can map them
/// directly onto their fields instead of showing a raw error dump.
class ApiException implements Exception {
  ApiException({
    required this.message,
    this.statusCode,
    this.fieldErrors = const {},
  });

  final String message;
  final int? statusCode;
  final Map<String, List<String>> fieldErrors;

  bool get isValidationError => fieldErrors.isNotEmpty;

  String? errorFor(String field) => fieldErrors[field]?.first;

  @override
  String toString() => message;
}
