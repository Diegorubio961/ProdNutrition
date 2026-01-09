export interface config {
    label?: string;
    options: any[];
    placeholder?: string;
    disabled?: boolean;
    searchable?: boolean;
    clearable?: boolean;
    noOptionsMessage?: string;
    onSelect?: (option: any) => void;
    onClear?: () => void;
    onSearch?: (searchTerm: string) => void;
    labelKey?: string;
    valueKey?: string;
    multiSelect?: boolean;
}

