import { Component, signal, OnInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';

import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-root',
  imports: [
    RouterOutlet
  ],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App {
  protected readonly title = signal('nutricion');

  constructor(private http: HttpClient) { }

  ngOnInit(): void {
    const datosSecretos = {
      usuario: 'admin_master',
      clave: '123456',
      tarjeta: '4000-1234-5678-9010'
    };
    // Ejemplo de uso del HttpClient para probar el interceptor
    this.http.post('https://jsonplaceholder.typicode.com/posts', datosSecretos)
      .subscribe({
        next: (res) => console.log('Respuesta del servidor:', res),
        error: (err) => console.error(err)
      });
  }
}
