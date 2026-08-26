import { Component, inject, signal } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ComunidadDao } from '../../../../core/api/ComunidadDao';

@Component({
  selector: 'app-comunidad-alta',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './comunidad-alta.html',
  styleUrl: './comunidad-alta.css',
})
export class ComunidadAlta {
  private readonly fb = inject(FormBuilder);
  private readonly comunidadDao = inject(ComunidadDao);
  private readonly router = inject(Router);

  protected readonly loading = signal(false);
  protected readonly errorMessage = signal('');
  protected readonly successMessage = signal('');

  protected readonly comunidadForm = this.fb.nonNullable.group({
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

  onSubmit(): void {
    if (this.comunidadForm.invalid || this.loading()) {
      return;
    }

    this.loading.set(true);
    this.errorMessage.set('');
    this.successMessage.set('');

    this.comunidadDao.create(this.comunidadForm.getRawValue()).subscribe({
      next: (response) => {
        this.loading.set(false);
        if (!response.success || !response.data?.item) {
          this.errorMessage.set(response.message || 'No se pudo crear la comunidad');
          return;
        }

        this.successMessage.set(`Comunidad "${response.data.item.nombre}" creada correctamente`);
        this.comunidadForm.reset({ activo: true });
      },
      error: (err) => {
        this.loading.set(false);
        this.errorMessage.set(err?.error?.message || 'No se pudo crear la comunidad');
      },
    });
  }

  onCancel(): void {
    this.router.navigateByUrl('/comunidades');
  }
}
