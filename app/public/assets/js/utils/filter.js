import {entities} from "../admin-dashboard.js";
import {constructTableUsers} from "../admin-dashboard-users.js";
import {hiddenLoadingPage, showLoadingPage} from "./loading-page.js";

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
    switch (entitiesType) {
        case "users":
            /** @type {HTMLSelectElement} */
            const typeEntity = document.querySelector("select#selectTypeEntity");
            /** @type {HTMLInputElement} */
            const inputSearchEntity = document.querySelector("input#inputSearchEntity");
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

            entitiesFilter = entities.filter(function(entity) {
                if (inputSearchEntity) {
                    if (!entity[typeEntity.value].toLowerCase().includes(inputSearchEntity.value.toLowerCase())) {
                        return false;
                    }
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

            constructTableUsers(entitiesFilter, showModal);
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
                    element.addEventListener("change", (event) => {
                        const img = document.querySelector("label[for="+event.currentTarget.id+"] img");
                        if (event.currentTarget.checked) {
                            img.src = img.src.replace("xmark", "check");
                            img.style.filter = "invert(1)";
                        } else {
                            img.src = img.src.replace("check", "xmark");
                            img.style.filter = "invert(0.4)";
                        }
                        console.log(img);
                        search();
                    });
                    break;
            }
        });
}