const modalElement = document.getElementById("modal1");
const modalTextElement = modalElement?.querySelector(".modal__text");
const modalButtonElement = modalElement?.querySelector(".modal__btn");
const modalBoxElement = modalElement?.querySelector(".modal__box");
const modalBackdropElement = modalElement?.querySelector(".modal__backdrop");

let modalAutoCloseTimer = null;

const hideModal = () => {
  if (!modalElement) return;

  modalElement.classList.add("close");
  modalElement.setAttribute("aria-hidden", "true");

  if (modalAutoCloseTimer) {
    clearTimeout(modalAutoCloseTimer);
    modalAutoCloseTimer = null;
  }
};

const showModal = (message, type = "ok") => {
  if (!modalElement || !modalTextElement) return;

  modalTextElement.textContent = message;
  modalTextElement.style.color =
    type === "error"
      ? "#ff8fa3"
      : type === "warning"
        ? "#ffd089"
        : "#eef2ff";

  modalElement.classList.remove("close");
  modalElement.setAttribute("aria-hidden", "false");

  if (modalAutoCloseTimer) {
    clearTimeout(modalAutoCloseTimer);
    modalAutoCloseTimer = null;
  }

  if (type !== "error") {
    modalAutoCloseTimer = setTimeout(() => {
      hideModal();
    }, 1600);
  }
};

modalButtonElement?.addEventListener("click", hideModal);

modalBackdropElement?.addEventListener("click", hideModal);

document.addEventListener("keydown", (event) => {
  if (
    event.key === "Escape" &&
    modalElement &&
    !modalElement.classList.contains("close")
  ) {
    hideModal();
  }
});