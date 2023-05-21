import {entities} from "../admin-dashboard.js";
import {hiddenLoadingPage, showLoadingPage} from "./loading-page.js";
import {constructTableUsers} from "../admin-dashboard-users.js";
import {constructTablePosts} from "../admin-dashboard-posts.js";
import {constructTableComments} from "../admin-dashboard-comments.js";
import {updatePositionTopForStickyElements} from "./sticky-elements.js";

let entitiesFilter = [];
let entitiesType = "";
let showModal = null;

export const initFilter = (type, listenerModal = null) => {
    showLoadingPage();

    entitiesType = type;

    // Logo filter toggle
    const btnFilter = document.querySelector("button.btn-filter");
    btnFilter.addEventListener("click", (event) => {
        const img = event.currentTarget.querySelector("img");
        if (img.src.includes("filter-on")) {
            img.src = img.src.replace("filter-on", "filter-off");
        } else {
            img.src = img.src.replace("filter-off", "filter-on");
        }
    });

    showModal = listenerModal;
    addListenersOnFilterElement();
    filterEntities();
    hiddenLoadingPage();
}

export const filterEntities = () => {
    const filterInputSearch = (entity) => {
        /** @type {HTMLSelectElement} */
        const typeEntity = document.querySelector("select#selectTypeEntity");
        /** @type {HTMLInputElement} */
        const inputSearchEntity = document.querySelector("input#inputSearchEntity");

        if (inputSearchEntity && inputSearchEntity.value !== "") {
            if (entity[typeEntity.value] === null) {
                return false;
            }

            return entity[typeEntity.value]
                .toLowerCase()
                .includes(inputSearchEntity.value.toLowerCase());
        }

        return true;
    };

    const filterUsers = () => {
        /** @type {HTMLInputElement} */
        const inputRoleAdmin = document.querySelector("input#roleAdmin");
        /** @type {HTMLInputElement} */
        const inputRoleUser = document.querySelector("input#roleUser");
        /** @type {HTMLInputElement} */
        const inputStatusWaiting = document.querySelector("input#statusWaiting");
        /** @type {HTMLInputElement} */
        const inputStatusRegistered = document.querySelector("input#statusRegistered");
        /** @type {HTMLInputElement} */
        const inputStatusDeactivated = document.querySelector("input#statusDeactivated");

        entitiesFilter = entities.filter((entity) => {
            if (!filterInputSearch(entity)) {
                return false;
            }

            if (
                (entity["role"] === "ROLE_ADMIN" && !inputRoleAdmin.checked) ||
                (entity["role"] === "ROLE_USER" && !inputRoleUser.checked)
            ) {
                return false;
            }

            if (
                (entity["status"] === 0 && !inputStatusWaiting.checked) ||
                (entity["status"] === 1 && !inputStatusRegistered.checked) ||
                (entity["status"] === 2 && !inputStatusDeactivated.checked)
            ) {
                return false;
            }

            return true;
        });
    };

    const filterPosts = () => {
        /** @type {HTMLInputElement} */
        const inputAllPosts = document.querySelector("input#allPosts");
        /** @type {HTMLInputElement} */
        const inputPublished = document.querySelector("input#published");
        /** @type {HTMLInputElement} */
        const inputFeatured = document.querySelector("input#featured");

        entitiesFilter = entities.filter((entity) => {
            if (!filterInputSearch(entity)) {
                return false;
            }

            if (
                (!inputAllPosts.checked) &&
                (
                    (inputPublished.checked && entity["isPublished"] === 0) ||
                    (inputFeatured.checked && entity["isFeatured"] === 0)
                )
            ) {
                return false;
            }

            return true;
        });
    };

    const filterComments = () => {
        /** @type {HTMLInputElement} */
        const inputAllPosts = document.querySelector("input#allComments");
        /** @type {HTMLInputElement} */
        const inputWaiting = document.querySelector("input#waiting");
        /** @type {HTMLInputElement} */
        const inputValid = document.querySelector("input#valid");
        /** @type {HTMLInputElement} */
        const inputNotValid = document.querySelector("input#notValid");

        entitiesFilter = entities.filter((entity) => {
            if (!filterInputSearch(entity)) {
                return false;
            }

            if (
                (!inputAllPosts.checked) &&
                (
                    (inputValid.checked && entity["isValid"] === 0) ||
                    (inputWaiting.checked && entity["validBy"] !== null) ||
                    ((inputNotValid.checked && entity["isValid"] === 1) || (inputNotValid.checked && entity["validBy"] === null))
                )
            ) {
                return false;
            }

            return true;
        });
    };

    switch (entitiesType) {
        case "users":
            filterUsers();
            constructTableUsers(entitiesFilter, showModal);
            break;
        case "posts":
            filterPosts();
            constructTablePosts(entitiesFilter, showModal);
            break;
        case "comments":
            filterComments();
            constructTableComments(entitiesFilter, showModal);
            break;
    }

};

const addListenersOnFilterElement = () => {
    let timeOut = undefined;

    const search = () => {
        showLoadingPage();
        if (typeof timeOut === "number") {
            clearTimeout(timeOut);
        }

        timeOut = setTimeout(() => {
            filterEntities();
            hiddenLoadingPage();
            updatePositionTopForStickyElements();
        }, 500);
    }

    document
        .querySelectorAll("[data-filter]")
        .forEach((element) => {
            switch (element.type) {
                case "select-one":
                    element.addEventListener("change", (event) => {
                        const inputLinkId = event.currentTarget.dataset.filterLink;
                        if (inputLinkId) {
                            const inputLink = document.getElementById(inputLinkId);
                            if (inputLink && inputLink.value) {
                                search();
                            }
                        }
                    });
                    break;
                case "text":
                    element.addEventListener("input", () => {
                        search();
                    });
                    break;
                case "checkbox":
                case "radio":
                    element.addEventListener("change", (event) => {
                        const img = document.querySelector("label[for="+event.currentTarget.id+"] img");
                        if (event.currentTarget.checked) {
                            img.src = img.src.replace("xmark", "check");
                            img.style.filter = "invert(1)";
                        } else {
                            img.src = img.src.replace("check", "xmark");
                            img.style.filter = "invert(0.4)";
                        }
                        search();
                    });
                    break;
            }
        });
}