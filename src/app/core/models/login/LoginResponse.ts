import { DefaultResponse } from "../DefaultResponse";
import { Session } from "./Session";

export class LoginResponse extends DefaultResponse {
    data!: Session;
}