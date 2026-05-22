const vehiculosGrid = document.getElementById("vehiculosGrid");

const normalizarVehiculo = (item) => ({
  id: item.id,
  marca: item.marca ?? "",
  modelo: item.modelo ?? "",
  anio: item.anio ?? item.año ?? "",
  categoria: item.categoria ?? "",
  estado: item.estado ?? "disponible",
  imagen: item.imagen ?? item.imagen_url ?? "",
});

const renderVehiculos = () => {
  if (!vehiculosGrid) return;

  vehiculosGrid.innerHTML = "";

  if (appState.vehiculos.length === 0) {
    vehiculosGrid.appendChild(
      createEmptyState(
        "Aún no hay vehículos registrados",
        "Aquí aparecerá la flota visual de AllCars con imagen, estado y acciones rápidas."
      )
    );
    updateDashboardCounts();
    actualizarSelectVehiculosReserva();
    return;
  }

  appState.vehiculos.forEach((vehiculo) => {
    const article = document.createElement("article");
    article.className = "vehicle-card";

    const imageSrc = vehiculo.imagen || getVehicleImageByCategory(vehiculo.categoria);
    const statusMeta = getVehicleStatusMeta(vehiculo.estado);

    article.innerHTML = `
      <img
        src="${escapeHtml(imageSrc)}"
        alt="Vehículo ${escapeHtml(vehiculo.marca)} ${escapeHtml(vehiculo.modelo)}"
        width="1200"
        height="800"
        loading="lazy"
      />
      <div class="vehicle-card__body">
        <div class="vehicle-card__top">
          <div class="vehicle-card__title-wrap">
            <h5 class="vehicle-card__title">${escapeHtml(vehiculo.marca)} ${escapeHtml(vehiculo.modelo)}</h5>
            <span class="vehicle-card__subtitle">${escapeHtml(vehiculo.categoria)}</span>
          </div>
          <span class="status-badge ${statusMeta.className}">
            ${escapeHtml(statusMeta.label)}
          </span>
        </div>

        <div class="vehicle-card__meta">
          <div class="vehicle-meta">
            <span class="vehicle-meta__label">Año</span>
            <span class="vehicle-meta__value">${escapeHtml(vehiculo.anio)}</span>
          </div>
          <div class="vehicle-meta">
            <span class="vehicle-meta__label">Categoría</span>
            <span class="vehicle-meta__value">${escapeHtml(vehiculo.categoria)}</span>
          </div>
          <div class="vehicle-meta">
            <span class="vehicle-meta__label">Estado</span>
            <span class="vehicle-meta__value">${escapeHtml(statusMeta.label)}</span>
          </div>
        </div>

        <div class="vehicle-card__actions"></div>
      </div>
    `;

    const actionsWrap = article.querySelector(".vehicle-card__actions");

    const editButton = createActionButton({
      text: "Editar",
      className: "action-btn--edit",
      title: `Editar ${vehiculo.marca} ${vehiculo.modelo}`,
      onClick: () => editarVehiculo(vehiculo.id),
    });

    const deleteButton = createActionButton({
      text: "Borrar",
      className: "action-btn--delete",
      title: `Borrar ${vehiculo.marca} ${vehiculo.modelo}`,
      onClick: () => borrarVehiculo(vehiculo.id),
    });

    const nextStatusButton = createActionButton({
      text: getNextVehicleStatusLabel(vehiculo.estado),
      className: "action-btn--success",
      title: `Cambiar estado de ${vehiculo.marca} ${vehiculo.modelo}`,
      onClick: () => cambiarEstadoVehiculo(vehiculo),
    });

    actionsWrap.appendChild(editButton);
    actionsWrap.appendChild(deleteButton);
    actionsWrap.appendChild(nextStatusButton);

    vehiculosGrid.appendChild(article);
  });

  updateDashboardCounts();
  actualizarSelectVehiculosReserva();
};

const consultarVehiculos = async () => {
  try {
    const data = await apiGet("/vehiculos");
    appState.vehiculos = Array.isArray(data) ? data.map(normalizarVehiculo) : [];
    renderVehiculos();
  } catch (error) {
    console.error(error);
    showModal("No fue posible cargar los vehículos.", "error");
  }
};

const editarVehiculo = (id) => {
  const vehiculo = appState.vehiculos.find((item) => Number(item.id) === Number(id));

  if (!vehiculo) {
    showModal("No se encontró el vehículo seleccionado.", "error");
    return;
  }

  appState.vehiculoEditando = vehiculo;
  fillVehiculoForm(vehiculo);
  activateTab("tab-vehiculos");
  document.getElementById("vehiculoMarca")?.focus();
};

const borrarVehiculo = async (id) => {
  const confirmado = window.confirm("¿Deseas borrar este vehículo?");
  if (!confirmado) return;

  try {
    await apiDelete(`/vehiculos/${id}`);
    appState.vehiculos = appState.vehiculos.filter(
      (item) => Number(item.id) !== Number(id)
    );
    renderVehiculos();

    // Igual que en clientes: primero reset del form, luego limpiar estado
    vehiculoForm?.reset();
    resetVehiculoFormState();

    showModal("Vehículo eliminado correctamente.");
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible borrar el vehículo.", "error");
  }
};

const getNextVehicleStatus = (estadoActual = "") => {
  const key = slugifyText(estadoActual);

  if (key === "disponible") return "alquilado";
  if (key === "alquilado") return "mantenimiento";
  return "disponible";
};

const getNextVehicleStatusLabel = (estadoActual = "") => {
  const nextStatus = getNextVehicleStatus(estadoActual);
  const meta = getVehicleStatusMeta(nextStatus);
  return meta.label;
};

const cambiarEstadoVehiculo = async (vehiculo) => {
  const nuevoEstado = getNextVehicleStatus(vehiculo.estado);

  try {
    let updated;

    try {
      updated = await apiPatch(`/vehiculos/${vehiculo.id}/estado`, {
        estado: nuevoEstado,
      });
    } catch (patchError) {
      updated = await apiPut(`/vehiculos/${vehiculo.id}`, {
        marca: vehiculo.marca,
        modelo: vehiculo.modelo,
        anio: vehiculo.anio,
        categoria: vehiculo.categoria,
        estado: nuevoEstado,
      });
    }

    const normalized = normalizarVehiculo(updated);

    appState.vehiculos = appState.vehiculos.map((item) =>
      Number(item.id) === Number(vehiculo.id) ? normalized : item
    );

    renderVehiculos();
    showModal(`Estado actualizado a ${getVehicleStatusMeta(nuevoEstado).label}.`);
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible cambiar el estado del vehículo.", "error");
  }
};