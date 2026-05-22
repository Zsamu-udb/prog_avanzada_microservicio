const appState = {
  clientes: [],
  vehiculos: [],
  reservas: [],
  clienteEditando: null,
  vehiculoEditando: null,
  reservaEditando: null,
};

const VEHICLE_IMAGES = {
  sedan: "assets/images/placeholder-sedan.jpg",
  suv: "assets/images/placeholder-suv.jpg",
  deportivo: "assets/images/placeholder-sport.jpg",
  sport: "assets/images/placeholder-sport.jpg",
  camioneta: "assets/images/placeholder-suv.jpg",
  hatchback: "assets/images/placeholder-sedan.jpg",
  pickup: "assets/images/placeholder-suv.jpg",
  default: "assets/images/placeholder-sedan.jpg",
};

const escapeHtml = (value = "") =>
  String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");

const setText = (selector, value) => {
  const element = document.querySelector(selector);
  if (element) {
    element.textContent = value;
  }
};

const updateDashboardCounts = () => {
  setText("#clientesCount", appState.clientes.length);
  setText("#vehiculosCount", appState.vehiculos.length);
  setText("#reservasCount", appState.reservas.length);
};

const createActionButton = ({
  text,
  className = "",
  onClick,
  title = "",
}) => {
  const button = document.createElement("button");
  button.type = "button";
  button.className = `action-btn ${className}`.trim();
  button.textContent = text;

  if (title) {
    button.title = title;
    button.setAttribute("aria-label", title);
  }

  if (typeof onClick === "function") {
    button.addEventListener("click", onClick);
  }

  return button;
};

const createEmptyState = (title, description) => {
  const wrapper = document.createElement("div");
  wrapper.className = "empty-state";

  const heading = document.createElement("h5");
  heading.textContent = title;

  const text = document.createElement("p");
  text.textContent = description;

  wrapper.appendChild(heading);
  wrapper.appendChild(text);

  return wrapper;
};

const slugifyText = (value = "") =>
  String(value)
    .trim()
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");

const getVehicleImageByCategory = (categoria = "") => {
  const key = slugifyText(categoria);
  return VEHICLE_IMAGES[key] || VEHICLE_IMAGES.default;
};

const getVehicleStatusMeta = (estado = "") => {
  const key = slugifyText(estado);

  const map = {
    disponible: {
      label: "Disponible",
      className: "status-badge--available",
    },
    alquilado: {
      label: "Alquilado",
      className: "status-badge--busy",
    },
    mantenimiento: {
      label: "Mantenimiento",
      className: "status-badge--danger",
    },
  };

  return (
    map[key] || {
      label: estado || "Sin estado",
      className: "status-badge--pending",
    }
  );
};

const getReservationStatusMeta = (estado = "") => {
  const key = slugifyText(estado);

  const map = {
    activa: {
      label: "Activa",
      className: "status-badge--available",
    },
    completada: {
      label: "Completada",
      className: "status-badge--busy",
    },
    cancelada: {
      label: "Cancelada",
      className: "status-badge--danger",
    },
  };

  return (
    map[key] || {
      label: estado || "Sin estado",
      className: "status-badge--pending",
    }
  );
};