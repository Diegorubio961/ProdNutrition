import { Component } from '@angular/core';
import { HeaderService } from '../../../../4.shareds/header/header-service';

@Component({
  selector: 'app-dashboard',
  imports: [],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.scss',
})
export class Dashboard {
  constructor(private headerService: HeaderService) {
    this.headerService.setupHeader('Dashboard');
  }
}
