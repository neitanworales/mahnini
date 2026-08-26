import { Component, OnInit, inject, signal } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { PersonaDao } from '../../../../core/api/PersonaDao';
import { ComunidadDao } from '../../../../core/api/ComunidadDao';
import { BarrioDao } from '../../../../core/api/BarrioDao';
import { SeccionDao } from '../../../../core/api/SeccionDao';
import { Utils } from '../../../../core/api/Utils';
import { Comunidad } from '../../../../core/models/comunidad/Comunidad';
import { Barrio } from '../../../../core/models/catalogo/Barrio';
import { Seccion } from '../../../../core/models/catalogo/Seccion';

const ESTATUS_OPTIONS = ['ACTIVO', 'INACTIVO', 'FALLECIDO', 'BAJA'];

@Component({
  selector: 'app-persona-alta',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './persona-alta.html',
  styleUrl: './persona-alta.css',
})
export class PersonaAlta implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly personaDao = inject(PersonaDao);
  private readonly comunidadDao = inject(ComunidadDao);
  private readonly barrioDao = inject(BarrioDao);
  private readonly seccionDao = inject(SeccionDao);
  private readonly utils = inject(Utils);
  private readonly router = inject(Router);

  protected readonly loading = signal(false);
  protected readonly errorMessage = signal('');
  protected readonly successMessage = signal('');
  protected readonly comunidades = signal<Comunidad[]>([]);
  protected readonly barrios = signal<Barrio[]>([]);
  protected readonly secciones = signal<Seccion[]>([]);
  protected readonly estatusOptions = ESTATUS_OPTIONS;
  protected readonly isSuperAdmin = signal(false);
  protected readonly comunidadNombre = signal('');

  protected readonly personaForm = this.fb.nonNullable.group({
    comunidadId: [null as number | null, [Validators.required]],
    numeroControl: ['', [Validators.required, Validators.maxLength(40)]],
    nombre: ['', [Validators.required, Validators.maxLength(100)]],
    apellidoPaterno: ['', [Validators.maxLength(100)]],
    apellidoMaterno: ['', [Validators.maxLength(100)]],
    fechaNacimiento: [''],
    telefono: ['', [Validators.maxLength(30)]],
    email: ['', [Validators.email, Validators.maxLength(150)]],
    direccion: ['', [Validators.maxLength(255)]],
    barrio: ['', [Validators.maxLength(120)]],
    seccion: ['', [Validators.maxLength(120)]],
    fechaAlta: [''],
    estatus: ['ACTIVO', [Validators.required]],
    observaciones: [''],
  });

  ngOnInit(): void {
    const session = this.utils.getSessionFromStorage();
    const esSuperAdmin = session?.role?.nombre === 'SUPERADMIN';
    this.isSuperAdmin.set(esSuperAdmin);

    this.comunidadDao.list().subscribe({
      next: (response) => {
        const items = response.data?.items ?? [];
        this.comunidades.set(items);

        if (!esSuperAdmin) {
          const comunidadId = session?.user?.comunidad_id ?? null;
          this.comunidadNombre.set(items.find((comunidad) => comunidad.id === comunidadId)?.nombre ?? '');
        }
      },
      error: () => this.comunidades.set([]),
    });

    if (!esSuperAdmin) {
      // La comunidad no es editable: se fija a la del usuario logeado y viaja igual en getRawValue().
      const comunidadId = session?.user?.comunidad_id ?? null;
      this.personaForm.controls.comunidadId.setValue(comunidadId);
      this.personaForm.controls.comunidadId.disable();

      if (comunidadId) {
        this.cargarCatalogos(comunidadId);
      }
    } else {
      this.personaForm.controls.comunidadId.valueChanges.subscribe((comunidadId) => {
        this.personaForm.patchValue({ barrio: '', seccion: '' });
        this.barrios.set([]);
        this.secciones.set([]);

        if (comunidadId) {
          this.cargarCatalogos(comunidadId);
        }
      });
    }
  }

  private cargarCatalogos(comunidadId: number): void {
    this.barrioDao.list(comunidadId).subscribe({
      next: (response) => this.barrios.set(response.data?.items ?? []),
      error: () => this.barrios.set([]),
    });

    this.seccionDao.list(comunidadId).subscribe({
      next: (response) => this.secciones.set(response.data?.items ?? []),
      error: () => this.secciones.set([]),
    });
  }

  onSubmit(): void {
    if (this.personaForm.invalid || this.loading()) {
      return;
    }

    this.loading.set(true);
    this.errorMessage.set('');
    this.successMessage.set('');

    const { comunidadId, ...rest } = this.personaForm.getRawValue();
    this.personaDao.create({ ...rest, comunidadId: comunidadId! }).subscribe({
      next: (response) => {
        this.loading.set(false);
        if (!response.success || !response.data?.item) {
          this.errorMessage.set(response.message || 'No se pudo crear la persona');
          return;
        }

        this.successMessage.set(`Persona "${response.data.item.nombre}" creada correctamente`);
        this.personaForm.reset({ estatus: 'ACTIVO' });
      },
      error: (err) => {
        this.loading.set(false);
        this.errorMessage.set(err?.error?.message || 'No se pudo crear la persona');
      },
    });
  }

  onCancel(): void {
    this.router.navigateByUrl('/personas');
  }
}
