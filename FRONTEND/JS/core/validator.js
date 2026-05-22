// js/core/validator.js
export class Validator {
  static required(value) {
    return value !== null && value !== undefined && String(value).trim() !== "";
  }

  static email(value) {
    if (!this.required(value)) return true; // si está vacío, que lo valide otro
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  static positiveInt(value) {
    if (!this.required(value)) return false;
    const n = Number(value);
    return Number.isInteger(n) && n > 0;
  }
}