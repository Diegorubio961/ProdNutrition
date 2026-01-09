import { Component } from '@angular/core';
import { HeaderService } from '../../../../4.shareds/header/header-service';

@Component({
  selector: 'app-plans-edit',
  imports: [],
  templateUrl: './plans-edit.html',
  styleUrl: './plans-edit.scss',
})
export class PlansEdit {
  constructor(private headerService: HeaderService) {
    this.headerService.setupHeader('Planes');
  }
}
