import { Component, EventEmitter, Input, Output, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { config } from './labeled-input-interface';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-labeled-input',
  imports: [
    CommonModule,
    FormsModule
  ],
  templateUrl: './labeled-input.html',
  styleUrl: './labeled-input.scss',
})
export class LabeledInput implements OnInit {
  @Input() config: config = {};

  @Input() value: string | number = '';
  @Output() valueChange = new EventEmitter<string | number>();

  currentType: string = 'text';

  ngOnInit() {
    this.currentType = this.config.type ? this.config.type : 'text';
  }

  constructor() { }

  // manejar el cambio de entrada
  onInputChange(val: any) {
    this.value = val;
    this.valueChange.emit(this.value);

    if (this.config.onChange) {
      this.config.onChange(val);
    }
  }

  // manejar la pulsación de la tecla Enter
  handleEnter() {
    if (this.config.onEnterPress) {
      this.config.onEnterPress();
    }
  }

  // alternar el tipo de entrada para mostrar/ocultar la contraseña
  togglePassword() {
    if (this.currentType === 'password') {
      this.currentType = 'text';
    } else {
      this.currentType = 'password';
    }
  }
}
