import { Component, AfterViewInit, TemplateRef, OnDestroy, ViewChild } from '@angular/core';
import { HeaderService } from '../../../../4.shareds/header/header-service';
import { LabeledInput } from "../../../../3.utils/labeled-input/labeled-input";
import { Dropdown } from "../../../../3.utils/dropdown/dropdown";

@Component({
  selector: 'app-nutritionist-list',
  imports: [
    LabeledInput,
    Dropdown
],
  templateUrl: './nutritionist-list.html',
  styleUrl: './nutritionist-list.scss',
})
export class NutritionistList implements OnDestroy, AfterViewInit {
  @ViewChild('headerActions') headerTemplate!: TemplateRef<any>;

  constructor(private headerService: HeaderService) {  }

  ngAfterViewInit(): void {
    this.headerService.setupHeader('Nutricionistas', this.headerTemplate);
  }

  ngOnDestroy(): void {
    this.headerService.resetHeader();
  }
}
