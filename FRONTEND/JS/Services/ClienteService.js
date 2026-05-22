class ClienteService {
  constructor() {
    this.api = new ApiClient();
  }

  getAll() {
    return this.api.get("/clientes");
  }

  create(data) {
    return this.api.post("/clientes", data);
  }

  update(id, data) {
    return this.api.put(`/clientes/${id}`, data);
  }

  delete(id) {
    return this.api.delete(`/clientes/${id}`);
  }
}