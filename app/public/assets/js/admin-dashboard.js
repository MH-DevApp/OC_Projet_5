import {initUsers} from "./admin-dashboard-users.js";
import {initPosts} from "./admin-dashboard-posts.js";
import {initComments} from "./admin-dashboard-comments.js";
import {initFilter} from "./utils/filter.js";
import {showLoadingPage, hiddenLoadingPage} from "./utils/loading-page.js";
import {clearNotification} from "./utils/notification.js";

export let entities = [];

showLoadingPage();

// SHOW MODAL

const modal = document.querySelector("div.modal-lg");

if (modal) {
    const btnShowModal = document.querySelector("button[type=button][data-bs-toggle=modal]");
    const typeEntities = modal.id.replace("modal", "").toLowerCase();
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

    if (typeEntities === "users") {
        initUsers(modal);
    } else if (typeEntities === "posts") {
        initPosts(modal);
    } else if (typeEntities === "comments") {
        initComments(modal);
    }

    fetch("/admin/dashboard/entities/"+typeEntities)
        .then((response) => {
            if (response.ok) {
                return response.json();
            }
            return null;
        })
        .then((response) => {
            if (response && response.success) {
                entities = response.entities;
                initFilter(typeEntities, showModal);
            }
        })
        .catch((error) => {
            console.log(error);
        })
        .finally(() => {
            hiddenLoadingPage();
        });
}
