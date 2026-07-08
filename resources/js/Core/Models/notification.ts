export default interface Notification {
    id: string;
    type: string;
    notifiable_type: string;
    notifiable_id: number;
    data: {
        title: string;
        message: string;
        url?: string;
        icon?: string;
    };
    read_at: string | null;
    created_at: string;
    updated_at: string;
}