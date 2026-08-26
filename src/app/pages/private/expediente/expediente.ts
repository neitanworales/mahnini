import { Component, inject, signal } from '@angular/core';
import { CurrencyPipe } from '@angular/common';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { of } from 'rxjs';
import { debounceTime, distinctUntilChanged, switchMap } from 'rxjs/operators';
import { ExpedienteDao } from '../../../core/api/ExpedienteDao';
import { Persona } from '../../../core/models/persona/Persona';
import { Cargo } from '../../../core/models/expediente/Cargo';
import { Pago } from '../../../core/models/expediente/Pago';
import { ExpedienteResumen } from '../../../core/models/expediente/ExpedienteResumen';
import { TarjetaReunion } from '../../../core/models/expediente/TarjetaReunion';
import { TarjetaFaena } from '../../../core/models/expediente/TarjetaFaena';
import { TarjetaCooperacion } from '../../../core/models/expediente/TarjetaCooperacion';
import { TarjetaObra } from '../../../core/models/expediente/TarjetaObra';

@Component({
  selector: 'app-expediente',
  imports: [ReactiveFormsModule, CurrencyPipe],
  templateUrl: './expediente.html',
  styleUrl: './expediente.css',
})
export class Expediente {
  private readonly expedienteDao = inject(ExpedienteDao);

  protected readonly searchControl = new FormControl('', { nonNullable: true });
  protected readonly resultados = signal<Persona[]>([]);
  protected readonly buscando = signal(false);
  protected readonly errorBusqueda = signal('');

  protected readonly personaSeleccionada = signal<Persona | null>(null);
  protected readonly resumen = signal<ExpedienteResumen | null>(null);
  protected readonly pagos = signal<Pago[]>([]);
  protected readonly adeudos = signal<Cargo[]>([]);
  protected readonly exentos = signal<Cargo[]>([]);
  protected readonly cargandoDetalle = signal(false);
  protected readonly errorDetalle = signal('');

  protected readonly mostrarTarjeta = signal(false);
  protected readonly cargandoTarjeta = signal(false);
  protected readonly errorTarjeta = signal('');
  protected readonly tarjetaAnio = signal<number | null>(null);
  protected readonly tarjetaReuniones = signal<TarjetaReunion[]>([]);
  protected readonly tarjetaFaenas = signal<TarjetaFaena[]>([]);
  protected readonly tarjetaCooperacion = signal<TarjetaCooperacion | null>(null);
  protected readonly tarjetaObras = signal<TarjetaObra[]>([]);

  constructor() {
    this.searchControl.valueChanges
      .pipe(
        debounceTime(300),
        distinctUntilChanged(),
        switchMap((query) => {
          const trimmed = query.trim();
          if (trimmed.length < 2) {
            this.resultados.set([]);
            this.errorBusqueda.set('');
            return of(null);
          }

          this.buscando.set(true);
          this.errorBusqueda.set('');
          return this.expedienteDao.buscar(trimmed);
        }),
      )
      .subscribe({
        next: (response) => {
          this.buscando.set(false);
          if (!response) {
            return;
          }
          if (!response.success) {
            this.errorBusqueda.set(response.message || 'No se pudo realizar la búsqueda');
            this.resultados.set([]);
            return;
          }
          this.resultados.set(response.data?.items ?? []);
        },
        error: (err) => {
          this.buscando.set(false);
          this.errorBusqueda.set(err?.error?.message || 'No se pudo realizar la búsqueda');
          this.resultados.set([]);
        },
      });
  }

  seleccionar(persona: Persona): void {
    this.personaSeleccionada.set(persona);
    this.resultados.set([]);
    this.searchControl.setValue('', { emitEvent: false });
    this.cargandoDetalle.set(true);
    this.errorDetalle.set('');

    this.expedienteDao.detalle(persona.id).subscribe({
      next: (response) => {
        this.cargandoDetalle.set(false);
        if (!response.success || !response.data) {
          this.errorDetalle.set(response.message || 'No se pudo cargar el expediente');
          return;
        }

        this.resumen.set(response.data.resumen);
        this.pagos.set(response.data.pagos ?? []);
        this.adeudos.set(response.data.adeudos ?? []);
        this.exentos.set(response.data.exentos ?? []);
      },
      error: (err) => {
        this.cargandoDetalle.set(false);
        this.errorDetalle.set(err?.error?.message || 'No se pudo cargar el expediente');
      },
    });
  }

  limpiarSeleccion(): void {
    this.personaSeleccionada.set(null);
    this.resumen.set(null);
    this.pagos.set([]);
    this.adeudos.set([]);
    this.exentos.set([]);
    this.mostrarTarjeta.set(false);
    this.tarjetaReuniones.set([]);
    this.tarjetaFaenas.set([]);
    this.tarjetaCooperacion.set(null);
    this.tarjetaObras.set([]);
  }

  toggleTarjeta(): void {
    const persona = this.personaSeleccionada();
    if (!persona) {
      return;
    }

    if (this.mostrarTarjeta()) {
      this.mostrarTarjeta.set(false);
      return;
    }

    this.mostrarTarjeta.set(true);
    this.cargandoTarjeta.set(true);
    this.errorTarjeta.set('');

    this.expedienteDao.tarjeta(persona.id).subscribe({
      next: (response) => {
        this.cargandoTarjeta.set(false);
        if (!response.success || !response.data) {
          this.errorTarjeta.set(response.message || 'No se pudo cargar la tarjeta comunitaria');
          return;
        }

        this.tarjetaAnio.set(response.data.anio);
        this.tarjetaReuniones.set(response.data.reuniones ?? []);
        this.tarjetaFaenas.set(response.data.faenas ?? []);
        this.tarjetaCooperacion.set(response.data.cooperacionAnual ?? null);
        this.tarjetaObras.set(response.data.obras ?? []);
      },
      error: (err) => {
        this.cargandoTarjeta.set(false);
        this.errorTarjeta.set(err?.error?.message || 'No se pudo cargar la tarjeta comunitaria');
      },
    });
  }

  imprimirTarjeta(): void {
    window.print();
  }
}
