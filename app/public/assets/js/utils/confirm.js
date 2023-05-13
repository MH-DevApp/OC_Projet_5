export const constructModalConfirm = (content, funcConfirmAction) => {
    const bodyContainer = document.querySelector("div.body")
    const modal = document.createElement("div");
    const modalDialog = document.createElement("div");
    const modalContent = document.createElement("div");
    const modalHeader = document.createElement("div");
    const modalBody = document.createElement("div");
    const modalFooter = document.createElement("div");
    const btnClose = document.createElement("button");

    // BTN CLOSE
    btnClose.type = "button";
    btnClose.className = "btn-close";
    btnClose.dataset.bsDismiss = "modal";
    btnClose.ariaLabel = "Close";

    // MODAL
    modal.className = "modal modal-lg fade";
    modal.dataset.bsBackdrop = "static";
    modal.dataset.bsKeyboard = "false";
    modal.id = "modalConfirm";
    modal.tabIndex = -1;
    modal.ariaLabelledby = "modalConfirm";
    modal.ariaHidden = "true";

    // DIALOG
    modalDialog.className = "modal-dialog modal-dialog-centered";

    // CONTENT
    modalContent.className = "modal-content";

    // HEADER
    modalHeader.className = "modal-header";
    const titleElement = document.createElement("h3");
    titleElement.textContent = "Confirmation ?";
    modalHeader.append(titleElement, btnClose);

    // BODY
    modalBody.className = "modal-body";
    const contentElement = document.createElement("p");
    contentElement.textContent = content;
    modalBody.append(contentElement);

    // FOOTER
    modalFooter.className = "modal-footer";
    const containerButtonsActions = document.createElement("div");
    const btnNo = document.createElement("button");
    const btnYes = document.createElement("button");

    containerButtonsActions.className =
        "d-flex flex-column flex-sm-row align-items-center " +
        "gap-2 justify-content-center justify-content-sm-end w-100";

    btnNo.type = "button";
    btnNo.className = "btn btn-outline-danger";
    btnNo.addEventListener("click", () => { btnClose.click();});
    btnNo.textContent = "Non";

    btnYes.type = "button";
    btnYes.className = "btn btn-success";
    btnYes.addEventListener("click", (event) => {
        event.currentTarget.setAttribute("disabled", true);
        funcConfirmAction();
        bsModal.hide();
    });
    btnYes.textContent = "Oui";

    containerButtonsActions.append(btnNo, btnYes);
    modalFooter.append(containerButtonsActions);


    // APPEND
    modalContent.append(
        modalHeader,
        modalBody,
        modalFooter
    );

    modalDialog.append(modalContent);

    modal.append(modalDialog);

    bodyContainer.append(modal);

    const bsModal = new bootstrap.Modal(modal, {
        keyboard: false,
        backdrop: "static"
    });

    modal.addEventListener("hidden.bs.modal", () => {
        bsModal.dispose();
        modal.remove();
    });

    return bsModal;
}