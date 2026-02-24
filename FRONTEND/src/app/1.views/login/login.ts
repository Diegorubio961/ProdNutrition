import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LabeledInput } from "../../3.utils/labeled-input/labeled-input";
import { Button } from "../../3.utils/button/button";
import { Checkbox } from "../../3.utils/checkbox/checkbox";
import { ActivatedRoute } from '@angular/router'

@Component({
  selector: 'app-login',
  imports: [
    CommonModule,
    LabeledInput,
    Button,
    Checkbox
  ],
  templateUrl: './login.html',
  styleUrl: './login.scss',
})
export class Login {
  slides: string[] = [
    'assets/img/login/f1.png',
    'assets/img/login/f2.png',
    'assets/img/login/f3.png'
  ];

  currentIndex: number = 0;
  intervalId: any;

  isRegisterMode: boolean = false;

  constructor(private route: ActivatedRoute) {}

  ngOnInit(): void {
    this.startCarousel();

    this.route.data.subscribe( (paqueteRecibido: any) => {
      this.isRegisterMode = paqueteRecibido['registerMode'] || false;
    });

    console.log("Register Mode:", this.isRegisterMode);
  }

  ngOnDestroy(): void {
    // Importante: Limpiar el intervalo cuando el componente se destruye
    if (this.intervalId) {
      clearInterval(this.intervalId);
    }
  }

  startCarousel(): void {
    // Cambia la imagen cada 3000ms (3 segundos)
    this.intervalId = setInterval(() => {
      this.currentIndex = (this.currentIndex + 1) % this.slides.length;
    }, 3000);
  }
}
