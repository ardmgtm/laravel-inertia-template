export interface FormModalExpose<T>{
    addAction: () => void;
    editAction: (data: T) => void;
    deleteAction: (data: T) => void;
}