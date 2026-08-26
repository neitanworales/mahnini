import { Injectable } from "@angular/core";
import { Observable } from 'rxjs';
import { HttpClient } from "@angular/common/http";
import { Utils } from "./Utils";
import { Persona } from "../models/persona/Persona";
import { PersonaResponse } from "../models/persona/PersonaResponse";
import { PersonaListResponse } from "../models/persona/PersonaListResponse";

@Injectable({
    providedIn: 'root'
})
export class PersonaDao {

    constructor(
        private readonly http: HttpClient,
        private readonly utils: Utils
    ) { }

    public list(comunidadId?: number): Observable<PersonaListResponse> {
        const params: Record<string, number> = {};
        if (comunidadId) {
            params['comunidad_id'] = comunidadId;
        }
        return this.http.get<PersonaListResponse>(this.utils.v1('/personas'), { headers: this.utils.getHeaders(true), params }).pipe();
    }

    public create(persona: Partial<Persona>): Observable<PersonaResponse> {
        return this.http.post<PersonaResponse>(this.utils.v1('/personas'), persona, { headers: this.utils.getHeaders(true) }).pipe();
    }

}
