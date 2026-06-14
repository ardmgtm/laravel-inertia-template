import { useAuthStore } from "@/Stores/auth-store";
import { getActivePinia } from 'pinia';

export function can(permissions: string | string[] | undefined): boolean {
    if (getActivePinia()) {
        const authStore = useAuthStore();

        if (typeof permissions === 'string') {
            return authStore.hasPermissions([permissions]);
        } else if (Array.isArray(permissions)) {
            return authStore.hasPermissions(permissions);
        }
        return true;
    }
    return false;
}