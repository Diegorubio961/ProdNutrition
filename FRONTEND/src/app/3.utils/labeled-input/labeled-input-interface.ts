export interface config {
    id?: string;
    label?: string;
    placeholder?: string;
    type?: inputType;
    disabled?: boolean;
    minCharacters?: number;
    maxCharacters?: number;
    required?: boolean;
    leftIcon?: string;
    rightIcon?: string;
    tooltip?: string;
    isLoading?: boolean;
    onChange?: (value: string | number) => void;
    errorMessage?: string;
    pattern?: string;
    min?: number;
    max?: number;
    readonly?: boolean;
    autocomplete?: autocompleteType;
    autofocus?: boolean;
    onEnterPress?: () => void;
}

export type inputType = 'text' | 'number' | 'password' | 'email' | 'tel' | 'url';

export type autocompleteType = 'on' | 'off';