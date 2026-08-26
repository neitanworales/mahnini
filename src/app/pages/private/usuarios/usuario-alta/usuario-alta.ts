import { Component, OnInit, inject, signal } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { UsuarioDao } from '../../../../core/api/UsuarioDao';
import { ComunidadDao } from '../../../../core/api/ComunidadDao';
import { PersonaDao } from '../../../../core/api/PersonaDao';
import { RolDao } from '../../../../core/api/RolDao';
import { Comunidad } from '../../../../core/models/comunidad/Comunidad';
import { Persona } from '../../../../core/models/persona/Persona';
import { Rol } from '../../../../core/models/rol/Rol';

@Component({
  selector: 'app-usuario-alta',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './usuario-alta.html',
  styleUrl: './usuario-alta.css',
})
export class UsuarioAlta implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly usuarioDao = inject(UsuarioDao);
  private readonly comunidadDao = inject(ComunidadDao);
  private readonly personaDao = inject(PersonaDao);
  private readonly rolDao = inject(RolDao);
  private readonly router = inject(Router);

  protected readonly loading = signal(false);
  protected readonly errorMessage = signal('');
  protected readonly successMessage = signal('');
  protected readonly comunidades = signal<Comunidad[]>([]);
  protected readonly personas = signal<Persona[]>([]);
  protected readonly roles = signal<Rol[]>([]);

  protected readonly usuarioForm = this.fb.nonNullable.group({
    comunidadId: [null as number | null, [Validators.required]],
    rolId: [null as number | null, [Validators.required]],
    personaId: [null as number | null],
    email: ['', [Validators.required, Validators.email, Validators.maxLength(150)]],
    password: ['', [Validators.required, Validators.pattern(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\sA-Za-z0-9])\S{12,}$/)]],
    activo: [true],
  });

  ngOnInit(): void {
    this.comunidadDao.list().subscribe({
      next: (response) => this.comunidades.set(response.data?.items ?? []),
      error: () => this.comunidades.set([]),
    });

    this.rolDao.list().subscribe({
      next: (response) => this.roles.set(response.data?.items ?? []),
      error: () => this.roles.set([]),
    });

    this.usuarioForm.controls.comunidadId.valueChanges.subscribe((comunidadId) => {
      this.usuarioForm.controls.personaId.setValue(null);
      this.personas.set([]);

      if (!comunidadId) {
        return;
      }

      this.personaDao.list(comunidadId).subscribe({
        next: (response) => this.personas.set(response.data?.items ?? []),
        error: () => this.personas.set([]),
      });
    });
  }

  onSubmit(): void {
    if (this.usuarioForm.invalid || this.loading()) {
      return;
    }

    this.loading.set(true);
    this.errorMessage.set('');
    this.successMessage.set('');

    const { comunidadId, rolId, personaId, ...rest } = this.usuarioForm.getRawValue();
    this.usuarioDao.create({
      ...rest,
      comunidadId: comunidadId!,
      rolId: rolId!,
      personaId: personaId ?? undefined,
    }).subscribe({
      next: (response) => {
        this.loading.set(false);
        if (!response.success || !response.data?.item) {
          this.errorMessage.set(response.message || 'No se pudo crear el usuario');
          return;
        }

        this.successMessage.set(`Usuario "${response.data.item.email}" creado correctamente`);
        this.usuarioForm.reset({ activo: true });
        this.personas.set([]);
      },
      error: (err) => {
        this.loading.set(false);
        this.errorMessage.set(err?.error?.message || 'No se pudo crear el usuario');
      },
    });
  }

  onCancel(): void {
    this.router.navigateByUrl('/usuarios');
  }
}
