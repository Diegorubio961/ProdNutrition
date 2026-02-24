export interface MenuItem {
    label: string;
    icon?: string; // Clase de FontAwesome ej: 'fa-regular fa-user'
    route?: string;
    active?: boolean;
    expanded?: boolean; // Para tree
    children?: MenuItem[]; // Submenú
    roles: string[]; // Roles que pueden ver este ítem
}

