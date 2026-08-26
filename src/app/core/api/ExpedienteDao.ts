import { Injectable } from "@angular/core";
import { Observable } from 'rxjs';
import { HttpClient } from "@angular/common/http";
import { Utils } from "./Utils";
import { ExpedienteBusquedaResponse } from "../models/expediente/ExpedienteBusquedaResponse";
import { ExpedienteDetalleResponse } from "../models/expediente/ExpedienteDetalleResponse";
import { TarjetaComunitariaResponse } from "../models/expediente/TarjetaComunitariaResponse";

@Injectable({
    providedIn: 'root'
})
export class ExpedienteDao {

    constructor(
        private readonly http: HttpClient,
        private readonly utils: Utils
    ) { }

    public buscar(query: string): Observable<ExpedienteBusquedaResponse> {
        return this.http.get<ExpedienteBusquedaResponse>(this.utils.v1('/expediente/buscar'), {
            headers: this.utils.getHeaders(true),
            params: { query },
        }).pipe();
    }

    public detalle(personaId: number): Observable<ExpedienteDetalleResponse> {
        return this.http.get<ExpedienteDetalleResponse>(this.utils.v1('/expediente/detalle'), {
            headers: this.utils.getHeaders(true),
            params: { persona_id: personaId },
        }).pipe();
    }

    public tarjeta(personaId: number, anio?: number): Observable<TarjetaComunitariaResponse> {
        const params: Record<string, number> = { persona_id: personaId };
        if (anio) {
            params['anio'] = anio;
        }
        return this.http.get<TarjetaComunitariaResponse>(this.utils.v1('/expediente/tarjeta'), {
            headers: this.utils.getHeaders(true),
            params,
        }).pipe();
    }

}
