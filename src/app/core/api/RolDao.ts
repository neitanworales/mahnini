import { Injectable } from "@angular/core";
import { Observable } from 'rxjs';
import { HttpClient } from "@angular/common/http";
import { Utils } from "./Utils";
import { RolListResponse } from "../models/rol/RolListResponse";

@Injectable({
    providedIn: 'root'
})
export class RolDao {

    constructor(
        private readonly http: HttpClient,
        private readonly utils: Utils
    ) { }

    public list(): Observable<RolListResponse> {
        return this.http.get<RolListResponse>(this.utils.v1('/roles'), { headers: this.utils.getHeaders(true) }).pipe();
    }

}
