export interface UserRole {
    id: number;
    name: string;
    permissions?: PermissionGroups;
    [key: string]: any;
}

export interface UserRoleForm {
    id: number | null;
    name: string | null;
    [key: string]: any;
}

export interface Permission {
    id: number;
    name: string;
}

export interface PermissionGroups {
    [key: string]: Array<PermissionItem>;
}

export interface PermissionItem {
    id: number;
    name: string;
    role_has_permission: boolean;
}