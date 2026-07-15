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
    responsibilities: string[];
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
    responsibilities: string[];
    expires_at: string;
}

export interface Task {
    id: number;
    uuid: string;
    title: string;
    description: string | null;
    status: string;
    priority: string;
    due_date: string | null;
    assignee_id: number | null;
    assignee?: OccasionMember | null;
}
