export interface config {
    id?: string;
    label?: string;
    onClick?: () => void;
    disabled?: boolean;    
    loading?: boolean;
    leftIcon?: string;
    rightIcon?: string;
    type?: buttonType;
    color?: buttonColor;
}

export type buttonType = 'button' | 'submit' | 'reset';

export type buttonColor = 'primary' | 'secondary' | 'tertiary';