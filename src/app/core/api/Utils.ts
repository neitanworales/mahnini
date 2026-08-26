import { HttpHeaders } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { Router } from "@angular/router";
import { environment } from "../../../environments/environment";
import { Session } from "../models/login/Session";

const SESSION_STORAGE_KEY = 'currentUser';

@Injectable({
    providedIn: 'root'
})
export class Utils {

    constructor(
        private readonly router: Router
    ) { }

    public getHeaders(withToken: boolean): HttpHeaders {
        const headersConfig: any = {
            'Content-Type': 'application/json'
        };
        if (withToken) {
            const session = this.getSessionFromStorage();
            headersConfig['Authorization'] = `Bearer ${session?.token}`;
        }
        return new HttpHeaders(headersConfig);
    }

    public getSessionFromStorage(): Session | undefined {
        const raw = localStorage.getItem(SESSION_STORAGE_KEY);
        if (raw == null) {
            return undefined;
        }
        try {
            return JSON.parse(raw) as Session;
        } catch {
            return undefined;
        }
    }

    public setSessionInStorage(session: Session): void {
        localStorage.setItem(SESSION_STORAGE_KEY, JSON.stringify(session));
    }

    public clearSessionFromStorage(): void {
        localStorage.removeItem(SESSION_STORAGE_KEY);
    }

    public hasValidSession(): boolean {
        return !!this.getSessionFromStorage()?.token;
    }

    public requireSessionOrRedirect(): Session | undefined {
        const session = this.getSessionFromStorage();
        if (!session) {
            this.router.navigate(["/login"]);
        }
        return session;
    }

    public v1(path: string): string {
        const apiUrl = environment.apiUrl || '';
        let end = apiUrl.length;
        while (end > 0 && apiUrl.codePointAt(end - 1) === 47) {
            end--;
        }
        const base = apiUrl.slice(0, end);
        return `${base}${path}`;
    }
}