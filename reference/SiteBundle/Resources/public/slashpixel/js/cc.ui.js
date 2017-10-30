class Ui {
  constructor() {
    this.removeParentOnClick();
  }

  removeParentOnClick() {
    document.addEventListener("click", function handler(event) {
      for (let target = event.target; target && target !== this; target = target.parentNode) {
        if (target.matches("[data-remove-parent]")) {
          const el = target.parentElement;
          el.parentElement.removeChild(el);
        }
      }
    }, false);
  }
}

export default new Ui();
