export interface Service {
    id: number;
    uuid: string;
    vendor_id: number;
    category: string;
    name: string;
    description: string | null;
    pricing_model: string;
    price: string | null;
    currency: string;
    estimated_duration: string | null;
    status: string;
    vendor?: { id: number; business_name: string };
    quotations?: Quotation[];
    bookings?: Booking[];
}

export interface Quotation {
    id: number;
    uuid: string;
    occasion_id: number;
    service_id: number;
    requested_by: number;
    message: string | null;
    status: string;
    quoted_price: string | null;
    currency: string;
    vendor_notes: string | null;
    requested_at: string;
    responded_at: string | null;
    service?: { id: number; uuid: string; name: string };
}

export interface Review {
    id: number;
    uuid: string;
    rating: number;
    comment: string | null;
    published_at: string;
}

export interface Booking {
    id: number;
    uuid: string;
    occasion_id: number;
    service_id: number;
    quotation_id: number;
    confirmed_by: number;
    status: string;
    agreed_price: string;
    currency: string;
    notes: string | null;
    confirmed_at: string;
    service?: { id: number; uuid: string; name: string };
    review?: Review | null;
}

export interface Vendor {
    id: number;
    uuid: string;
    business_name: string;
    categories: string[];
    service_areas: string[] | null;
    contact_email: string;
    contact_phone: string;
    verification_status: string;
    status: string;
    services: Service[];
}

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
    responsibilities: string[];
    rsvp_status: string | null;
    rsvp_responded_at: string | null;
    guest_count: number | null;
    rsvp_message: string | null;
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

export interface ResponsibilityOption {
    value: string;
    label: string;
}

export interface TaskDependency {
    id: number;
    uuid: string;
    title: string;
    status: string;
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
    dependencies: TaskDependency[];
    is_blocked: boolean;
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
    album: { id: number; name: string } | null;
    task: { id: number; title: string } | null;
    expense: { id: number; description: string } | null;
    announcement: { id: number; title: string } | null;
    created_at: string;
}

export interface Album {
    id: number;
    uuid: string;
    name: string;
    media_assets_count: number;
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

export interface BudgetItem {
    id: number;
    uuid: string;
    name: string;
    estimated_cost: string;
    currency: string;
    budget_category_id: number;
}

export interface BudgetCategory {
    id: number;
    uuid: string;
    name: string;
    budget_items?: BudgetItem[];
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

export interface Pledge {
    id: number;
    uuid: string;
    pledgor_name: string;
    pledgor_phone: string | null;
    amount: string;
    currency: string;
    status: string;
    message: string | null;
    pledged_at: string;
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
    total_pledged?: string;
    pending_pledged?: string;
    planned_amount?: string | null;
    total_expense?: string;
    remaining_budget?: string | null;
    funding_progress?: number | null;
    spending_progress?: number | null;
    health?: string | null;
}

export interface TaskProgress {
    total: number;
    draft: number;
    open: number;
    in_progress: number;
    completed: number;
    deferred: number;
    completion_percentage: number | null;
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

export interface TaskOwnership {
    member_name: string;
    task_count: number;
}

export interface Participation {
    invitation_acceptance_rate: number | null;
    total_invitations: number;
    rsvp_completion_rate: number | null;
    active_member_count: number;
    task_ownership: TaskOwnership[];
}

export interface Recommendation {
    message: string;
    reason: string;
    severity: string;
}
