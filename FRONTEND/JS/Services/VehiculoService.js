class VehiculoService {
  constructor() {
    this.api = new ApiClient();
  }

  getAll() {
    return this.api.get("/vehiculos");
  }

  getDisponibles() {
    return this.api.get("/vehiculos/disponibles");
  }

  create(data) {
    return this.api.post("/vehiculos", data);
  }

  update(id, data) {
    return this.api.put(`/vehiculos/${id}`, data);
  }

  changeEstado(id, estado) {
    return this.api.patch(`/vehiculos/${id}/estado`, { estado: estado });
  }

  delete(id) {
    return this.api.delete(`/vehiculos/${id}`);
  }
}