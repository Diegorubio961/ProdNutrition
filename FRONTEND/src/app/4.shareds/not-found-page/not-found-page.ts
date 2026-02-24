import { Component } from '@angular/core';
import { Button } from "../../3.utils/button/button";
import { Router } from '@angular/router';

@Component({
  selector: 'app-not-found-page',
  imports: [
    Button
  ],
  templateUrl: './not-found-page.html',
  styleUrl: './not-found-page.scss',
})
export class NotFoundPage {
  constructor(private router: Router) { }

  goHome = () => {
    this.router.navigate(['/']);
  }
}
