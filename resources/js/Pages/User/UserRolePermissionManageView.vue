<template>
    <Head title="Role Manage" />
    <AdminLayout title="Role Manage" :breadcrumbs>
        <div class="flex">
            <div class="w-80 mr-4 flex-flex-col flex-grow-0 flex-shrink-0">
                <div class="flex justify-end mb-4 gap-4">
                    <div class="flex-1">
                        <AppInputSearch v-model="search" />
                    </div>
                    <div class="flex-none">
                        <Button icon="pi pi-plus" @click="addUserRoleAction" v-if="can('role.create')"
                            v-tooltip.bottom="'Add User Role'" />
                    </div>
                </div>
                <div class="h-[500px] overflow-y-scroll scroll pr-2">
                    <div v-if="isRolesEmpty" class="h-full w-full flex items-center justify-center">
                        <span class="text-gray-700 italic">
                            No Data
                        </span>
                    </div>
                    <div v-else>
                        <div v-ripple class="p-4 rounded-xl border border-gray-200  w-full mb-2 cursor-pointer" :class="[
                            { 'bg-primary': role.id == idSelectedRole },
                            { 'bg-white hover:bg-primary-surface': role.id != idSelectedRole },
                        ]" v-for="role in filteredRoles" :key="role.id" @click="selectingRole(role)">
                            <div class="flex flex-row justify-between items-center">
                                <div class="flex flex-row items-center">
                                    <div class="flex flex-col items-start">
                                        <span class="font-bold text-left" :class="[
                                            { 'text-white': role.id == idSelectedRole },
                                            { 'text-black': role.id != idSelectedRole },
                                        ]">
                                            {{ role.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grow">
                <div class="col-span-12 lg:col-span-4 p-4 flex-1 border border-gray-200 rounded-2xl h-full">
                    <div class="h-[500px] w-full flex items-center justify-center flex-col" v-if="!isAnyRoleSelected">
                        <i class="pi pi-exclamation-triangle text-[50px] text-primary"></i>
                        <h4 class="text-xl font-bold mb-2">Tidak ada User Role yang dipilih</h4>
                        <span class="w-56 text-center text-gray-700">
                            Pilih role di kiri untuk melihat atau mengedit role pengguna.
                        </span>
                    </div>
                    <div v-else>
                        <Transition name="fadetransition" mode="out-in" appear>
                            <div class="h-[500px] w-full flex items-center justify-center flex-col"
                                v-if="permissionLoading">
                                <ProgressSpinner stroke-width="4" />
                            </div>
                            <div v-else class="flex flex-col">
                                <div class="flex flex-row justify-between items-center mb-4">
                                    <div class="flex flex-col">
                                        <h3 class="text-xl font-bold">{{ selectedRole.name }}</h3>
                                        <span class="text-sm text-gray-800">
                                            {{ totalPermissionGranted }} Permission Granted | {{ totalUser }} Users
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <Button variant="text" icon="pi pi-ellipsis-v" severity="secondary" rounded
                                            v-tooltip.bottom="'Action'" @click="(e) => $refs.roleMenu?.toggle(e)" />
                                        <Popover ref="roleMenu">
                                            <div class="flex flex-col">
                                                <Button icon="pi pi-pen-to-square" variant="text" severity="secondary"
                                                    label="Edit User Role" class="w-full flex justify-start"
                                                    @click="editUserRoleAction" />
                                                <Button icon="pi pi-trash" variant="text" severity="danger"
                                                    label="Delete User Role" class="w-full flex justify-start"
                                                    @click="deleteUserRoleAction" />
                                            </div>
                                        </Popover>
                                    </div>
                                </div>
                                <Tabs :value="activeTab">
                                    <TabList>
                                        <Tab value="permission">Permission</Tab>
                                        <Tab value="user">User</Tab>
                                    </TabList>
                                    <TabPanels>
                                        <TabPanel value="permission">
                                            <div class="grid grid-cols-1 xl:grid-cols-2">
                                                <div class="bg-white rounded-lg p-4 mx-2 mb-2 border border-gray-200"
                                                    v-for="permissionList, permissionGroupName in rolePermissions">
                                                    <div class="flex items-center justify-between">
                                                        <h5 class="text-xl font-bold">
                                                            {{ parsePermissionName(permissionGroupName as string) }}
                                                        </h5>
                                                        <ToggleSwitch
                                                            :disabled="!can('role.assign_permission') || selectedRole.id == 1"
                                                            :model-value="permissionList.every(permission => permission.role_has_permission)"
                                                            @value-change="(val) => permissionList.forEach(function (permission) { permission.role_has_permission = val; onSwitchChange(selectedRole.id, permission, val) })" />
                                                    </div>
                                                    <Divider />
                                                    <div class="grid grid-cols-1 2xl:grid-cols-2">
                                                        <div class="flex flex-row justify-between items-center px-4 py-2"
                                                            v-for="permissionObj in permissionList">
                                                            <span class="text-gray-800">
                                                                {{ parsePermissionName(permissionObj.name) }}
                                                            </span>
                                                            <ToggleSwitch
                                                                :disabled="!can('role.assign_permission') || selectedRole.id == 1"
                                                                v-model="permissionObj.role_has_permission"
                                                                @value-change="(val: boolean) => onSwitchChange(selectedRole.id, permissionObj, val)" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </TabPanel>
                                        <TabPanel value="user">
                                            <div class="flex justify-between">
                                                <div>
                                                </div>
                                                <IconField>
                                                    <InputIcon>
                                                        <i class="pi pi-search" />
                                                    </InputIcon>
                                                    <InputText placeholder="Search" v-model="searchUser" fluid />
                                                </IconField>
                                            </div>
                                            <div v-if="userPaginated.length > 0" class="h-full">
                                                <div class="mb-4">
                                                    <div v-for="user in userPaginated" :key="user.id">
                                                        <div
                                                            class="shrink-0 rounded-lg py-2 px-5 border gap-4 border-gray-200 flex align-middle items-center bg-white group-hover:bg-primary-200 cursor-pointer mt-2">
                                                            <AppLetterAvatar :name="user.name" />
                                                            <div class="flex items-center">
                                                                <div class=" flex flex-col text-gray-900">
                                                                    <div class="truncate text-md font-bold">
                                                                        {{ user.name }}
                                                                    </div>
                                                                    <div class="truncate text-xs font-thin">
                                                                        {{ user.username }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex justify-center">
                                                    <Paginator :rows="10" :totalRecords="filteredUserCount"
                                                        @page="(e) => userPage = e.page"
                                                        template="FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink" />
                                                </div>
                                            </div>
                                            <div v-else>
                                                <div class="flex h-20 items-center justify-center italic text-gray-800">
                                                    No User
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
    <UserRoleFormModal ref="userRoleFormModalRef" @data-deleted="idSelectedRole = null" />
</template>
<script setup lang="ts">
import AppLetterAvatar from '@/Components/AppAvatarLetter.vue';
import { User } from '@/Core/Models/user';
import { PermissionGroups, PermissionItem, UserRole } from '@/Core/Models/user-role';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from 'primevue';
import { MenuItem } from 'primevue/menuitem';
import { computed, ComputedRef, ref, Ref } from 'vue';
import UserRoleFormModal from './Components/UserRoleFormModal.vue';
import { FormModalExpose } from '@/Core/Models/form-modal';
import AppInputSearch from '@/Components/AppInputSearch.vue';

const toast = useToast();

//TODO : Implement can
const can = (params: any) => true;

const breadcrumbs: Ref<MenuItem[]> = ref([
    {
        label: 'Role Management',
        url: route('role.browse'),
    }
])
const roles: ComputedRef<UserRole[]> = computed(() => usePage().props.roles as UserRole[]);
const search: Ref<string> = ref('');

const filteredRoles = computed(() => {
    return roles.value.filter(role => role.name.toLowerCase().includes(search.value.toLowerCase()));
});
const isRolesEmpty = computed(() => filteredRoles.value.length < 1);

// Role permission & User
const activeTab: Ref<string> = ref('permission');
const idSelectedRole: Ref<number | null> = ref(null);
const selectedRole: ComputedRef<UserRole> = computed(() => roles.value.find(role => role.id == idSelectedRole.value) as UserRole);

const rolePermissions: Ref<PermissionGroups> = ref({} as PermissionGroups);
const totalPermissionGranted: Ref<number> = ref(0);

const roleUsers: Ref<User[]> = ref([] as User[]);
const totalUser: Ref<number> = ref(0);

const isAnyRoleSelected: ComputedRef<boolean> = computed(() => idSelectedRole.value != null);
const permissionLoading: Ref<boolean> = ref(false);

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

function selectingRole(dataRole: UserRole) {
    idSelectedRole.value = dataRole.id;
    permissionLoading.value = true;
    userPage.value = 1;
    activeTab.value = 'permission';
    axios.get(route('role.permission_list', dataRole.id))
        .then((response) => {
            let responseData = response.data;
            rolePermissions.value = responseData.data.permissions;
            totalPermissionGranted.value = responseData.data.total_assigned_permission;
        })
        .catch((error) => {
            let errorResponseData = error.response.data;
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: errorResponseData.message,
                life: 3000
            });
        })
        .finally(() => {
            setTimeout(() => {
                permissionLoading.value = false;
            }, 500)
        });
    axios.get(route('role.user_list', dataRole.id))
        .then((response) => {
            let responseData = response.data;
            roleUsers.value = responseData.data.users;
            totalUser.value = responseData.data.user_count;
        })
        .catch((error) => {
            let errorResponseData = error.response.data;
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: errorResponseData.message,
                life: 3000
            });
        })
        .finally(() => {
            setTimeout(() => {
                permissionLoading.value = false;
            }, 500)
        });
}
function parsePermissionName(str: string) {
    let text = str.split('.').slice(-1)[0];
    let words = text.split('_');
    let result = words.map(word => word.charAt(0).toLowerCase() + word.slice(1)).join(' ');
    result = result.charAt(0).toUpperCase() + result.slice(1);
    return result;
}
function onSwitchChange(idRole: number, permissionData: PermissionItem, newValue: any) {
    const formData = {
        id_permission: permissionData.id,
        permission_name: permissionData.name,
        value: newValue,
    };
    return new Promise((resolve, reject) => {
        return axios.put(route('role.switch_permission', idRole), formData)
            .then((response) => {
                let responseData = response.data;
                totalPermissionGranted.value = !newValue ? totalPermissionGranted.value - 1 : totalPermissionGranted.value + 1;
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: responseData.message,
                    life: 1000
                });
                return resolve(true);
            })
            .catch((error) => {
                let errorResponseData = error.response.data;
                permissionData.role_has_permission = newValue;
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: errorResponseData.message,
                    life: 3000
                });
                return reject(new Error('error'));
            });
    });
}

// USER PAGINATION
const userPage: Ref<number> = ref(1);
const searchUser: Ref<string> = ref('');
const filteredUser: Ref<User[]> = computed(() => {
    return roleUsers.value?.filter(user => user.name.toLowerCase().includes(searchUser.value.toLowerCase())) ?? [];
});
const filteredUserCount: Ref<number> = computed(() => filteredUser.value.length);
const userPaginated: Ref<User[]> = computed(() => {
    const startIndex = (userPage.value - 1) * 10;
    const endIndex = startIndex + 10;
    const paginatedUsers = filteredUser.value?.slice(startIndex, endIndex) ?? [];
    return paginatedUsers;
});
</script>