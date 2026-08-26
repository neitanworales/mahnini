import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { UsuarioDao } from '../../../../core/api/UsuarioDao';
import { ComunidadDao } from '../../../../core/api/ComunidadDao';
import { PersonaDao } from '../../../../core/api/PersonaDao';
import { RolDao } from '../../../../core/api/RolDao';
import { Usuario } from '../../../../core/models/usuario/Usuario';
import { Rol } from '../../../../core/models/rol/Rol';

type FiltroActivo = 'TODOS' | 'ACTIVOS' | 'INACTIVOS';

@Component({
  selector: 'app-usuario-listado',
  imports: [RouterLink],
  templateUrl: './usuario-listado.html',
  styleUrl: './usuario-listado.css',
})
export class UsuarioListado implements OnInit {
  private readonly usuarioDao = inject(UsuarioDao);
  private readonly comunidadDao = inject(ComunidadDao);
  private readonly personaDao = inject(PersonaDao);
  private readonly rolDao = inject(RolDao);

  protected readonly usuarios = signal<Usuario[]>([]);
  protected readonly loading = signal(true);
  protected readonly errorMessage = signal('');
  protected readonly comunidadNombres = signal<Record<number, string>>({});
  protected readonly rolNombres = signal<Record<number, string>>({});
  protected readonly personaNombres = signal<Record<number, string>>({});
  protected readonly roles = signal<Rol[]>([]);
  protected readonly busqueda = signal('');
  protected readonly filtroRolId = signal<number | null>(null);
  protected readonly filtroActivo = signal<FiltroActivo>('TODOS');

  protected readonly usuariosFiltrados = computed(() => {
    const termino = this.busqueda().trim().toLowerCase();
    const rolId = this.filtroRolId();
    const activo = this.filtroActivo();

    return this.usuarios().filter((usuario) => {
      if (rolId !== null && usuario.rolId !== rolId) {
        return false;
      }
      if (activo === 'ACTIVOS' && !usuario.activo) {
        return false;
      }
      if (activo === 'INACTIVOS' && usuario.activo) {
        return false;
      }
      if (termino === '') {
        return true;
      }

      return usuario.email.toLowerCase().includes(termino);
    });
  });

  ngOnInit(): void {
    this.comunidadDao.list().subscribe({
      next: (response) => {
        const nombres: Record<number, string> = {};
        for (const comunidad of response.data?.items ?? []) {
          nombres[comunidad.id] = comunidad.nombre;
        }
        this.comunidadNombres.set(nombres);
      },
      error: () => this.comunidadNombres.set({}),
    });

    this.rolDao.list().subscribe({
      next: (response) => {
        const items = response.data?.items ?? [];
        this.roles.set(items);
        const nombres: Record<number, string> = {};
        for (const rol of items) {
          nombres[rol.id] = rol.nombre;
        }
        this.rolNombres.set(nombres);
      },
      error: () => this.rolNombres.set({}),
    });

    this.personaDao.list().subscribe({
      next: (response) => {
        const nombres: Record<number, string> = {};
        for (const persona of response.data?.items ?? []) {
          nombres[persona.id] = `${persona.nombre} ${persona.apellidoPaterno ?? ''}`.trim();
        }
        this.personaNombres.set(nombres);
      },
      error: () => this.personaNombres.set({}),
    });

    this.usuarioDao.list().subscribe({
      next: (response) => {
        this.loading.set(false);
        if (!response.success) {
          this.errorMessage.set(response.message || 'No se pudieron cargar los usuarios');
          return;
        }
        this.usuarios.set(response.data?.items ?? []);
      },
      error: (err) => {
        this.loading.set(false);
        this.errorMessage.set(err?.error?.message || 'No se pudieron cargar los usuarios');
      },
    });
  }

  protected nombreComunidad(comunidadId?: number): string {
    return (comunidadId && this.comunidadNombres()[comunidadId]) || '—';
  }

  protected nombreRol(rolId?: number): string {
    return (rolId && this.rolNombres()[rolId]) || '—';
  }

  protected nombrePersona(personaId?: number): string {
    return (personaId && this.personaNombres()[personaId]) || '—';
  }
}
