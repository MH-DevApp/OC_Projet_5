export const addSpinnerElement = (el) => {
    const spinner = document.createElement("span");
    spinner.className = "spinner-border spinner-border-sm ms-2";
    spinner.role = "status";
    spinner.ariaHidden = "true";

    el.setAttribute("disabled", true);

    el.append(spinner);
}

export const removeSpinnerElement = (el) => {
    const spinner = el.querySelector("span.spinner-border");
    if (spinner) {
        spinner.remove();
    }
    el.removeAttribute("disabled");
}