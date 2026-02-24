export interface config {
    id?: string;
    label?: string;
    type?: checkboxType;
    color?: checkboxColor;
    labelPosition?: labelPosition;
    disabled?: boolean;        
    onChange?: (checked: boolean) => void;
}

export type checkboxType = 'checkbox' | 'switch';

export type checkboxColor = 'primary' | 'secondary' | 'tertiary';

export type labelPosition = 'before' | 'after' | 'top' | 'bottom';