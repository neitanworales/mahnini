import { Injectable } from "@angular/core";
import { Observable } from 'rxjs';
import { HttpClient } from "@angular/common/http";
import { Utils } from "./Utils";
import { Usuario } from "../models/usuario/Usuario";
import { UsuarioResponse } from "../models/usuario/UsuarioResponse";
import { UsuarioListResponse } from "../models/usuario/UsuarioListResponse";

@Injectable({
    providedIn: 'root'
})
export class UsuarioDao {

    constructor(
        private readonly http: HttpClient,
        private readonly utils: Utils
    ) { }

    public list(): Observable<UsuarioListResponse> {
        return this.http.get<UsuarioListResponse>(this.utils.v1('/usuarios'), { headers: this.utils.getHeaders(true) }).pipe();
    }

    public create(usuario: Partial<Usuario> & { password: string }): Observable<UsuarioResponse> {
        return this.http.post<UsuarioResponse>(this.utils.v1('/usuarios'), usuario, { headers: this.utils.getHeaders(true) }).pipe();
    }

}
