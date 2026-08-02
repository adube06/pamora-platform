import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import '../models/checklist.dart';
import '../models/milestone.dart';
import '../models/task.dart';
import '../models/timeline_event.dart';

final planningRepositoryProvider = Provider<PlanningRepository>((ref) {
  return PlanningRepository(ref.read(dioProvider));
});

class PlanningRepository {
  PlanningRepository(this._dio);

  final Dio _dio;

  String _date(DateTime d) => d.toIso8601String().split('T').first;

  // Tasks
  Future<List<Task>> tasks(String occasionId) {
    return guarded(() async {
      final response = await _dio.get('/occasions/$occasionId/tasks');
      return (unwrapData(response) as List).map((e) => Task.fromJson(e as Map<String, dynamic>)).toList();
    });
  }

  Future<Task> createTask(
    String occasionId, {
    required String title,
    String? description,
    TaskPriority? priority,
    DateTime? dueDate,
  }) {
    return guarded(() async {
      final response = await _dio.post('/occasions/$occasionId/tasks', data: {
        'title': title,
        if (description != null && description.isNotEmpty) 'description': description,
        if (priority != null) 'priority': priority.value,
        if (dueDate != null) 'due_date': _date(dueDate),
      });
      return Task.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  Future<Task> completeTask(String taskId) {
    return guarded(() async {
      final response = await _dio.post('/tasks/$taskId/complete');
      return Task.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  Future<Task> reopenTask(String taskId) {
    return guarded(() async {
      final response = await _dio.post('/tasks/$taskId/reopen');
      return Task.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  // Checklists (create-only per backend)
  Future<List<Checklist>> checklists(String occasionId) {
    return guarded(() async {
      final response = await _dio.get('/occasions/$occasionId/checklists');
      return (unwrapData(response) as List).map((e) => Checklist.fromJson(e as Map<String, dynamic>)).toList();
    });
  }

  Future<Checklist> createChecklist(String occasionId, {required String name}) {
    return guarded(() async {
      final response = await _dio.post('/occasions/$occasionId/checklists', data: {'name': name});
      return Checklist.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  // Milestones
  Future<List<Milestone>> milestones(String occasionId) {
    return guarded(() async {
      final response = await _dio.get('/occasions/$occasionId/milestones');
      return (unwrapData(response) as List).map((e) => Milestone.fromJson(e as Map<String, dynamic>)).toList();
    });
  }

  Future<Milestone> createMilestone(String occasionId, {required String name}) {
    return guarded(() async {
      final response = await _dio.post('/occasions/$occasionId/milestones', data: {'name': name});
      return Milestone.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  // Timeline events
  Future<List<TimelineEvent>> timelineEvents(String occasionId) {
    return guarded(() async {
      final response = await _dio.get('/occasions/$occasionId/timeline-events');
      return (unwrapData(response) as List).map((e) => TimelineEvent.fromJson(e as Map<String, dynamic>)).toList();
    });
  }

  Future<TimelineEvent> createTimelineEvent(String occasionId, {required String name, DateTime? scheduledAt}) {
    return guarded(() async {
      final response = await _dio.post('/occasions/$occasionId/timeline-events', data: {
        'name': name,
        if (scheduledAt != null) 'scheduled_at': scheduledAt.toIso8601String(),
      });
      return TimelineEvent.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }
}
