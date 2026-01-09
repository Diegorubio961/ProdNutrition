import { Component, Input } from '@angular/core';
import { config } from './card-interface';
import { CommonModule } from '@angular/common';
import { Button } from "../../../3.utils/button/button";

@Component({
  selector: 'app-card',
  imports: [
    CommonModule,
    Button
],
  templateUrl: './card.html',
  styleUrl: './card.scss',
})
export class Card {
  @Input() config: config = {};

  constructor() { }
}
