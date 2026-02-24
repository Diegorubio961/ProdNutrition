import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { Aside } from "../../4.shareds/aside/aside";
import { Header } from "../../4.shareds/header/header";

@Component({
  selector: 'app-layout',
  imports: [RouterOutlet, Aside, Header],
  templateUrl: './layout.html',
  styleUrl: './layout.scss',
})
export class Layout {

}
