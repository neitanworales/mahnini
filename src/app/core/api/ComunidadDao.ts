import { Injectable } from "@angular/core";
import { Observable } from 'rxjs';
import { HttpClient } from "@angular/common/http";
import { Utils } from "./Utils";
import { Comunidad } from "../models/comunidad/Comunidad";
import { ComunidadResponse } from "../models/comunidad/ComunidadResponse";
import { ComunidadListResponse } from "../models/comunidad/ComunidadListResponse";
import { ComunidadDetailResponse } from "../models/comunidad/ComunidadDetailResponse";

@Injectable({
    providedIn: 'root'
})
export class ComunidadDao {

    constructor(
        private readonly http: HttpClient,
        private readonly utils: Utils
    ) { }

    public list(): Observable<ComunidadListResponse> {
        return this.http.get<ComunidadListResponse>(this.utils.v1('/comunidades'), { headers: this.utils.getHeaders(true) }).pipe();
    }

    public detalle(id: number): Observable<ComunidadDetailResponse> {
        return this.http.get<ComunidadDetailResponse>(this.utils.v1('/comunidades/detail'), {
            headers: this.utils.getHeaders(true),
            params: { id },
        }).pipe();
    }

    public create(comunidad: Partial<Comunidad>): Observable<ComunidadResponse> {
        return this.http.post<ComunidadResponse>(this.utils.v1('/comunidades'), comunidad, { headers: this.utils.getHeaders(true) }).pipe();
    }

    public update(id: number, comunidad: Partial<Comunidad>): Observable<ComunidadResponse> {
        return this.http.put<ComunidadResponse>(this.utils.v1('/comunidades'), { ...comunidad, id }, { headers: this.utils.getHeaders(true) }).pipe();
    }

}
