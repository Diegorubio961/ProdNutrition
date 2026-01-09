import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HeaderService } from '../header/header-service';

@Component({
  selector: 'app-header',
  imports: [
    CommonModule
  ],
  templateUrl: './header.html',
  styleUrl: './header.scss',
})
export class Header {
  constructor(public headerService: HeaderService) {}
}
