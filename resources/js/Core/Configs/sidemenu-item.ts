import { MenuItem } from "primevue/menuitem";

const basicRoute = (routeName : string) => route(routeName,{},false);

export const sideMenuItemData: MenuItem[] = [
    {
        label: 'Menu',
        separator: true,
    },
    {
        label: 'Dashboard',
        icon: 'pi pi-home',
        url: basicRoute('dashboard'),
    },
    {
        label: 'User Management',
        icon: 'pi pi-box',
        items: [
            { 
                label: 'Users',
            }, 
            { 
                label: 'Roles & Permissions',
            }, 
            { 
                label: 'User Activity',
            }
        ]
    },
]