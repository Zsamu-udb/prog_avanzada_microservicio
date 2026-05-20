const API_BASE_URL = "http://127.0.0.1:8000";

const buildUrl = (endpoint) => `${API_BASE_URL}${endpoint}`;

const parseResponse = async (response) => {
  const contentType = response.headers.get("content-type") || "";
  const isJson = contentType.includes("application/json");
  const body = isJson ? await response.json() : await response.text();

  if (!response.ok) {
    const message =
      typeof body === "object" && body !== null
        ? body.message || body.error || "Error en la solicitud"
        : body || "Error en la solicitud";

    throw new Error(message);
  }

  return body;
};

const apiGet = async (endpoint) => {
  const response = await fetch(buildUrl(endpoint), {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
  });

  return parseResponse(response);
};

const apiPost = async (endpoint, data) => {
  const response = await fetch(buildUrl(endpoint), {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(data),
  });

  return parseResponse(response);
};

const apiPut = async (endpoint, data) => {
  const response = await fetch(buildUrl(endpoint), {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(data),
  });

  return parseResponse(response);
};

const apiPatch = async (endpoint, data) => {
  const response = await fetch(buildUrl(endpoint), {
    method: "PATCH",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(data),
  });

  return parseResponse(response);
};

const apiDelete = async (endpoint) => {
  const response = await fetch(buildUrl(endpoint), {
    method: "DELETE",
    headers: {
      Accept: "application/json",
    },
  });

  return parseResponse(response);
};