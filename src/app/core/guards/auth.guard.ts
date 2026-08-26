import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { Utils } from '../api/Utils';

export const authGuard: CanActivateFn = () => {
  const router = inject(Router);
  const utils = inject(Utils);
  if (utils.hasValidSession()) {
    return true;
  }
  return router.parseUrl('/login');
};
