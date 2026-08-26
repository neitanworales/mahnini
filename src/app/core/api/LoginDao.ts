import { Injectable } from "@angular/core";
import { Observable } from 'rxjs';
import { Utils } from "./Utils";
import { LoginResponse } from "../models/login/LoginResponse";
import { HttpClient } from "@angular/common/http";

@Injectable({
    providedIn: 'root'
})
export class LoginDao {

    constructor(
        private readonly http: HttpClient,
        private readonly utils: Utils
    ) { }

    public login(email: string, password: string): Observable<LoginResponse> {
        const body = {
            email: email.toString(),
            password: password.toString()
        };

        return this.http.post<LoginResponse>(this.utils.v1('/auth/login'), body, { headers: this.utils.getHeaders(false) }).pipe();
    }

    public logout(token: string): Observable<LoginResponse> {
        const body = { token };
        return this.http.post<LoginResponse>(this.utils.v1('/auth/logout'), body, { headers: this.utils.getHeaders(false) }).pipe();
    }

}