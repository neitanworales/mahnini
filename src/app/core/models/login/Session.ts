import { User } from "./User";
import { UserRole } from "./UserRole";
import { Persona } from "../persona/Persona";

export class Session {
    token!: string;
    expires_at!: string;
    user!: User;
    role?: UserRole;
    persona?: Persona;
}
