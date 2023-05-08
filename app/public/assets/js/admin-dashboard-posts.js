import {addSpinnerElement, removeSpinnerElement} from "./utils/spinner.js";
import {addNotification, clearNotification} from "./utils/notification.js";
import {entities} from "./admin-dashboard.js";
import {filterEntities} from "./utils/filter.js";

export const constructTablePosts = (posts, showModal) => {
    const tBody = document.querySelector("table tbody");
    tBody.innerHTML = "";

    if (posts.length) {
        const spanCountEntities = document.querySelector("span.count-entities");
        spanCountEntities.innerHTML = posts.length.toString();

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
            thNumElement.innerHTML = countElement.toString();
            thNumElement.scope = "row";

            const tdAuthor = document.createElement("td");
            tdAuthor.dataset.col = "col-author";
            tdAuthor.innerHTML = post["author"];

            const tdTitle = document.createElement("td");
            tdTitle.dataset.col = "col-title";
            tdTitle.innerHTML = post["title"];

            const tdChapo = document.createElement("td");
            tdChapo.dataset.col = "col-chapo";
            tdChapo.className = "d-none d-xxl-table-cell";
            tdChapo.innerHTML = post["chapo"];

            const tdContent = document.createElement("td");
            tdContent.dataset.col = "col-content";
            tdContent.className = "d-none d-xxl-table-cell";
            tdContent.innerHTML = post["content"];

            const tdIsPublished = document.createElement("td");
            tdIsPublished.dataset.col = "col-isPublished";
            tdIsPublished.innerHTML = post["isPublished"] === 1 ? "Oui" : "Non";
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
            tdIsFeatured.innerHTML = post["isFeatured"] === 1 ? "Oui" : "Non";
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
            tdNbComments.innerHTML = post["countComments"];

            const tdCreatedAt = document.createElement("td");
            tdCreatedAt.dataset.col = "col-createdAt";
            tdCreatedAt.className = "d-none d-sm-table-cell";
            tdCreatedAt.innerHTML = post["createdAt"] !== null ? new Date(post["createdAt"]+" UTC").toLocaleDateString() : "-";

            const tdUpdatedAt = document.createElement("td");
            tdUpdatedAt.dataset.col = "col-updatedAt";
            tdUpdatedAt.className = "d-none d-sm-table-cell";
            tdUpdatedAt.innerHTML = post["updatedAt"] !== null ? new Date(post["updatedAt"]+" UTC").toLocaleDateString() : "-";

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

        if (publishedModalElement.innerHTML === "Oui") {
            publishedModalElement.className = "small rounded bg-dark text-light py-1 px-2";
            btnUpdatePublished.innerHTML = "Ne plus publier";
            btnUpdatePublished.className = "btn btn-sm btn-danger";

        } else {
            publishedModalElement.className = "small border border-dark rounded text-dark py-1 px-2";
            btnUpdatePublished.innerHTML = "Publier";
            btnUpdatePublished.className = "btn btn-sm btn-success";
        }
    });

    featuredModalElement.addEventListener("DOMSubtreeModified", () => {
        const btnUpdateFeatured = modal.querySelector("div.modal-footer button[data-action=update-featured]");

        if (featuredModalElement.innerHTML === "Oui") {
            featuredModalElement.className = "small rounded bg-dark text-light py-1 px-2";
            btnUpdateFeatured.innerHTML = "Ne plus mettre en avant";
            btnUpdateFeatured.className = "btn btn-sm btn-danger";
        } else {
            featuredModalElement.className = "small border border-dark rounded text-dark py-1 px-2";
            btnUpdateFeatured.innerHTML = "Mettre en avant";
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
                addNotification(response, modal.querySelector("div.notification"));
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
                            publishedModalElement.innerHTML = publishedModalElement.innerHTML === "Oui" ?
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
                            featuredModalElement.innerHTML = featuredModalElement.innerHTML === "Oui" ?
                                "Non" :
                                "Oui";
                            break;
                    }
                    updatedAtModalElement.innerHTML = new Date().toLocaleDateString();
                    filterEntities();
                }
            }).catch(() => {
                addNotification({
                    success: false,
                    message: "Une erreur s'est produite, veuillez réessayer plus tard."
                }, modal.querySelector("div.notification"));
            }).finally(() => {
                removeSpinnerElement(btn);
            });
        });
    });

    // MODAL FORM ADD POST
    const modalAddPost = document.querySelector("div.modal#modalAddPost");
    const formAddPost = modalAddPost.querySelector("form#formAddPost");

    formAddPost.addEventListener("submit", (event) => {
        event.preventDefault();
    });

    const btnAddPost = modalAddPost.querySelector("div.modal#modalAddPost button#btnAddPost");

    btnAddPost.addEventListener("click", (event) => {
        const url = event.currentTarget.dataset.apiUrl;
        addSpinnerElement(btnAddPost);

        fetch(url, {
            method: "POST",
            body: new FormData(formAddPost)
        }).then((response) => {
            if (response.ok) {
                return response.json();
            }
            throw new Error("Une erreur s'est produite lors de l'ajout du post. Veuillez réessayer plus tard.");
        }).then((response) => {
            if (response.success) {
                clearForm(formAddPost);
                entities.push(response["postUpdated"]);
                entities.sort((a, b) => {
                    return new Date(b["createdAt"]) - new Date(a["createdAt"]);
                });
                filterEntities();
                addNotification(
                    response,
                    modalAddPost.querySelector("div.notification")
                );
            } else {
                if (response["errors"]["global"]) {
                    addNotification({
                        success: false,
                        message: response["errors"]["global"]
                    }, modalAddPost.querySelector("div.notification"));
                }

                addValidationOnInputs(response["errors"], formAddPost);
            }
        }).catch((error) => {
            addNotification({
                success: false,
                message: error.message
            }, modalAddPost.querySelector("div.notification"));
        }).finally(() => {
            removeSpinnerElement(btnAddPost);
        });
    });

    const btnShowModalAddPost = document
        .querySelector("div#actionsContainer button[data-bs-target='#modalAddPost']");

    btnShowModalAddPost.addEventListener("click", () => {
        clearNotification(modalAddPost.querySelector("div.notification"));
        clearForm(formAddPost);
    });

    formAddPost
        .querySelectorAll("[data-form='"+formAddPost.id+"']")
        .forEach((element) => {
            switch (element.type) {
                case "text":
                case "textarea":
                    element.addEventListener("input", () => {
                        removeValidationOnInputs(element);
                    });
                    break;
                case "checkbox":
                    element.addEventListener("change", () => {
                        removeValidationOnInputs(element);
                    });
            }
        });

};

