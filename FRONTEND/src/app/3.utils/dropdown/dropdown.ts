import { Component, Input, HostListener, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { config } from './dropdown-interface';
import { Checkbox } from "../checkbox/checkbox";

@Component({
  selector: 'app-dropdown',
  imports: [
    CommonModule,
    Checkbox
],
  templateUrl: './dropdown.html',
  styleUrl: './dropdown.scss',
})
export class Dropdown {
  @Input() config: config = {
    options: []
  };

  isOpen = false;
  selectedOption: any = null;
  searchTerm = '';

  constructor(private eRef: ElementRef) { }

  // Cierra el dropdown si clicas fuera del componente
  @HostListener('document:click', ['$event'])
  clickout(event: Event) {
    if (!this.eRef.nativeElement.contains(event.target)) {
      this.isOpen = false;
    }
  }

  toggleDropdown() {
    if (!this.config.disabled) {
      this.isOpen = !this.isOpen;
    }
  }

  // selectOption(option: any) {
  //   this.selectedOption = option;
  //   this.isOpen = false;

  //   // Ejecutar el callback de tu interfaz
  //   if (this.config.onSelect) {
  //     this.config.onSelect(option);
  //   }
  // }

  // // Helper para obtener el texto a mostrar (si es objeto o string)
  // getOptionLabel(option: any): string {
  //   if (!option) return '';
  //   if (this.config.labelKey && typeof option === 'object') {
  //     return option[this.config.labelKey];
  //   }
  //   return option; // Si es un string simple
  // }

  // // Filtrado simple para la vista (si searchable es true)
  // get filteredOptions() {
  //   if (!this.searchTerm) return this.config.options;
    
  //   return this.config.options.filter(opt => 
  //     this.getOptionLabel(opt).toLowerCase().includes(this.searchTerm.toLowerCase())
  //   );
  // }

  // onSearchChange(event: any) {
  //   this.searchTerm = event.target.value;
  //   if (this.config.onSearch) {
  //     this.config.onSearch(this.searchTerm);
  //   }
  // }


  // --- LÓGICA DE SELECCIÓN ---
  selectOption(option: any) {
    if (this.config.multiSelect) {
      // Inicializar si es null
      if (!Array.isArray(this.selectedOption)) {
        this.selectedOption = [];
      }

      const index = this.selectedOption.indexOf(option);
      
      if (index > -1) {
        // Si ya existe, lo quitamos (deseleccionar)
        this.selectedOption.splice(index, 1); 
      } else {
        // Si no existe, lo agregamos
        this.selectedOption.push(option);
      }
      // En multiSelect NO cerramos el dropdown (isOpen = true)

    } else {
      // Modo Single: Reemplazar y cerrar
      this.selectedOption = option;
      this.isOpen = false;
    }

    // Callback
    if (this.config.onSelect) {
      this.config.onSelect(this.selectedOption);
    }
  }

  // --- LÓGICA DE LIMPIEZA (CLEARABLE) ---
  clearSelection(event: Event) {
    event.stopPropagation(); // Evitar que abra/cierre el dropdown
    this.selectedOption = this.config.multiSelect ? [] : null;
    
    if (this.config.onClear) {
      this.config.onClear();
    }
  }

  // --- HELPERS VISUALES ---
  
  // Verifica si una opción está seleccionada (para el checkbox y clase CSS)
  isSelected(option: any): boolean {
    if (this.config.multiSelect) {
      return Array.isArray(this.selectedOption) && this.selectedOption.includes(option);
    }
    return this.selectedOption === option;
  }

  // Verifica si hay algo seleccionado para mostrar la X de limpiar
  get hasSelection(): boolean {
    if (this.config.multiSelect) {
      return Array.isArray(this.selectedOption) && this.selectedOption.length > 0;
    }
    return this.selectedOption !== null && this.selectedOption !== undefined;
  }

  // Texto a mostrar en el trigger
  get displayText(): string {
    if (!this.hasSelection) {
      return this.config.placeholder || 'Seleccionar';
    }

    if (this.config.multiSelect) {
      // Ejemplo: "Activo, Pendiente"
      return this.selectedOption
        .map((opt: any) => this.getOptionLabel(opt))
        .join(', ');
    }
    
    return this.getOptionLabel(this.selectedOption);
  }

  getOptionLabel(option: any): string {
    if (!option) return '';
    if (this.config.labelKey && typeof option === 'object') {
      return option[this.config.labelKey];
    }
    return option;
  }

  get filteredOptions() {
    if (!this.searchTerm) return this.config.options;
    return this.config.options.filter(opt => 
      this.getOptionLabel(opt).toLowerCase().includes(this.searchTerm.toLowerCase())
    );
  }

  onSearchChange(event: any) {
    this.searchTerm = event.target.value;
    if (this.config.onSearch) this.config.onSearch(this.searchTerm);
  }
}
