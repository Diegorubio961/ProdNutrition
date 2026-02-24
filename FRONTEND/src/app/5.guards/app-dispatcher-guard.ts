import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { Auth } from '../2.general-services/auth/auth'; // Tu servicio

export const appDispatcherGuard: CanActivateFn = () => {
  const auth = inject(Auth);
  const router = inject(Router);
  
  // Asumimos que getRoles() devuelve un array: ['ADMIN', 'NUTRITIONIST']
  const roles = auth.getRoles(); 

  // --- PRIORIDAD 1: ADMIN ---
  // Si tiene el rol admin, va directo a su dashboard, aunque tenga otros roles.
  if (roles.includes('admin')) {
    return router.createUrlTree(['/app/admin']);
  }

  // --- PRIORIDAD 2: NUTRICIONISTA ---
  if (roles.includes('nutritionist')) {
    return router.createUrlTree(['/app/nutritionist']);
  }

  // --- PRIORIDAD 3: CLIENTE (Default) ---
  return router.createUrlTree(['/app/client']);
};