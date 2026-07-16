export interface Occasion {
    id: number;
    uuid: string;
    slug: string;
    title: string;
    type: string;
    description: string | null;
    primary_date: string | null;
    timezone: string;
    location: string | null;
    visibility: string;
    status: string;
}

export interface OccasionMember {
    id: number;
    uuid: string;
    occasion_id: number;
    user_id: number;
    status: string;
    role: string;
    notes: string | null;
    permissions: string[];
    user?: {
        id: number;
        name: string;
        email: string;
    };
}

export interface Invitation {
    id: number;
    uuid: string;
    email: string;
    status: string;
    role: string;
    notes: string | null;
    expires_at: string;
}

export interface RoleOption {
    value: string;
    label: string;
}

export interface Task {
    id: number;
    uuid: string;
    title: string;
    description: string | null;
    status: string;
    priority: string;
    due_date: string | null;
    completed_at: string | null;
    checklist_id: number | null;
    assignee_id: number | null;
    assignee?: OccasionMember | null;
}

export interface Checklist {
    id: number;
    uuid: string;
    name: string;
}

export interface MilestoneTask {
    id: string;
    title: string;
    status: string;
}

export interface Milestone {
    id: string;
    name: string;
    is_achieved: boolean;
    tasks: MilestoneTask[];
}

export interface TimelineEvent {
    id: number;
    uuid: string;
    name: string;
    scheduled_at: string;
}

export interface Notification {
    id: number;
    uuid: string;
    type: string;
    title: string;
    body: string;
    read_at: string | null;
    created_at: string;
}

export interface Announcement {
    id: number;
    uuid: string;
    title: string;
    message: string;
    audience: string;
    status: string;
    published_at: string;
    // The eager-loaded `createdBy` relation is snake-cased on serialization
    // (Eloquent's relationsToArray()), which overwrites the raw created_by
    // FK column in the JSON — so this key holds the loaded {id, name}, not
    // an integer.
    created_by: { id: number; name: string };
}

export interface ReminderRule {
    id: number;
    uuid: string;
    offset_minutes: number;
    triggered_at: string | null;
    timeline_event: {
        id: number;
        uuid: string;
        name: string;
        scheduled_at: string;
    };
    created_at: string;
}

export interface MediaAsset {
    id: string;
    file_name: string;
    file_type: string;
    size: number;
    visibility: string;
    download_url: string;
    uploaded_by: string;
    created_at: string;
}

export interface Contribution {
    id: number;
    uuid: string;
    contributor_name: string;
    contributor_phone: string | null;
    amount: string;
    currency: string;
    method: string;
    message: string | null;
    contributed_at: string;
}

export interface BudgetCategory {
    id: number;
    uuid: string;
    name: string;
}

export interface Budget {
    id: number;
    uuid: string;
    name: string;
    currency: string;
    planned_amount: string;
    status: string;
    categories: BudgetCategory[];
}

export interface Expense {
    id: number;
    uuid: string;
    amount: string;
    currency: string;
    description: string | null;
    spent_at: string;
    budget_category_id: number;
    category?: BudgetCategory;
}

export interface BudgetSummary {
    total_received: string;
    contribution_count: number;
    planned_amount?: string | null;
    total_expense?: string;
    remaining_budget?: string | null;
    funding_progress?: number | null;
    spending_progress?: number | null;
    health?: string | null;
}

export interface ReadinessSignal {
    key: string;
    label: string;
    value: number;
}

export interface Readiness {
    score: number | null;
    signals: ReadinessSignal[];
}
