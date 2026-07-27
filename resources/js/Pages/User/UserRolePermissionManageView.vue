<template>
    <Head title="Role Manage" />
    <AdminLayout title="Role Manage" :breadcrumbs>
        <div class="flex gap-4">
            <div class="w-80 flex-shrink-0">
                <div class="flex justify-end mb-4 gap-4">
                    <div class="flex-1">
                        <AppInputSearch v-model="search" placeholder="Search roles..." />
                    </div>
                    <div class="flex-none">
                        <Button icon="pi pi-plus" @click="addUserRoleAction" v-if="can('role.create')"
                            v-tooltip.bottom="'Add User Role'" />
                    </div>
                </div>
                <div class="h-[calc(100vh-260px)] overflow-y-auto scroll pr-2">
                    <div v-if="isRolesEmpty" class="h-full w-full flex items-center justify-center">
                        <span class="text-gray-700 italic">
                            No roles found
                        </span>
                    </div>
                    <div v-else class="space-y-2">
                        <div v-ripple 
                            class="p-4 rounded-xl border border-gray-200 w-full cursor-pointer transition-all" 
                            :class="[
                                { 'bg-primary border-primary shadow-sm': role.id == selectedRoleId },
                                { 'bg-white hover:bg-primary-50 hover:border-primary-300': role.id != selectedRoleId },
                            ]" 
                            v-for="role in filteredRoles" 
                            :key="role.id" 
                            @click="selectRole(role)">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold" :class="[
                                    { 'text-white': role.id == selectedRoleId },
                                    { 'text-gray-900': role.id != selectedRoleId },
                                ]">
                                    {{ role.name }}
                                </span>
                                <i v-if="role.id == selectedRoleId" class="pi pi-check text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex-1">
                <div class="border border-gray-200 rounded-2xl h-full bg-white">
                    <div class="h-[calc(100vh-200px)] w-full flex items-center justify-center flex-col" v-if="!isAnyRoleSelected">
                        <i class="pi pi-shield text-6xl text-gray-300 mb-4"></i>
                        <h4 class="text-xl font-bold mb-2 text-gray-900">No Role Selected</h4>
                        <span class="w-64 text-center text-gray-600">
                            Select a role from the list to view and manage permissions and users.
                        </span>
                    </div>
                    <div v-else class="p-6 h-[calc(100vh-200px)]">
                        <Transition name="fade" mode="out-in">
                            <div class="h-full w-full flex items-center justify-center" v-if="isLoading">
                                <ProgressSpinner stroke-width="4" />
                            </div>
                            <div v-else class="flex flex-col h-full">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex flex-col">
                                        <h3 class="text-2xl font-bold text-gray-900">{{ selectedRole?.name }}</h3>
                                    </div>
                                    <div class="flex gap-2">
                                        <Button variant="text" icon="pi pi-ellipsis-v" severity="secondary" rounded
                                            v-tooltip.bottom="'More actions'"
                                            @click="(e: any) => ($refs.roleMenu as any).toggle(e)" 
                                            v-if="can(['role.update','role.delete'])" />
                                        <Popover ref="roleMenu">
                                            <div class="flex flex-col min-w-[200px]">
                                                <Button icon="pi pi-pen-to-square" variant="text" severity="secondary"
                                                    label="Edit Role" class="w-full justify-start"
                                                    @click="editUserRoleAction" v-if="can('role.update')" />
                                                <Button icon="pi pi-trash" variant="text" severity="danger"
                                                    label="Delete Role" class="w-full justify-start"
                                                    @click="deleteUserRoleAction" v-if="can('role.delete')" />
                                            </div>
                                        </Popover>
                                    </div>
                                </div>
                                
                                <Tabs :value="activeTab" @update:value="(val) => activeTab = val as string">
                                    <TabList>
                                        <Tab value="permission">
                                            <div class="flex items-center gap-2">
                                                <i class="pi pi-shield"></i>
                                                <span>Permissions</span>
                                                <Badge :value="totalPermissionGranted" severity="primary" />
                                            </div>
                                        </Tab>
                                        <Tab value="user">
                                            <div class="flex items-center gap-2">
                                                <i class="pi pi-users"></i>
                                                <span>Users</span>
                                                <Badge :value="totalUser" severity="primary" />
                                            </div>
                                        </Tab>
                                    </TabList>
                                    <TabPanels class="pt-4">
                                        <TabPanel value="permission">
                                            <div class="h-[calc(100vh-450px)] overflow-y-auto pr-2">
                                                <div v-if="Object.keys(permissions).length === 0" 
                                                    class="h-full flex items-center justify-center text-gray-500 italic">
                                                    No permissions available
                                                </div>
                                                <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200"
                                                        v-for="(permissionList, permissionGroupName) in permissions" 
                                                        :key="permissionGroupName as string">
                                                        <div class="flex items-center justify-between mb-3">
                                                            <h5 class="text-lg font-semibold text-gray-900">
                                                                {{ parsePermissionName(permissionGroupName as string) }}
                                                            </h5>
                                                            <ToggleSwitch
                                                                :disabled="!can('role.assign_permission') || selectedRole?.id === 1"
                                                                :model-value="permissionList.every((p: any) => p.role_has_permission)"
                                                                @update:model-value="(val: boolean) => toggleAllPermissions(permissionList, val)" />
                                                        </div>
                                                        <Divider class="my-3" />
                                                        <div class="space-y-2">
                                                            <div class="flex justify-between items-center py-2 px-3 rounded hover:bg-white transition-colors"
                                                                v-for="permissionObj in permissionList" 
                                                                :key="permissionObj.id">
                                                                <span class="text-sm text-gray-700">
                                                                    {{ parsePermissionName(permissionObj.name) }}
                                                                </span>
                                                                <ToggleSwitch
                                                                    :disabled="!can('role.assign_permission') || selectedRole?.id === 1"
                                                                    v-model="permissionObj.role_has_permission"
                                                                    @update:model-value="(val: boolean) => onSwitchChange(selectedRole!.id, permissionObj, val)" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </TabPanel>
                                        
                                        <TabPanel value="user">
                                            <div class="h-[calc(100vh-450px)] flex flex-col">
                                                <div class="mb-4">
                                                    <IconField>
                                                        <InputIcon>
                                                            <i class="pi pi-search" />
                                                        </InputIcon>
                                                        <InputText placeholder="Search users..." v-model="searchUser" fluid />
                                                    </IconField>
                                                </div>
                                                
                                                <div class="flex-1 overflow-y-auto">
                                                    <div v-if="userPaginated.length === 0" 
                                                        class="h-full flex items-center justify-center text-gray-500 italic">
                                                        <div class="text-center">
                                                            <i class="pi pi-users text-4xl text-gray-300 mb-2"></i>
                                                            <p>No users found</p>
                                                        </div>
                                                    </div>
                                                    <div v-else class="space-y-2">
                                                        <div v-for="user in userPaginated" :key="user.id"
                                                            class="rounded-lg py-3 px-4 border border-gray-200 flex items-center gap-4 bg-white hover:bg-gray-50 transition-colors">
                                                            <AppProfilePicture :user />
                                                            <div class="flex-1">
                                                                <div class="font-semibold text-gray-900">
                                                                    {{ user.name }}
                                                                </div>
                                                                <div class="text-sm text-gray-600">
                                                                    {{ user.username }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div v-if="filteredUserCount > 10" class="mt-4 flex justify-center">
                                                    <Paginator 
                                                        :rows="10" 
                                                        :totalRecords="filteredUserCount"
                                                        @page="(e: any) => userPage = e.page"
                                                        template="FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink" />
                                                </div>
                                            </div>
                                        </TabPanel>
                                    </TabPanels>
                                </Tabs>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
    <UserRoleFormModal ref="userRoleFormModalRef" @data-deleted="() => router.visit(route('role.browse'))" />
</template>
<script setup lang="ts">
import AppProfilePicture from '@/Components/AppProfilePicture.vue';
import AppInputSearch from '@/Components/AppInputSearch.vue';
import { User } from '@/Core/Models/user';
import { PermissionGroups, PermissionItem, UserRole } from '@/Core/Models/user-role';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { useToast } from 'primevue';
import { MenuItem } from 'primevue/menuitem';
import { computed, ComputedRef, ref, Ref, watch } from 'vue';
import UserRoleFormModal from './Components/UserRoleFormModal.vue';
import { FormModalExpose } from '@/Core/Models/form-modal';
import { can } from '@/Core/Utils/permission-check.js';

const toast = useToast();

const breadcrumbs: Ref<MenuItem[]> = ref([
    {
        label: 'Role Management',
        url: route('role.browse'),
    }
]);

const page = usePage();
const roles: ComputedRef<UserRole[]> = computed(() => page.props.roles as UserRole[]);
const selectedRoleId: ComputedRef<number | null> = computed(() => page.props.selectedRoleId as number | null);
const rolePermissionsData: ComputedRef<any> = computed(() => page.props.rolePermissions as any);
const roleUsersData: ComputedRef<any> = computed(() => page.props.roleUsers as any);

const search: Ref<string> = ref('');

const filteredRoles = computed(() => {
    return roles.value.filter(role => role.name.toLowerCase().includes(search.value.toLowerCase()));
});

const isRolesEmpty = computed(() => filteredRoles.value.length < 1);

// Role selection & data
const activeTab: Ref<string> = ref('permission');
const selectedRole: ComputedRef<UserRole | undefined> = computed(() => 
    roles.value.find(role => role.id === selectedRoleId.value)
);

const permissions: Ref<PermissionGroups> = ref({} as PermissionGroups);
const totalPermissionGranted: Ref<number> = ref(0);

const users: Ref<User[]> = ref([] as User[]);
const totalUser: Ref<number> = ref(0);

const isAnyRoleSelected: ComputedRef<boolean> = computed(() => selectedRoleId.value !== null);
const isLoading: Ref<boolean> = ref(false);

// Watch for role data changes from props
watch(rolePermissionsData, (newData) => {
    if (newData) {
        permissions.value = newData.permissions;
        totalPermissionGranted.value = newData.total_assigned_permission;
    }
}, { immediate: true });

watch(roleUsersData, (newData) => {
    if (newData) {
        users.value = newData.users;
        totalUser.value = newData.user_count;
    }
}, { immediate: true });

// Form modal actions
const userRoleFormModalRef = ref<FormModalExpose<UserRole>>();

const addUserRoleAction = () => userRoleFormModalRef.value?.addAction();

const editUserRoleAction = () => {
    if (selectedRole.value) {
        userRoleFormModalRef.value?.editAction(selectedRole.value);
    }
};

const deleteUserRoleAction = () => {
    if (selectedRole.value) {
        userRoleFormModalRef.value?.deleteAction(selectedRole.value);
    }
};

// Role selection using Inertia
function selectRole(role: UserRole) {
    if (role.id === selectedRoleId.value) return;
    
    isLoading.value = true;
    activeTab.value = 'permission';
    userPage.value = 0;
    
    router.get(
        route('role.browse', { role_id: role.id }),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ['rolePermissions', 'roleUsers', 'selectedRoleId'],
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
}

function parsePermissionName(str: string): string {
    const text = str.split('.').slice(-1)[0];
    const words = text.split('_');
    const result = words.map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    return result;
}

function toggleAllPermissions(permissionList: PermissionItem[], newValue: boolean) {
    // Update UI immediately for better UX
    permissionList.forEach((permission: PermissionItem) => {
        permission.role_has_permission = newValue;
    });

    // Prepare batch data
    const permissions = permissionList.map((permission: PermissionItem) => ({
        id_permission: permission.id,
        value: newValue,
    }));

    // Send single batch request
    router.post(
        route('role.batch_switch_permission', selectedRole.value!.id),
        { permissions },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                // Recalculate total after batch update
                recalculateTotalPermissions();

                const message = (page.props as any).flash?.message;
                if (message) {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: message,
                        life: 3000
                    });
                }
            },
            onError: (errors) => {
                // Revert all permissions on error
                permissionList.forEach((permission: PermissionItem) => {
                    permission.role_has_permission = !newValue;
                });

                const message = (errors as any).message || 'Failed to update permissions';
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: message,
                    life: 3000
                });
            },
        }
    );
}

