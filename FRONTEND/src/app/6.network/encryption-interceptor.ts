import { HttpInterceptorFn, HttpResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { map } from 'rxjs/operators';
import { Encryption } from '../2.general-services/encryption/encryption';

export const encryptionInterceptor: HttpInterceptorFn = (req, next) => {
  const encryptionService = inject(Encryption);

  let requestToForward = req;

  // Solo encriptamos si hay cuerpo y NO es un FormData (archivos)
  if (req.body && !(req.body instanceof FormData)) {
    
    // Encriptamos el body usando tu servicio
    const encryptedBody = encryptionService.encrypt(req.body);

    // Clonamos la petición (porque son inmutables)
    requestToForward = req.clone({
      body: encryptedBody,
      // Cambiamos el header para avisar que enviamos texto, no JSON
      setHeaders: {
        'Content-Type': 'text/plain' 
      }
    });
  }

  // ==========================================
  // 📥 FASE 2: ENTRADA (RESPONSE)
  // ==========================================
  return next(requestToForward).pipe(
    map((event) => {
      // Verificamos que sea una respuesta HTTP exitosa y que tenga cuerpo
      if (event instanceof HttpResponse && event.body) {
        
        // Intentamos detectar si la respuesta es un string (encriptado)
        if (typeof event.body === 'string') {
           // Desencriptamos para que el componente reciba el JSON limpio
           const decryptedBody = encryptionService.decrypt(event.body);
           
           // Clonamos la respuesta con el cuerpo ya legible
           return event.clone({ body: decryptedBody });
        }
      }
      return event;
    })
  );
};
