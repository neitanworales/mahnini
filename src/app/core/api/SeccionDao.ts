import { Injectable } from "@angular/core";
import { Observable } from 'rxjs';
import { HttpClient } from "@angular/common/http";
import { Utils } from "./Utils";
import { Seccion } from "../models/catalogo/Seccion";
import { SeccionResponse } from "../models/catalogo/SeccionResponse";
import { SeccionListResponse } from "../models/catalogo/SeccionListResponse";
import { DefaultResponse } from "../models/DefaultResponse";

@Injectable({
    providedIn: 'root'
})
export class SeccionDao {

    constructor(
        private readonly http: HttpClient,
        private readonly utils: Utils
    ) { }

    public list(comunidadId: number): Observable<SeccionListResponse> {
        return this.http.get<SeccionListResponse>(this.utils.v1('/secciones'), {
            headers: this.utils.getHeaders(true),
            params: { comunidad_id: comunidadId },
        }).pipe();
    }

    public create(seccion: Partial<Seccion>): Observable<SeccionResponse> {
        return this.http.post<SeccionResponse>(this.utils.v1('/secciones'), seccion, { headers: this.utils.getHeaders(true) }).pipe();
    }

    public remove(id: number): Observable<DefaultResponse> {
        return this.http.delete<DefaultResponse>(this.utils.v1('/secciones'), {
            headers: this.utils.getHeaders(true),
            params: { id },
        }).pipe();
    }

}
