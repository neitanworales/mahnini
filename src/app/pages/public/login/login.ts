import { Component, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { LoginDao } from '../../../core/api/LoginDao';
import { Utils } from '../../../core/api/Utils';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule],
  templateUrl: './login.html',
  styleUrl: './login.css',
})
export class Login {
  private readonly fb = inject(FormBuilder);
  private readonly loginDao = inject(LoginDao);
  private readonly router = inject(Router);
  private readonly utils = inject(Utils);

  protected readonly loading = signal(false);
  protected readonly errorMessage = signal('');

  protected readonly loginForm = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required]],
  });

  onSubmit(): void {
    if (this.loginForm.invalid || this.loading()) {
      return;
    }

    const { email, password } = this.loginForm.getRawValue();
    this.loading.set(true);
    this.errorMessage.set('');

    this.loginDao.login(email, password).subscribe({
      next: (response) => {
        this.loading.set(false);
        if (!response.success || !response.data?.user) {
          this.errorMessage.set(response.message || 'Credenciales inválidas');
          return;
        }

        this.utils.setSessionInStorage(response.data);
        this.router.navigateByUrl('/dashboard');
      },
      error: (err) => {
        this.loading.set(false);
        this.errorMessage.set(err?.error?.message || 'No se pudo iniciar sesión');
      },
    });
  }
}
