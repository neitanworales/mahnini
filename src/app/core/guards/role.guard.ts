import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { Utils } from '../api/Utils';

export function roleGuard(allowedRoles: string[]): CanActivateFn {
  return () => {
    const router = inject(Router);
    const utils = inject(Utils);
    const session = utils.getSessionFromStorage();

    if (!session?.token) {
      return router.parseUrl('/login');
    }

    if (!session.role || !allowedRoles.includes(session.role.nombre)) {
      return router.parseUrl('/dashboard');
    }

    return true;
  };
}
