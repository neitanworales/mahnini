import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { Utils } from '../api/Utils';

export const guestGuard: CanActivateFn = () => {
  const router = inject(Router);
  const utils = inject(Utils);
  if (utils.hasValidSession()) {
    return router.parseUrl('/dashboard');
  }
  return true;
};
