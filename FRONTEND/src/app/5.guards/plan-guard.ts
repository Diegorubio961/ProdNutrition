import { CanActivateFn, Router } from '@angular/router';
import { Auth } from '../2.general-services/auth/auth';
import { inject } from '@angular/core';

export const planGuard: CanActivateFn = () => {
  const auth = inject(Auth);
  const router = inject(Router);

  // Si tiene plan, ¡PASA!
  if (auth.hasPlan()) {
    return true;
  }
  // Si no, a comprar paquetes
  return router.createUrlTree(['/packages']);
};