const adminDashboardContent = document.getElementById("admin-dashboard-content");
const tableEntities = adminDashboardContent.querySelector("table");
const loadingPage = adminDashboardContent.querySelector("#loading-page");

// LOADING PAGE

export const showLoadingPage = () => {
    tableEntities.classList.add("d-none");
    loadingPage.classList.remove("d-none");
    loadingPage.classList.add("d-flex");
};

export const hiddenLoadingPage = () => {
    loadingPage.classList.add("d-none");
    loadingPage.classList.remove("d-flex");
    tableEntities.classList.remove("d-none");
}