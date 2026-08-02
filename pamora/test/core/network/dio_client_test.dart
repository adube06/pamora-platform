import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:pamora/core/network/dio_client.dart';

void main() {
  RequestOptions options() => RequestOptions(path: '/test');

  group('mapDioError', () {
    test('extracts message and field errors from the API error envelope', () {
      final response = Response(
        requestOptions: options(),
        statusCode: 422,
        data: {
          'success': false,
          'error': {
            'code': 'VALIDATION_ERROR',
            'message': 'Validation failed.',
            'fields': {
              'email': ['The email field is required.'],
            },
          },
        },
      );
      final exception = mapDioError(DioException(requestOptions: options(), response: response));

      expect(exception.message, 'Validation failed.');
      expect(exception.isValidationError, isTrue);
      expect(exception.errorFor('email'), 'The email field is required.');
    });

    test('extracts a message with no field errors for a non-validation error', () {
      final response = Response(
        requestOptions: options(),
        statusCode: 403,
        data: {
          'success': false,
          'error': {'code': 'FORBIDDEN', 'message': 'This action is unauthorized.'},
        },
      );
      final exception = mapDioError(DioException(requestOptions: options(), response: response));

      expect(exception.message, 'This action is unauthorized.');
      expect(exception.isValidationError, isFalse);
    });

    test('falls back to a generic message when the response has no error envelope', () {
      final response = Response(requestOptions: options(), statusCode: 500, data: {'unexpected': true});
      final exception = mapDioError(DioException(requestOptions: options(), response: response));

      expect(exception.message, 'Something went wrong. Please try again.');
      expect(exception.isValidationError, isFalse);
    });

    test('reports a connection-specific message when there is no response at all', () {
      final exception = mapDioError(
        DioException(requestOptions: options(), type: DioExceptionType.connectionError),
      );

      expect(exception.message, contains('reach Pamora'));
    });
  });
}
