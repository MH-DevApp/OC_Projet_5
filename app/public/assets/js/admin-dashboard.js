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

    const showModal = (event, idEntity = null) => {
        clearNotification(modalShowEntities.querySelector("div.notification"));
        modalShowEntities.dataset.entityId = idEntity = idEntity ?? event.currentTarget.dataset.entityId;

        const cols = document.querySelectorAll("tr[data-entity-id='"+idEntity+"'] td[data-col]");

        cols.forEach((col) => {
            const rowModal = modalShowEntities.querySelector("[data-col="+col.dataset.col+"]");
            rowModal.innerHTML = col.innerHTML;
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

export const updateEntities = (newEntities) => {
    entities = newEntities;
}

export const constructBtnActions = (btnInfos, elementHTML) => {
    btnInfos.forEach((btnInfo) => {
        const btn = document.createElement("button");
        const img = document.createElement("img");

        btn.className = "btn btn-"+btnInfo.color+" btn-sm px-2";
        btn.type = "button"
        btn.style.marginLeft = "2px";
        btn.style.marginRight = "2px";

        if (btnInfo.dataset) {
            btnInfo.dataset.forEach((dataset) => {
                btn.dataset[dataset.key] = dataset.value;
            });
        }

        if (btnInfo.events) {
            btnInfo.events.forEach((event) => {
                btn.addEventListener(event.event, event.func);
            });
        }

        img.src = "/assets/images/logos/"+btnInfo.imgName;
        img.alt = btnInfo.altName;
        img.width = 12;
        btn.append(img);

        elementHTML.append(btn);
    });
};
