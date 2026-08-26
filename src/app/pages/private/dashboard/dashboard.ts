import { Component, OnInit, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { Utils } from '../../../core/api/Utils';
import { User } from '../../../core/models/login/User';
import { UserRole } from '../../../core/models/login/UserRole';

interface DashboardSection {
  label: string;
  description: string;
  roles: string[];
  route?: string;
}

const ALL_ROLES = ['SUPERADMIN', 'ADMIN_COMUNIDAD', 'TESORERO', 'CAPTURISTA', 'CONSULTA'];

const ALL_SECTIONS: DashboardSection[] = [
  { label: 'Expediente', description: 'Buscar personas y consultar su balance de pagos', route: '/expediente', roles: ALL_ROLES },
  { label: 'Comunidades', description: 'Alta y administración de comunidades', route: '/comunidades', roles: ['SUPERADMIN'] },
  { label: 'Usuarios', description: 'Cuentas, roles y accesos', route: '/usuarios', roles: ['SUPERADMIN', 'ADMIN_COMUNIDAD'] },
  { label: 'Personas', description: 'Padrón de personas y hogares', route: '/personas', roles: ['SUPERADMIN', 'ADMIN_COMUNIDAD'] },
  { label: 'Pagos', description: 'Cargos, pagos y saldos', roles: ['SUPERADMIN', 'ADMIN_COMUNIDAD', 'TESORERO'] },
  { label: 'Auditoría', description: 'Historial de cambios del sistema', roles: ['SUPERADMIN'] },
];

@Component({
  selector: 'app-dashboard',
  imports: [RouterLink],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.css',
})
export class Dashboard implements OnInit {
  private readonly utils = inject(Utils);

  protected readonly user = signal<User | null>(null);
  protected readonly role = signal<UserRole | null>(null);
  protected readonly sections = signal<DashboardSection[]>([]);

  ngOnInit(): void {
    const session = this.utils.getSessionFromStorage();
    this.user.set(session?.user ?? null);
    this.role.set(session?.role ?? null);

    const roleName = session?.role?.nombre;
    const sections = roleName ? ALL_SECTIONS.filter((section) => section.roles.includes(roleName)) : [];

    // ADMIN_COMUNIDAD administra barrios/secciones de su propia comunidad (SUPERADMIN ya llega ahi desde el listado de Comunidades).
    if (roleName === 'ADMIN_COMUNIDAD' && session?.user?.comunidad_id) {
      sections.push({
        label: 'Configurar comunidad',
        description: 'Barrios y secciones/sector de tu comunidad',
        route: `/comunidades/${session.user.comunidad_id}/configurar`,
        roles: ['ADMIN_COMUNIDAD'],
      });
    }

    this.sections.set(sections);
  }
}
