import {addSpinnerElement, removeSpinnerElement} from "./utils/spinner.js";
import {addNotification} from "./utils/notification.js";

export const constructTableUsers = (users, showModal) => {
    const tBody = document.querySelector("table tbody");
    tBody.innerHTML = "";

    if (users.length) {
        const spanCountEntities = document.querySelector("span.count-entities");
        spanCountEntities.textContent = users.length.toString();

        let countElement = 0;
        users.forEach((user) => {
            countElement++;

            const tr = document.createElement("tr");
            tr.addEventListener("click", showModal);
            tr.dataset.entityId = user["id"];
            tr.className = user["status"] === 0 ?
                "table-warning" :
                    user["status"] === 2 ?
                    "table-danger" :
                "";

            const thNumElement = document.createElement("th");
            thNumElement.textContent = countElement.toString();
            thNumElement.scope = "row";

            const tdLastname = document.createElement("td");
            tdLastname.dataset.col = "col-lastname";
            tdLastname.className = "d-none d-sm-table-cell";
            tdLastname.textContent = user["lastname"];

            const tdFirstname = document.createElement("td");
            tdFirstname.dataset.col = "col-firstname";
            tdFirstname.className = "d-none d-sm-table-cell";
            tdFirstname.textContent = user["firstname"];

            const tdPseudo = document.createElement("td");
            tdPseudo.dataset.col = "col-pseudo";
            tdPseudo.textContent = user["pseudo"];

            const tdEmail = document.createElement("td");
            tdEmail.dataset.col = "col-email";
            tdEmail.textContent = user["email"];

            const tdRole = document.createElement("td");
            tdRole.dataset.col = "col-role";
            tdRole.textContent = user["role"] === "ROLE_ADMIN" ? "Admin" : "User";

            const tdStatus = document.createElement("td");
            tdStatus.dataset.col = "col-status";
            tdStatus.className = "d-none d-xxl-table-cell";
            tdStatus.textContent = user["status"] === 0 ?
                "En attente" :
                user["status"] === 2 ?
                    "Désactivé" :
                    "Enregistré";
            tdStatus.addEventListener("DOMSubtreeModified", (event) => {
                switch (event.currentTarget.textContent) {
                    case "En attente":
                        tr.className = "table-warning";
                        break;
                    case "Enregistré":
                        tr.className = "";
                        break;
                    case "Désactivé":
                        tr.className = "table-danger";
                        break;
                }
            });

            const tdNbPosts = document.createElement("td");
            tdNbPosts.dataset.col = "col-countPosts";
            tdNbPosts.className = "d-none d-xxl-table-cell";
            tdNbPosts.textContent = user["countPosts"];

            const tdNbComments = document.createElement("td");
            tdNbComments.dataset.col = "col-countComments";
            tdNbComments.className = "d-none d-xxl-table-cell";
            tdNbComments.textContent = user["countComments"];

            tr.append(
                thNumElement,
                tdLastname,
                tdFirstname,
                tdPseudo,
                tdEmail,
                tdRole,
                tdStatus,
                tdNbPosts,
                tdNbComments
            );

            tBody.append(tr);
        });
    } else {
        tBody.innerHTML = "<tr><td colspan='9' class='text-center'>Aucun utiliseur n'a été trouvé.</td></tr>";
    }
};

export const initUsers = (modal) => {
    const btnActions = modal.querySelectorAll("div.modal-footer button[data-action]");
    const statusModalElement = modal.querySelector("[data-col=col-status]");
    const roleModalElement = modal.querySelector("[data-col=col-role]");

    statusModalElement.addEventListener("DOMSubtreeModified", () => {
        const id = modal.dataset.entityId;
        const statusTableElement = document.querySelector("tbody tr[data-entity-id='"+id+"'] td[data-col='col-status']");
        const btnUpdateStatus = modal.querySelector("div.modal-footer button[data-action=update-status]");

        statusTableElement.textContent = statusModalElement.textContent;

        if (statusModalElement.textContent === "En attente") {
            statusModalElement.className = "badge badge-pill bg-warning text-dark p-2";
            btnUpdateStatus.textContent = "Activer le compte";
            btnUpdateStatus.className = "btn btn-sm btn-success";
        } else if (statusModalElement.textContent === "Désactivé") {
            statusModalElement.className = "badge badge-pill bg-danger text-light p-2"
            btnUpdateStatus.textContent = "Activer le compte";
            btnUpdateStatus.className = "btn btn-sm btn-success";
        } else {
            statusModalElement.className = "badge badge-pill bg-dark text-light p-2";
            btnUpdateStatus.textContent = "Désactiver le compte";
            btnUpdateStatus.className = "btn btn-sm btn-danger";
        }
    });

    roleModalElement.addEventListener("DOMSubtreeModified", () => {
        const id = modal.dataset.entityId;
        const roleTableElement = document.querySelector("tbody tr[data-entity-id='"+id+"'] td[data-col='col-role']");
        const btnUpdateRole = modal.querySelector("div.modal-footer button[data-action=update-role]");

        roleTableElement.textContent = roleModalElement.textContent;

        if (roleModalElement.textContent === "Admin") {
            btnUpdateRole.textContent = "Passer User";
        } else {
            btnUpdateRole.textContent = "Passer Admin";
        }
    });

    btnActions.forEach((btn) => {
        btn.addEventListener("click", (event) => {
            const id = modal.dataset.entityId;
            const url = event.currentTarget.dataset.apiUrl.replace("__ID__", id);
            addSpinnerElement(btn);

            fetch(url).then((response) => {
                return response.json();
            }).then((response) => {
                addNotification(response);
                if (response.success) {
                    switch (response.action) {
                        case "update-status":
                            statusModalElement.textContent = statusModalElement.textContent === "Enregistré" ?
                                "Désactivé" :
                                "Enregistré";
                            break;
                        case "update-role":
                            roleModalElement.textContent = roleModalElement.textContent === "Admin" ?
                                "User" :
                                "Admin";
                            break;
                    }
                }
            }).catch(() => {
                addNotification({
                    success: false,
                    message: "Une erreur s'est produite, veuillez réessayer plus tard."
                });
            }).finally(() => {
                removeSpinnerElement(btn);
            });
        });
    });
};