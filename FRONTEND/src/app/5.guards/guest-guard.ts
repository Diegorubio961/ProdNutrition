import { CanActivateFn, Router } from '@angular/router';
import { Auth } from '../2.general-services/auth/auth';
import { inject } from '@angular/core';

export const guestGuard: CanActivateFn = () => {
  const authService = inject(Auth);
  const router = inject(Router);

  // CASO 1: YA ESTÁ LOGUEADO
  if (authService.isLoggedIn()) {
    // Lo mandamos a la raíz ('') para que el rootDispatcher decida 
    // si va a /packages o a /app según su plan.
    return router.createUrlTree(['/']);
  }

  // CASO 2: NO ESTÁ LOGUEADO
  // Perfecto, déjalo entrar al login.
  return true;
};