import { Injectable } from '@angular/core';
import { packages } from './package-interface';

@Injectable({
  providedIn: 'root',
})
export class Packages {
  packages: packages[] = [
    {
      id: 0,
      time: 'Mes',
      price: 'Gratis',
      features: [
        '✓ 10 clientes incluidos',
        '✓ 1 nutricionista',
        '✓ Soporte por correo',
      ],
    },
    {
      id: 1,
      time: 'Trimestre',
      price: '$30.000 cop',
      features: [
        '✓ 25 clientes incluidos',
        '✓ 1 nutricionista',
        '✓ panel de métricas avanzado',
      ],
      recomended: true
    },
    {
      id: 2,
      time: 'Semestre',
      price: '$100.000 cop',
      features: [
        '✓ 50 Clientes incluidos',
        '✓ 1 Nutricionista',
        '✓ Soporte prioritario',
      ],
    },
    {
      id: 3,
      time: 'Anual',
      price: 'Personalizado',
      features: [
        '✓ Clientes ilimitados',
        '✓ 5 Nutricionistas',
        '✓ Soporte 24/7 y formación personalizada',
      ],
    },
  ];

  constructor() { }

  getPackages(): packages[] {
    return this.packages;
  }
}
