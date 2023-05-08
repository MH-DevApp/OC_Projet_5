import {initUsers} from "./admin-dashboard-users.js";
import {initPosts} from "./admin-dashboard-posts.js";
import {initComments} from "./admin-dashboard-comments.js";
import {initFilter} from "./utils/filter.js";
import {showLoadingPage, hiddenLoadingPage} from "./utils/loading-page.js";
import {clearNotification} from "./utils/notification.js";
import {initStickyElements} from "./utils/sticky-elements.js";

export let entities = [];

showLoadingPage();

// STICKY ELEMENTS

initStickyElements();

// SHOW MODAL

const modalShowEntities = document.querySelector("div.modal[data-modal=showEntities]");

if (modalShowEntities) {
    const btnShowModal = document.querySelector("button[type=button][data-bs-toggle=modal]");
    const typeEntities = modalShowEntities.id.replace("modal", "").toLowerCase();
    let id = null;

    const showModal = (event) => {
        clearNotification(modalShowEntities.querySelector("div.notification"));
        id = event.currentTarget.dataset.entityId;
        modalShowEntities.dataset.entityId = id;

        const cols = event.currentTarget.querySelectorAll("td[data-col]");

        cols.forEach((col) => {
            const rowModal = modalShowEntities.querySelector("[data-col="+col.dataset.col+"]");
            rowModal.textContent = col.textContent;
        });

        btnShowModal.click();
    }

    if (typeEntities === "users") {
        initUsers(modalShowEntities);
    } else if (typeEntities === "posts") {
        initPosts(modalShowEntities);
    } else if (typeEntities === "comments") {
        initComments(modalShowEntities);
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
