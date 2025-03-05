import { UserRole } from "./user-role";

export interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    profile_picture: string;
    roles?: UserRole[];
}

export interface UserForm {
    id?: number | null;
    name?: string | null;
    username?: string | null;
    email?: string | null;
    password?: string | null;
    profile_picture?: string | File | null;
    role?: number | null;
    [key: string]: any;
}