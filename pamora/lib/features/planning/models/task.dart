enum TaskStatus {
  draft('draft', 'Draft'),
  open('open', 'Open'),
  inProgress('in_progress', 'In Progress'),
  completed('completed', 'Completed'),
  cancelled('cancelled', 'Cancelled'),
  deferred('deferred', 'Deferred');

  const TaskStatus(this.value, this.label);
  final String value;
  final String label;

  static TaskStatus fromValue(String value) =>
      TaskStatus.values.firstWhere((e) => e.value == value, orElse: () => TaskStatus.open);
}

enum TaskPriority {
  critical('critical', 'Critical'),
  high('high', 'High'),
  medium('medium', 'Medium'),
  low('low', 'Low');

  const TaskPriority(this.value, this.label);
  final String value;
  final String label;

  static TaskPriority fromValue(String value) =>
      TaskPriority.values.firstWhere((e) => e.value == value, orElse: () => TaskPriority.medium);
}

class Task {
  const Task({
    required this.id,
    required this.title,
    required this.status,
    required this.priority,
    this.description,
    this.dueDate,
    this.completedAt,
    this.isBlocked = false,
  });

  final String id;
  final String title;
  final TaskStatus status;
  final TaskPriority priority;
  final String? description;
  final DateTime? dueDate;
  final DateTime? completedAt;
  final bool isBlocked;

  factory Task.fromJson(Map<String, dynamic> json) {
    return Task(
      id: json['id'].toString(),
      title: json['title'] as String? ?? '',
      status: TaskStatus.fromValue(json['status'] as String? ?? 'open'),
      priority: TaskPriority.fromValue(json['priority'] as String? ?? 'medium'),
      description: json['description'] as String?,
      dueDate: json['due_date'] != null ? DateTime.tryParse(json['due_date'] as String) : null,
      completedAt: json['completed_at'] != null ? DateTime.tryParse(json['completed_at'] as String) : null,
      isBlocked: json['is_blocked'] == true,
    );
  }
}
