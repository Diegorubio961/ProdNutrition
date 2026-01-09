import { CanActivateFn, Router } from '@angular/router';
import { Auth } from '../2.general-services/auth/auth';
import { inject } from '@angular/core';

export const authGuard: CanActivateFn = () => {
  const auth = inject(Auth);
  const router = inject(Router);

  // Si está logueado, ¡PASA! (return true)
  if (auth.isLoggedIn()) {
    return true; 
  }
  // Si no, al login
  return router.createUrlTree(['/login']);
};