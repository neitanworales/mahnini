import { Component, HostListener, OnInit, inject, signal } from '@angular/core';
import { Router, RouterLink, RouterLinkActive } from '@angular/router';
import { LoginDao } from '../../core/api/LoginDao';
import { Utils } from '../../core/api/Utils';
import { User } from '../../core/models/login/User';
import { UserRole } from '../../core/models/login/UserRole';
import { Persona } from '../../core/models/persona/Persona';

const ADMIN_ROLES = new Set(['SUPERADMIN', 'ADMIN_COMUNIDAD']);

@Component({
  selector: 'app-header',
  imports: [RouterLink, RouterLinkActive],
  templateUrl: './header.html',
  styleUrl: './header.css',
})
export class Header implements OnInit {
  private readonly loginDao = inject(LoginDao);
  private readonly router = inject(Router);
  private readonly utils = inject(Utils);

  protected readonly isMenuOpen = signal(false);
  protected readonly currentUser = signal<User | null>(null);
  protected readonly currentRole = signal<UserRole | null>(null);
  protected readonly currentPersona = signal<Persona | null>(null);

  ngOnInit(): void {
    const session = this.utils.getSessionFromStorage();
    this.currentUser.set(session?.user ?? null);
    this.currentRole.set(session?.role ?? null);
    this.currentPersona.set(session?.persona ?? null);
  }

  @HostListener('document:click')
  closeMenu(): void {
    this.isMenuOpen.set(false);
  }

  toggleMenu(event: Event): void {
    event.stopPropagation();
    this.isMenuOpen.update((open) => !open);
  }

  stopPropagation(event: Event): void {
    event.stopPropagation();
  }

  protected canManageAdmin(): boolean {
    const role = this.currentRole();
    return !!role && ADMIN_ROLES.has(role.nombre);
  }

  logout(): void {
    const session = this.utils.getSessionFromStorage();

    const finish = () => {
      this.utils.clearSessionFromStorage();
      this.currentUser.set(null);
      this.currentRole.set(null);
      this.currentPersona.set(null);
      this.isMenuOpen.set(false);
      this.router.navigateByUrl('/home');
    };

    if (!session?.token) {
      finish();
      return;
    }

    this.loginDao.logout(session.token).subscribe({ next: finish, error: finish });
  }
}
