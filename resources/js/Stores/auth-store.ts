import { User } from "@/Core/Models/user";
import { Permission, UserRole } from "@/Core/Models/user-role";
import { defineStore } from "pinia";

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: <User | null>null,
        roles: <UserRole[]>[],
        permissions: <Permission[]>[],
    }),
    persist: true,
    actions: {
        setUser(user: User) {
            this.user = user;
        },
        setRoles(roles: UserRole[]) {
            this.roles = roles;
        },
        setPermissions(permissions: Permission[]) {
            this.permissions = permissions;
        },
        hasPermission(permission: string): boolean {
            return this.permissions.some(p => p.name === permission);
        },
        hasPermissions(permissions: string[]): boolean {
            return permissions.every(permission => this.permissions.some(p => p.name === permission));
        },
    }
});