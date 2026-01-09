export interface AuthInterface {
    user: UserInterface | null;
    plan?: PlanInterface | null;
    activeRole?: RoleType;
}

export interface UserInterface {
    id: number;
    name: string;
    email: string;
    identity_card: string;
    role: RoleType[];
    photoURL?: string | null;
}

export interface PlanInterface {
    id: number;
    name: string;
    description: string;
}

export type RoleType = 'admin' | 'nutritionist' | 'client';

