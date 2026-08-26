import { Routes } from '@angular/router';
import { Home } from './pages/public/home/home';
import { Login } from './pages/public/login/login';
import { Dashboard } from './pages/private/dashboard/dashboard';
import { ComunidadAlta } from './pages/private/comunidades/comunidad-alta/comunidad-alta';
import { ComunidadListado } from './pages/private/comunidades/comunidad-listado/comunidad-listado';
import { ComunidadConfiguracion } from './pages/private/comunidades/comunidad-configuracion/comunidad-configuracion';
import { PersonaAlta } from './pages/private/personas/persona-alta/persona-alta';
import { PersonaListado } from './pages/private/personas/persona-listado/persona-listado';
import { UsuarioAlta } from './pages/private/usuarios/usuario-alta/usuario-alta';
import { UsuarioListado } from './pages/private/usuarios/usuario-listado/usuario-listado';
import { Expediente } from './pages/private/expediente/expediente';
import { authGuard } from './core/guards/auth.guard';
import { guestGuard } from './core/guards/guest.guard';
import { roleGuard } from './core/guards/role.guard';

export const routes: Routes = [
    { path: '', redirectTo: 'home', pathMatch: 'full' },
    { path: 'home', component: Home },
    { path: 'login', component: Login, canActivate: [guestGuard] },
    { path: 'dashboard', component: Dashboard, canActivate: [authGuard] },
    { path: 'comunidades', component: ComunidadListado, canActivate: [authGuard, roleGuard(['SUPERADMIN'])] },
    { path: 'comunidades/nueva', component: ComunidadAlta, canActivate: [authGuard, roleGuard(['SUPERADMIN'])] },
    { path: 'comunidades/:id/configurar', component: ComunidadConfiguracion, canActivate: [authGuard, roleGuard(['SUPERADMIN', 'ADMIN_COMUNIDAD'])] },
    { path: 'personas', component: PersonaListado, canActivate: [authGuard, roleGuard(['SUPERADMIN', 'ADMIN_COMUNIDAD'])] },
    { path: 'personas/nueva', component: PersonaAlta, canActivate: [authGuard, roleGuard(['SUPERADMIN', 'ADMIN_COMUNIDAD'])] },
    { path: 'usuarios', component: UsuarioListado, canActivate: [authGuard, roleGuard(['SUPERADMIN', 'ADMIN_COMUNIDAD'])] },
    { path: 'usuarios/nueva', component: UsuarioAlta, canActivate: [authGuard, roleGuard(['SUPERADMIN', 'ADMIN_COMUNIDAD'])] },
    { path: 'expediente', component: Expediente, canActivate: [authGuard] },
    { path: '**', redirectTo: 'home' },
];
