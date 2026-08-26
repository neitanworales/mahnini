import { Component, OnInit, inject, signal } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Utils } from '../../../../core/api/Utils';
import { ComunidadDao } from '../../../../core/api/ComunidadDao';
import { BarrioDao } from '../../../../core/api/BarrioDao';
import { SeccionDao } from '../../../../core/api/SeccionDao';
import { Comunidad } from '../../../../core/models/comunidad/Comunidad';
import { Barrio } from '../../../../core/models/catalogo/Barrio';
import { Seccion } from '../../../../core/models/catalogo/Seccion';

@Component({
  selector: 'app-comunidad-configuracion',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './comunidad-configuracion.html',
  styleUrl: './comunidad-configuracion.css',
})
export class ComunidadConfiguracion implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly fb = inject(FormBuilder);
  private readonly utils = inject(Utils);
  private readonly comunidadDao = inject(ComunidadDao);
  private readonly barrioDao = inject(BarrioDao);
  private readonly seccionDao = inject(SeccionDao);

  protected readonly comunidadId = signal<number | null>(null);
  protected readonly comunidad = signal<Comunidad | null>(null);
  protected readonly barrios = signal<Barrio[]>([]);
  protected readonly secciones = signal<Seccion[]>([]);
  protected readonly errorMessage = signal('');
  protected readonly errorBarrio = signal('');
  protected readonly errorSeccion = signal('');
  protected readonly volverRuta = signal('/dashboard');

  protected readonly datosLoading = signal(false);
  protected readonly datosError = signal('');
  protected readonly datosGuardado = signal('');

  protected readonly datosForm = this.fb.nonNullable.group({
    clave: ['', [Validators.required, Validators.maxLength(30)]],
    nombre: ['', [Validators.required, Validators.maxLength(150)]],
    municipio: ['', [Validators.maxLength(120)]],
    estado: ['', [Validators.maxLength(120)]],
    direccion: ['', [Validators.maxLength(255)]],
    telefono: ['', [Validators.maxLength(30)]],
    email: ['', [Validators.email, Validators.maxLength(150)]],
    codigoPostal: ['', [Validators.maxLength(10)]],
    descripcion: ['', [Validators.maxLength(2000)]],
    activo: [true],
  });

  protected readonly barrioForm = this.fb.nonNullable.group({
    nombre: ['', [Validators.required, Validators.maxLength(120)]],
  });

  protected readonly seccionForm = this.fb.nonNullable.group({
    nombre: ['', [Validators.required, Validators.maxLength(120)]],
  });

  ngOnInit(): void {
    const session = this.utils.getSessionFromStorage();
    const esSuperAdmin = session?.role?.nombre === 'SUPERADMIN';
    const idParam = Number(this.route.snapshot.paramMap.get('id'));
    this.volverRuta.set(esSuperAdmin ? '/comunidades' : '/dashboard');

    if (!esSuperAdmin) {
      const propioId = session?.user?.comunidad_id ?? null;
      if (!propioId) {
        this.errorMessage.set('Tu usuario no tiene una comunidad asignada');
        return;
      }
      if (idParam !== propioId) {
        this.router.navigate(['/comunidades', propioId, 'configurar']);
        return;
      }
    }

    if (!idParam) {
      this.errorMessage.set('Comunidad inválida');
      return;
    }

    this.comunidadId.set(idParam);

    this.comunidadDao.detalle(idParam).subscribe({
      next: (response) => {
        const item = response.data?.item ?? null;
        this.comunidad.set(item);
        if (item) {
          this.datosForm.reset({
            clave: item.clave,
            nombre: item.nombre,
            municipio: item.municipio ?? '',
            estado: item.estado ?? '',
            direccion: item.direccion ?? '',
            telefono: item.telefono ?? '',
            email: item.email ?? '',
            codigoPostal: item.codigoPostal ?? '',
            descripcion: item.descripcion ?? '',
            activo: item.activo ?? true,
          });
        }
      },
      error: () => this.comunidad.set(null),
    });

    this.barrioDao.list(idParam).subscribe({
      next: (response) => this.barrios.set(response.data?.items ?? []),
      error: () => this.barrios.set([]),
    });

    this.seccionDao.list(idParam).subscribe({
      next: (response) => this.secciones.set(response.data?.items ?? []),
      error: () => this.secciones.set([]),
    });
  }

  agregarBarrio(): void {
    const comunidadId = this.comunidadId();
    if (this.barrioForm.invalid || !comunidadId) {
      return;
    }

    this.errorBarrio.set('');
    const nombre = this.barrioForm.getRawValue().nombre;
    this.barrioDao.create({ comunidadId, nombre }).subscribe({
      next: (response) => {
        if (!response.success || !response.data?.item) {
          this.errorBarrio.set(response.message || 'No se pudo agregar el barrio');
          return;
        }
        this.barrios.update((items) => [...items, response.data.item]);
        this.barrioForm.reset();
      },
      error: (err) => this.errorBarrio.set(err?.error?.message || 'No se pudo agregar el barrio'),
    });
  }

  eliminarBarrio(barrio: Barrio): void {
    this.barrioDao.remove(barrio.id).subscribe({
      next: (response) => {
        if (response.success) {
          this.barrios.update((items) => items.filter((item) => item.id !== barrio.id));
        }
      },
    });
  }

  agregarSeccion(): void {
    const comunidadId = this.comunidadId();
    if (this.seccionForm.invalid || !comunidadId) {
      return;
    }

    this.errorSeccion.set('');
    const nombre = this.seccionForm.getRawValue().nombre;
    this.seccionDao.create({ comunidadId, nombre }).subscribe({
      next: (response) => {
        if (!response.success || !response.data?.item) {
          this.errorSeccion.set(response.message || 'No se pudo agregar la sección');
          return;
        }
        this.secciones.update((items) => [...items, response.data.item]);
        this.seccionForm.reset();
      },
      error: (err) => this.errorSeccion.set(err?.error?.message || 'No se pudo agregar la sección'),
    });
  }

  eliminarSeccion(seccion: Seccion): void {
    this.seccionDao.remove(seccion.id).subscribe({
      next: (response) => {
        if (response.success) {
          this.secciones.update((items) => items.filter((item) => item.id !== seccion.id));
        }
      },
    });
  }

  guardarDatos(): void {
    const comunidadId = this.comunidadId();
    if (this.datosForm.invalid || !comunidadId || this.datosLoading()) {
      return;
    }

    this.datosLoading.set(true);
    this.datosError.set('');
    this.datosGuardado.set('');

    this.comunidadDao.update(comunidadId, this.datosForm.getRawValue()).subscribe({
      next: (response) => {
        this.datosLoading.set(false);
        if (!response.success || !response.data?.item) {
          this.datosError.set(response.message || 'No se pudo guardar la comunidad');
          return;
        }
        this.comunidad.set(response.data.item);
        this.datosGuardado.set('Datos guardados correctamente');
      },
      error: (err) => {
        this.datosLoading.set(false);
        this.datosError.set(err?.error?.message || 'No se pudo guardar la comunidad');
      },
    });
  }
}
