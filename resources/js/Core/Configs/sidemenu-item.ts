import { MenuItem } from "primevue/menuitem";

const basicRoute = (routeName : string, params = {}) => route(routeName,params,false);

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
                url: basicRoute('user.browse'),
            }, 
            { 
                label: 'Roles & Permissions',
                url: basicRoute('role.browse'),
            }, 
            { 
                label: 'User Activity',
                url: basicRoute('user_activity.browse'),
            }
        ]
    },
]