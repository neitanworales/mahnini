import { DefaultResponse } from "../DefaultResponse";
import { Barrio } from "./Barrio";

export class BarrioResponse extends DefaultResponse {
    data!: {
        item: Barrio;
    };
}