const clearForm = (form) => {
    const elements = form.querySelectorAll("[data-form='"+form.id+"']");
    elements.forEach((element) => {
        removeValidationOnInputs(element);

        switch (element.type) {
            case "text":
            case "textarea":
                element.value = "";
                break;
            case "checkbox":
                element.checked = false;
                break;
        }
    });
};

const addValidationOnInputs = (errors, form) => {
    const inputs = form.querySelectorAll("[data-form='"+form.id+"']");
    inputs.forEach((input) => {
        input.classList.remove("is-valid", "is-invalid");
        if (errors[input.name]) {
            const invalidFeedbackElement = form.querySelector("div[data-input-name='"+input.name+"']");

            if (invalidFeedbackElement) {
                invalidFeedbackElement.innerHTML = errors[input.name];
            }

            input.classList.add("is-invalid");

        } else {
            input.classList.add("is-valid");
        }
    });
}

const removeValidationOnInputs = (input) => {
    const invalidFeedbackElement = document.querySelector("div[data-input-name='"+input.name+"']");

    if (invalidFeedbackElement) {
        invalidFeedbackElement.innerHTML = "";
    }

    if (input.classList.contains("is-valid") || input.classList.contains("is-invalid")) {
        input.classList.remove("is-valid", "is-invalid");
    }
};
