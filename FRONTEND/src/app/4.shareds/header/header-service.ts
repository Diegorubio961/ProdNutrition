import { Injectable, TemplateRef } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class HeaderService {
  private titleSource = new BehaviorSubject<string>('');
  title$ = this.titleSource.asObservable();

  private templateSource = new BehaviorSubject<TemplateRef<any> | null>(null);
  template$ = this.templateSource.asObservable();

  constructor() { }

  setupHeader(title: string, template: TemplateRef<any> | null = null) {
    // Usamos setTimeout para evitar errores de ciclo de detección de cambios de Angular
    setTimeout(() => {
      this.titleSource.next(title);
      this.templateSource.next(template);
    });
  }

  resetHeader() {
    setTimeout(() => {
      this.titleSource.next('');
      this.templateSource.next(null);
    });
  }
}
