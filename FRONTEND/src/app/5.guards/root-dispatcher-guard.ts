import { CanActivateFn, Router } from '@angular/router';
import { Auth } from '../2.general-services/auth/auth';
import { inject } from '@angular/core';

export const rootDispatcherGuard: CanActivateFn = (route, state) => {
  const authService = inject(Auth);
  const router = inject(Router);

  // Lógica de "Semáforo"
  if (!authService.isLoggedIn()) {    
    return router.createUrlTree(['/login']);
  } else if (!authService.hasPlan()) {
    return router.createUrlTree(['/packages']);
  } else {
    return router.createUrlTree(['/app']);
  }
};