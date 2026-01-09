import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { config } from './checkbox-interface'; 

@Component({
  selector: 'app-checkbox',
  imports: [
    CommonModule
  ],
  templateUrl: './checkbox.html',
  styleUrl: './checkbox.scss',
})
export class Checkbox {
  @Input() config: config = {};

  @Input() checked: boolean = false; 
  @Output() checkedChange = new EventEmitter<boolean>();

  constructor() { }

  toggle(event: Event) {
    // Evitamos que el evento del click se propague doble si hay labels anidados
    event.stopPropagation();

    if (this.config.disabled) return;

    this.checked = !this.checked;
    this.checkedChange.emit(this.checked);

    if (this.config.onChange) {
      this.config.onChange(this.checked);
    }
  }
}
