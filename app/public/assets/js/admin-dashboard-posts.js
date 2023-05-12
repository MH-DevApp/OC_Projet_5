import {addSpinnerElement, removeSpinnerElement} from "./utils/spinner.js";
import {addNotification, clearNotification} from "./utils/notification.js";
import {constructBtnActions, entities, updateEntities} from "./admin-dashboard.js";
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
            tr.addEventListener("dblclick", showModal);
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
            tdIsPublished.className = "d-none d-lg-table-cell";
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
            tdIsFeatured.className = "d-none d-lg-table-cell";
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

            const tdActions = document.createElement("td");
            const btnActions = [
                {
                    imgName: "eye-solid.svg",
                    altName: "Logo eye",
                    color: "dark",
                    events: [
                        {
                            event: "click",
                            func: (event) => showModal(event, post["id"])
                        }
                    ]
                },
                {
                    imgName: "pen-solid.svg",
                    altName: "Logo pen",
                    color: "dark",
                    dataset: [
                        {
                            key: "bsToggle",
                            value: "modal"
                        },
                        {
                            key: "bsTarget",
                            value: "#modalPostForm"
                        }
                    ],
                    events: [
                        {
                            event: "click",
                            func: () => {
                                const postForm = document.querySelector("form#postForm");
                                const titleForm = postForm.querySelector("h1#modalTitlePostForm");
                                const btnPostForm = postForm.querySelector("button#btnPostForm");
                                const entity = entities.filter((entity) => entity.id === post["id"])[0];
                                const inputsForm = postForm.querySelectorAll("[data-form='"+postForm.id+"']");

                                clearForm(postForm);

                                inputsForm.forEach((input) => {
                                    switch (input.type) {
                                        case "text":
                                        case "textarea":
                                            input.value = new DOMParser()
                                                .parseFromString(entity[input.id], "text/html")
                                                .documentElement
                                                .textContent;
                                            break;
                                        case "checkbox":
                                            input.checked = entity[input.id] === 1;
                                            break;
                                    }
                                });

                                titleForm.innerHTML = "Modifier un post";
                                btnPostForm.innerHTML = "Modifier";
                                btnPostForm.dataset.apiUrl = "/admin/post/edit/"+post["id"]
                            }
                        }
                    ]
                },
                {
                    imgName: "trash-solid.svg",
                    altName: "Logo trash",
                    color: "danger"
                }
            ];

            tdActions.className = "d-flex justify-content-center";

            constructBtnActions(btnActions, tdActions);

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
                tdUpdatedAt,
                tdActions
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

    // MODAL FORM POST
    const modalPostForm = document.querySelector("div.modal#modalPostForm");
    const postForm = modalPostForm.querySelector("form#postForm");

    postForm.addEventListener("submit", (event) => {
        event.preventDefault();
    });

    const btnPostForm = modalPostForm.querySelector("div.modal#modalPostForm button#btnPostForm");

    btnPostForm.addEventListener("click", (event) => {
        const url = event.currentTarget.dataset.apiUrl;
        addSpinnerElement(btnPostForm);

        fetch(url, {
            method: "POST",
            body: new FormData(postForm)
        }).then((response) => {
            if (response.ok) {
                return response.json();
            }
            throw new Error("Une erreur s'est produite durant l'opération. Veuillez réessayer plus tard.");
        }).then((response) => {
            if (response.success) {
                if (response["formType"] === "updated-post") {
                    updateEntities(entities.filter((entity) => entity["id"] !== response["post"]["id"]));
                    removeValidationOnInputs(postForm.querySelectorAll("[data-form='"+postForm.id+"']"));
                } else {
                    clearForm(postForm);
                }

                entities.push(response["post"]);
                entities.sort((a, b) => {
                    return new Date(b["createdAt"]) - new Date(a["createdAt"]);
                });
                filterEntities();
                addNotification(
                    response,
                    modalPostForm.querySelector("div.notification")
                );
            } else {
                if (response["errors"]["global"]) {
                    addNotification({
                        success: false,
                        message: response["errors"]["global"]
                    }, modalPostForm.querySelector("div.notification"));
                }

                addValidationOnInputs(response["errors"], postForm);
            }
        }).catch((error) => {
            addNotification({
                success: false,
                message: error.message
            }, modalPostForm.querySelector("div.notification"));
        }).finally(() => {
            removeSpinnerElement(btnPostForm);
        });
    });

    const btnShowModalPostForm = document
        .querySelector("div#actionsContainer button[data-bs-target='#modalPostForm']");

    btnShowModalPostForm.addEventListener("click", () => {
        const modalTitle = modalPostForm.querySelector("h1#modalTitlePostForm");
        modalTitle.innerHTML = "Ajouter un post"
        btnPostForm.dataset.apiUrl = "/admin/post/add";
        btnPostForm.innerHTML = "Ajouter";
        clearNotification(modalPostForm.querySelector("div.notification"));
        clearForm(postForm);
    });

    postForm
        .querySelectorAll("[data-form='"+postForm.id+"']")
        .forEach((element) => {
            switch (element.type) {
                case "text":
                case "textarea":
                    element.addEventListener("input", () => {
                        removeValidationOnInputs([element]);
                    });
                    break;
                case "checkbox":
                    element.addEventListener("change", () => {
                        removeValidationOnInputs([element]);
                    });
            }
        });

};

const clearForm = (form) => {
    const elements = form.querySelectorAll("[data-form='"+form.id+"']");

    removeValidationOnInputs(elements);

    elements.forEach((element) => {

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

const removeValidationOnInputs = (inputs) => {
    inputs.forEach((input) => {
        const invalidFeedbackElement = document.querySelector("div[data-input-name='"+input.name+"']");

        if (invalidFeedbackElement) {
            invalidFeedbackElement.innerHTML = "";
        }

        if (input.classList.contains("is-valid") || input.classList.contains("is-invalid")) {
            input.classList.remove("is-valid", "is-invalid");
        }
    });
};
