import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { PersonaDao } from '../../../../core/api/PersonaDao';
import { ComunidadDao } from '../../../../core/api/ComunidadDao';
import { Persona } from '../../../../core/models/persona/Persona';

const ESTATUS_OPTIONS = ['ACTIVO', 'INACTIVO', 'FALLECIDO', 'BAJA'];

@Component({
  selector: 'app-persona-listado',
  imports: [RouterLink],
  templateUrl: './persona-listado.html',
  styleUrl: './persona-listado.css',
})
export class PersonaListado implements OnInit {
  private readonly personaDao = inject(PersonaDao);
  private readonly comunidadDao = inject(ComunidadDao);

  protected readonly personas = signal<Persona[]>([]);
  protected readonly loading = signal(true);
  protected readonly errorMessage = signal('');
  protected readonly comunidadNombres = signal<Record<number, string>>({});
  protected readonly busqueda = signal('');
  protected readonly filtroEstatus = signal('');
  protected readonly estatusOptions = ESTATUS_OPTIONS;

  protected readonly personasFiltradas = computed(() => {
    const termino = this.busqueda().trim().toLowerCase();
    const estatus = this.filtroEstatus();

    return this.personas().filter((persona) => {
      if (estatus !== '' && persona.estatus !== estatus) {
        return false;
      }
      if (termino === '') {
        return true;
      }

      return [persona.numeroControl, persona.nombre, persona.apellidoPaterno, persona.apellidoMaterno, persona.barrio, persona.seccion]
        .some((campo) => (campo ?? '').toLowerCase().includes(termino));
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

    this.personaDao.list().subscribe({
      next: (response) => {
        this.loading.set(false);
        if (!response.success) {
          this.errorMessage.set(response.message || 'No se pudieron cargar las personas');
          return;
        }
        this.personas.set(response.data?.items ?? []);
      },
      error: (err) => {
        this.loading.set(false);
        this.errorMessage.set(err?.error?.message || 'No se pudieron cargar las personas');
      },
    });
  }

  protected nombreComunidad(comunidadId: number): string {
    return this.comunidadNombres()[comunidadId] ?? '—';
  }
}
