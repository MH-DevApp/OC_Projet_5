const containerNotification = document.querySelector("div.modal-lg .notification");

export const addNotification = (data) => {
    if (data.success !== undefined && data.message !== undefined) {
        const notificationDiv = document.createElement("div");
        const notificationPara = document.createElement("p");

        containerNotification.innerHTML = "";

        notificationDiv.className = "p-0 mb-3 w-100 text-center rounded shadow alert alert-" + (data.success === true ? "success" : "danger");

        notificationPara.textContent = data.message;
        notificationPara.className = "m-0 p-1 p-sm-3";

        notificationDiv.append(notificationPara);
        containerNotification.append(notificationDiv);

        setTimeout(() => {
            removeNotification(notificationDiv);
        }, 5000);
    }
}

export const removeNotification = (element) => {
    if (element) {
        element.remove();
    }
}

export const clearNotification = () => {
    containerNotification.innerHTML = "";
};