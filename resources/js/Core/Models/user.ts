export interface User {
    id: number;
    name: string;
    username: string;
    email: string;
}

export interface UserForm {
    id: number | null;
    name: string | null;
    username: string | null;
    email: string | null;
    password: string | null;
    [key: string]: any;
}