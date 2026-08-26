import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { ComunidadDao } from '../../../../core/api/ComunidadDao';
import { Comunidad } from '../../../../core/models/comunidad/Comunidad';

type FiltroEstatus = 'TODAS' | 'ACTIVAS' | 'INACTIVAS';

@Component({
  selector: 'app-comunidad-listado',
  imports: [RouterLink],
  templateUrl: './comunidad-listado.html',
  styleUrl: './comunidad-listado.css',
})
export class ComunidadListado implements OnInit {
  private readonly comunidadDao = inject(ComunidadDao);

  protected readonly comunidades = signal<Comunidad[]>([]);
  protected readonly loading = signal(true);
  protected readonly errorMessage = signal('');
  protected readonly busqueda = signal('');
  protected readonly filtroEstatus = signal<FiltroEstatus>('TODAS');

  protected readonly comunidadesFiltradas = computed(() => {
    const termino = this.busqueda().trim().toLowerCase();
    const estatus = this.filtroEstatus();

    return this.comunidades().filter((comunidad) => {
      if (estatus === 'ACTIVAS' && !comunidad.activo) {
        return false;
      }
      if (estatus === 'INACTIVAS' && comunidad.activo) {
        return false;
      }
      if (termino === '') {
        return true;
      }

      return [comunidad.clave, comunidad.nombre, comunidad.municipio, comunidad.estado]
        .some((campo) => (campo ?? '').toLowerCase().includes(termino));
    });
  });

  ngOnInit(): void {
    this.comunidadDao.list().subscribe({
      next: (response) => {
        this.loading.set(false);
        if (!response.success) {
          this.errorMessage.set(response.message || 'No se pudieron cargar las comunidades');
          return;
        }
        this.comunidades.set(response.data?.items ?? []);
      },
      error: (err) => {
        this.loading.set(false);
        this.errorMessage.set(err?.error?.message || 'No se pudieron cargar las comunidades');
      },
    });
  }
}