function recalculateTotalPermissions() {
    let total = 0;
    Object.values(permissions.value).forEach((permissionList: any) => {
        permissionList.forEach((permission: PermissionItem) => {
            if (permission.role_has_permission) {
                total++;
            }
        });
    });
    totalPermissionGranted.value = total;
}

function onSwitchChange(idRole: number, permissionData: PermissionItem, newValue: boolean): Promise<boolean> {
    const formData = {
        id_permission: permissionData.id,
        permission_name: permissionData.name,
        value: newValue,
    };
    
    return new Promise((resolve, reject) => {
        router.post(route('role.switch_permission', idRole), formData, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                // Update total count for individual changes
                totalPermissionGranted.value = newValue 
                    ? totalPermissionGranted.value + 1 
                    : totalPermissionGranted.value - 1;
                    
                const message = (page.props as any).flash?.message;
                if (message) {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: message,
                        life: 2000
                    });
                }
                resolve(true);
            },
            onError: (errors) => {
                // Revert permission state on error
                permissionData.role_has_permission = !newValue;
                
                const message = (errors as any).message || 'An error occurred';
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: message,
                    life: 3000
                });
                reject(new Error('error'));
            },
        });
    });
}

// User pagination
const userPage: Ref<number> = ref(0);
const searchUser: Ref<string> = ref('');

const filteredUser: ComputedRef<User[]> = computed(() => {
    return users.value?.filter(user => 
        user.name.toLowerCase().includes(searchUser.value.toLowerCase())
    ) ?? [];
});

const filteredUserCount: ComputedRef<number> = computed(() => filteredUser.value.length);

const userPaginated: ComputedRef<User[]> = computed(() => {
    const startIndex = userPage.value * 10;
    const endIndex = startIndex + 10;
    return filteredUser.value?.slice(startIndex, endIndex) ?? [];
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>