import {addSpinnerElement, removeSpinnerElement} from "./utils/spinner.js";
import {addNotification} from "./utils/notification.js";
import {filterEntities} from "./utils/filter.js";
import {entities} from "./admin-dashboard.js";

export const constructTableUsers = (users, showModal) => {
    const tBody = document.querySelector("table tbody");
    tBody.innerHTML = "";

    if (users.length) {
        const spanCountEntities = document.querySelector("span.count-entities");
        spanCountEntities.innerHTML = users.length.toString();

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
            thNumElement.innerHTML = countElement.toString();
            thNumElement.scope = "row";

            const tdLastname = document.createElement("td");
            tdLastname.dataset.col = "col-lastname";
            tdLastname.className = "d-none d-sm-table-cell";
            tdLastname.innerHTML = user["lastname"];

            const tdFirstname = document.createElement("td");
            tdFirstname.dataset.col = "col-firstname";
            tdFirstname.className = "d-none d-sm-table-cell";
            tdFirstname.innerHTML = user["firstname"];

            const tdPseudo = document.createElement("td");
            tdPseudo.dataset.col = "col-pseudo";
            tdPseudo.innerHTML = user["pseudo"];

            const tdEmail = document.createElement("td");
            tdEmail.dataset.col = "col-email";
            tdEmail.innerHTML = user["email"];

            const tdRole = document.createElement("td");
            tdRole.dataset.col = "col-role";
            tdRole.innerHTML = user["role"] === "ROLE_ADMIN" ? "Admin" : "User";

            const tdStatus = document.createElement("td");
            tdStatus.dataset.col = "col-status";
            tdStatus.className = "d-none d-xxl-table-cell";
            tdStatus.innerHTML = user["status"] === 0 ?
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
            tdNbPosts.innerHTML = user["countPosts"];

            const tdNbComments = document.createElement("td");
            tdNbComments.dataset.col = "col-countComments";
            tdNbComments.className = "d-none d-xxl-table-cell";
            tdNbComments.innerHTML = user["countComments"];

            const tdCreatedAt = document.createElement("td");
            tdCreatedAt.dataset.col = "col-createdAt";
            tdCreatedAt.className = "d-none d-sm-table-cell";
            tdCreatedAt.innerHTML = user["createdAt"] !== null ? new Date(user["createdAt"]+" UTC").toLocaleDateString() : "-";

            tr.append(
                thNumElement,
                tdLastname,
                tdFirstname,
                tdPseudo,
                tdEmail,
                tdRole,
                tdStatus,
                tdNbPosts,
                tdNbComments,
                tdCreatedAt
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
        const btnUpdateStatus = modal.querySelector("div.modal-footer button[data-action=update-status]");

        if (statusModalElement.innerHTML === "En attente") {
            statusModalElement.className = "badge badge-pill bg-warning text-dark p-2";
            btnUpdateStatus.innerHTML = "Activer le compte";
            btnUpdateStatus.className = "btn btn-sm btn-success";
        } else if (statusModalElement.innerHTML === "Désactivé") {
            statusModalElement.className = "badge badge-pill bg-danger text-light p-2"
            btnUpdateStatus.innerHTML = "Activer le compte";
            btnUpdateStatus.className = "btn btn-sm btn-success";
        } else {
            statusModalElement.className = "badge badge-pill bg-dark text-light p-2";
            btnUpdateStatus.innerHTML = "Désactiver le compte";
            btnUpdateStatus.className = "btn btn-sm btn-danger";
        }
    });

    roleModalElement.addEventListener("DOMSubtreeModified", () => {
        const btnUpdateRole = modal.querySelector("div.modal-footer button[data-action=update-role]");

        if (roleModalElement.innerHTML === "Admin") {
            btnUpdateRole.innerHTML = "Passer User";
        } else {
            btnUpdateRole.innerHTML = "Passer Admin";
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
                            entities.map((entity) => {
                                if (entity["id"] === id) {
                                    entity["status"] = entity["status"] === 1 ? 2 : 1;
                                }
                                return entity;
                            });
                            statusModalElement.innerHTML = statusModalElement.innerHTML === "Enregistré" ?
                                "Désactivé" :
                                "Enregistré";
                            break;
                        case "update-role":
                            entities.map((entity) => {
                                if (entity["id"] === id) {
                                    entity["role"] = entity["role"] === "ROLE_ADMIN" ? "ROLE_USER" : "ROLE_ADMIN";
                                }
                                return entity;
                            });
                            roleModalElement.innerHTML = roleModalElement.innerHTML === "Admin" ?
                                "User" :
                                "Admin";
                            break;
                    }
                    filterEntities();
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