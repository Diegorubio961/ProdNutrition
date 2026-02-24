import { CanActivateFn, Router } from '@angular/router';
import { Auth } from '../2.general-services/auth/auth';
import { inject } from '@angular/core';

export const alreadyHasPlanGuard: CanActivateFn = () => {
  const auth = inject(Auth);
  const router = inject(Router);

  // SI YA TIENE PLAN:
  if (auth.hasPlan()) {
    // Lo mandamos directo a la App
    return router.createUrlTree(['/app']);
  }

  // SI NO TIENE PLAN:
  // Deja que entre a /packages para comprar uno
  return true;
};