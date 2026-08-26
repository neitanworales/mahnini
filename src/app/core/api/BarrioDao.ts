import { Injectable } from "@angular/core";
import { Observable } from 'rxjs';
import { HttpClient } from "@angular/common/http";
import { Utils } from "./Utils";
import { Barrio } from "../models/catalogo/Barrio";
import { BarrioResponse } from "../models/catalogo/BarrioResponse";
import { BarrioListResponse } from "../models/catalogo/BarrioListResponse";
import { DefaultResponse } from "../models/DefaultResponse";

@Injectable({
    providedIn: 'root'
})
export class BarrioDao {

    constructor(
        private readonly http: HttpClient,
        private readonly utils: Utils
    ) { }

    public list(comunidadId: number): Observable<BarrioListResponse> {
        return this.http.get<BarrioListResponse>(this.utils.v1('/barrios'), {
            headers: this.utils.getHeaders(true),
            params: { comunidad_id: comunidadId },
        }).pipe();
    }

    public create(barrio: Partial<Barrio>): Observable<BarrioResponse> {
        return this.http.post<BarrioResponse>(this.utils.v1('/barrios'), barrio, { headers: this.utils.getHeaders(true) }).pipe();
    }

    public remove(id: number): Observable<DefaultResponse> {
        return this.http.delete<DefaultResponse>(this.utils.v1('/barrios'), {
            headers: this.utils.getHeaders(true),
            params: { id },
        }).pipe();
    }

}
