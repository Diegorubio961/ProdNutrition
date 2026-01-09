import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { Auth } from '../2.general-services/auth/auth';

export const roleAdminGuard: CanActivateFn = () => {
  const auth = inject(Auth);
  const router = inject(Router);
  
  // Solo pasa si tiene el rol explícito
  if (auth.getRoles().includes('admin')) {
    return true;
  }
  // Si no, lo mandamos a /app para que el despachador lo reubique donde debe estar
  return router.createUrlTree(['/app']); 
};