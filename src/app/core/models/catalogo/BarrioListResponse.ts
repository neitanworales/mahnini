import { DefaultResponse } from "../DefaultResponse";
import { Barrio } from "./Barrio";

export class BarrioListResponse extends DefaultResponse {
    data!: {
        items: Barrio[];
    };
}
