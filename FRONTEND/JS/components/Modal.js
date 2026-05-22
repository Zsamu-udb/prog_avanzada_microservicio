// js/components/Modal.js
export class Modal {
  constructor(root) {
    this.root = root;
    this.titleEl = root.querySelector(".modal__title");
    this.messageEl = root.querySelector(".modal__message");
    this.okBtn = root.querySelector("#modalOkBtn");

    this.okBtn.addEventListener("click", () => this.hide());
  }

  show(message, title = "Información") {
    this.titleEl.textContent = title;
    this.messageEl.textContent = message;
    this.root.classList.remove("hidden");
  }

  hide() {
    this.root.classList.add("hidden");
  }
}