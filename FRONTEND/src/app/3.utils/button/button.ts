import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { config } from './button-interface';

@Component({
  selector: 'app-button',
  imports: [
    CommonModule
  ],
  templateUrl: './button.html',
  styleUrl: './button.scss',
})
export class Button {
  @Input() config: config = {};

  constructor() { }

  handleClick(event: Event): void {    
    if (this.config.disabled || this.config.loading) {
      event.preventDefault();
      event.stopPropagation();
      return;
    }
    
    if (this.config.onClick) {
      this.config.onClick();
    }
  }
}
