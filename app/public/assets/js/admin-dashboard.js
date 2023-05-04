import {constructTableUsers, initUsers} from "./admin-dashboard-users.js";
import {showLoadingPage, hiddenLoadingPage} from "./utils/loading-page.js";
import {clearNotification} from "./utils/notification.js";
import {initFilter} from "./utils/filter.js";

export let entities = [];

showLoadingPage();

// SHOW MODAL

const modal = document.querySelector("div.modal-lg");

if (modal) {
    const btnShowModal = document.querySelector("button[type=button][data-bs-toggle=modal]");
    let id = null;

    const showModal = (event) => {
        clearNotification();
        id = event.currentTarget.dataset.entityId;
        modal.dataset.entityId = id;

        const cols = event.currentTarget.querySelectorAll("td[data-col]");

        cols.forEach((col) => {
            const rowModal = modal.querySelector("[data-col="+col.dataset.col+"]");
            rowModal.textContent = col.textContent;
        });

        btnShowModal.click();
    }

    if (modal.id === "modalUsers") {

        initUsers(modal);

        fetch("/admin/dashboard/entities/users")
            .then((response) => {
                if (response.ok) {
                    return response.json();
                }
                return null;
            })
            .then((response) => {
                if (response && response.success) {
                    entities = response.entities;
                    initFilter("users", showModal);
                }
            })
            .catch((error) => {
                console.log(error);
            })
            .finally(() => {
                hiddenLoadingPage();
            });

    }
}
