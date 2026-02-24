import { Component } from '@angular/core';
import { Packages as PackagesService } from '../../2.general-services/packages/packages';
import { Card } from "./card/card";

@Component({
  selector: 'app-packages',
  imports: [Card],
  templateUrl: './packages.html',
  styleUrl: './packages.scss',
})
export class Packages {
  constructor(
    public packagesService: PackagesService
  ) { }
}
