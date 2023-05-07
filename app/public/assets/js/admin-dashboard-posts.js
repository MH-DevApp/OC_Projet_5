import {addSpinnerElement, removeSpinnerElement} from "./utils/spinner.js";
import {addNotification} from "./utils/notification.js";
import {entities} from "./admin-dashboard.js";
import {filterEntities} from "./utils/filter.js";

export const constructTablePosts = (posts, showModal) => {
    const tBody = document.querySelector("table tbody");
    tBody.innerHTML = "";

    if (posts.length) {
        const spanCountEntities = document.querySelector("span.count-entities");
        spanCountEntities.textContent = posts.length.toString();

        let countElement = 0;
        posts.forEach((post) => {
            countElement++;

            const tr = document.createElement("tr");
            tr.addEventListener("click", showModal);
            tr.dataset.entityId = post["id"];
            tr.className = post["isFeatured"] === 1 ?
                "table-success" :
                post["isPublished"] === 0 ?
                    "table-warning" :
                "";

            const thNumElement = document.createElement("th");
            thNumElement.textContent = countElement.toString();
            thNumElement.scope = "row";

            const tdAuthor = document.createElement("td");
            tdAuthor.dataset.col = "col-author";
            tdAuthor.textContent = post["author"];

            const tdTitle = document.createElement("td");
            tdTitle.dataset.col = "col-title";
            tdTitle.textContent = post["title"];

            const tdChapo = document.createElement("td");
            tdChapo.dataset.col = "col-chapo";
            tdChapo.className = "d-none d-xxl-table-cell";
            tdChapo.textContent = post["chapo"];

            const tdContent = document.createElement("td");
            tdContent.dataset.col = "col-content";
            tdContent.className = "d-none d-xxl-table-cell";
            tdContent.textContent = post["content"];

            const tdIsPublished = document.createElement("td");
            tdIsPublished.dataset.col = "col-isPublished";
            tdIsPublished.textContent = post["isPublished"] === 1 ? "Oui" : "Non";
            tdIsPublished.addEventListener("DOMSubtreeModified", (event) => {
                switch (event.currentTarget.textContent) {
                    case "Oui":
                        if (tdIsFeatured.textContent === "Oui") {
                            tr.className = "table-success";
                        } else {
                            tr.className = "";
                        }
                        break;
                    case "Non":
                        tr.className = "table-warning";
                        break;
                }
            });

            const tdIsFeatured = document.createElement("td");
            tdIsFeatured.dataset.col = "col-isFeatured";
            tdIsFeatured.textContent = post["isFeatured"] === 1 ? "Oui" : "Non";
            tdIsFeatured.addEventListener("DOMSubtreeModified", (event) => {
                switch (event.currentTarget.textContent) {
                    case "Oui":
                        tr.className = "table-success";
                        break;
                    case "Non":
                        if (tdIsPublished.textContent === "Non") {
                            tr.className = "table-warning";
                        } else {
                            tr.className = ""
                        }
                        break;
                }
            });

            const tdNbComments = document.createElement("td");
            tdNbComments.dataset.col = "col-countComments";
            tdNbComments.className = "d-none d-sm-table-cell";
            tdNbComments.textContent = post["countComments"];

            const tdCreatedAt = document.createElement("td");
            tdCreatedAt.dataset.col = "col-createdAt";
            tdCreatedAt.className = "d-none d-sm-table-cell";
            tdCreatedAt.textContent = post["createdAt"] !== null ? new Date(post["createdAt"]+" UTC").toLocaleDateString() : "-";

            const tdUpdatedAt = document.createElement("td");
            tdUpdatedAt.dataset.col = "col-updatedAt";
            tdUpdatedAt.className = "d-none d-sm-table-cell";
            tdUpdatedAt.textContent = post["updatedAt"] !== null ? new Date(post["updatedAt"]+" UTC").toLocaleDateString() : "-";

            tr.append(
                thNumElement,
                tdAuthor,
                tdTitle,
                tdChapo,
                tdContent,
                tdIsPublished,
                tdIsFeatured,
                tdNbComments,
                tdCreatedAt,
                tdUpdatedAt
            );

            tBody.append(tr);
        });
    } else {
        tBody.innerHTML = "<tr><td colspan='9' class='text-center'>Aucun post n'a été trouvé.</td></tr>";
    }
};

export const initPosts = (modal) => {
    const btnActions = modal.querySelectorAll("div.modal-footer button[data-action]");
    const publishedModalElement = modal.querySelector("[data-col=col-isPublished]");
    const featuredModalElement = modal.querySelector("[data-col=col-isFeatured]");
    const updatedAtModalElement = modal.querySelector("[data-col=col-updatedAt]");

    publishedModalElement.addEventListener("DOMSubtreeModified", () => {
        const btnUpdatePublished = modal.querySelector("div.modal-footer button[data-action=update-published]");

        if (publishedModalElement.textContent === "Oui") {
            publishedModalElement.className = "small rounded bg-dark text-light py-1 px-2";
            btnUpdatePublished.textContent = "Ne plus publier";
            btnUpdatePublished.className = "btn btn-sm btn-danger";

        } else {
            publishedModalElement.className = "small border border-dark rounded text-dark py-1 px-2";
            btnUpdatePublished.textContent = "Publier";
            btnUpdatePublished.className = "btn btn-sm btn-success";
        }
    });

    featuredModalElement.addEventListener("DOMSubtreeModified", () => {
        const btnUpdateFeatured = modal.querySelector("div.modal-footer button[data-action=update-featured]");

        if (featuredModalElement.textContent === "Oui") {
            featuredModalElement.className = "small rounded bg-dark text-light py-1 px-2";
            btnUpdateFeatured.textContent = "Ne plus mettre en avant";
            btnUpdateFeatured.className = "btn btn-sm btn-danger";
        } else {
            featuredModalElement.className = "small border border-dark rounded text-dark py-1 px-2";
            btnUpdateFeatured.textContent = "Mettre en avant";
            btnUpdateFeatured.className = "btn btn-sm btn-success";
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
                        case "update-published":
                            entities.map((entity) => {
                                if (entity["id"] === id) {
                                    entity["isPublished"] = entity["isPublished"] === 0 ? 1 : 0;
                                    entity["updatedAt"] = new Date().toUTCString();
                                }
                                return entity;
                            });
                            publishedModalElement.textContent = publishedModalElement.textContent === "Oui" ?
                                "Non" :
                                "Oui";
                            break;
                        case "update-featured":
                            entities.map((entity) => {
                                if (entity["id"] === id) {
                                    entity["isFeatured"] = entity["isFeatured"] === 0 ? 1 : 0;
                                    entity["updatedAt"] = new Date().toUTCString();
                                }
                                return entity;
                            });
                            featuredModalElement.textContent = featuredModalElement.textContent === "Oui" ?
                                "Non" :
                                "Oui";
                            break;
                    }
                    updatedAtModalElement.textContent = new Date().toLocaleDateString();
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