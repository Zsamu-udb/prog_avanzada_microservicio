// js/services/ReservaService.js
import { ApiClient } from "../core/api.js";

export class ReservaService {
  constructor() {
    this.api = new ApiClient();
  }

  getAll() {
    return this.api.get("/reservas");
  }

  create(data) {
    return this.api.post("/reservas", data);
  }

  update(id, data) {
    return this.api.put(`/reservas/${id}`, data);
  }

  completar(id) {
    return this.api.patch(`/reservas/${id}/completar`, {});
  }

  cancelar(id) {
    return this.api.patch(`/reservas/${id}/cancelar`, {});
  }

  delete(id) {
    return this.api.delete(`/reservas/${id}`);
  }
}