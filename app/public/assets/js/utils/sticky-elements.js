export const updatePositionTopForStickyElements = () => {
    const navbar = document.querySelector("header#navbarContainer .navbar");
    const filterContainer = document.getElementById("filterContainer");
    const table = document.querySelector("table thead.sticky-top");
    const actionsContainer = document.querySelector("div#actionsContainer");

    if (filterContainer) {
        filterContainer.style.top = navbar.getBoundingClientRect().bottom + "px";
    }

    if (actionsContainer) {
        actionsContainer.style.top = filterContainer ?
            filterContainer.getBoundingClientRect().bottom + "px" :
            navbar.getBoundingClientRect().bottom + "px";
    }

    if (table) {
        table.style.top = actionsContainer ?
            filterContainer.getBoundingClientRect().bottom + actionsContainer.getBoundingClientRect().height + "px" :
            filterContainer ?
                filterContainer.getBoundingClientRect().bottom + "px" :
                navbar.getBoundingClientRect().bottom + "px";
    }
}

export const initStickyElements = () => {
    const observerUpdatePositionTopForStickyElements = new ResizeObserver((element) => {
        if (element[0].target !== undefined) {
            const currentElement = element[0].target;
            const navBarHeight = document.querySelector("header#navbarContainer .navbar").offsetHeight;

            switch (currentElement.id) {
                case "navbarText":
                    const filterContainer = document.getElementById("filterContainer");
                    filterContainer.style.top = navBarHeight+"px";
                    updatePositionTopForStickyElements();
                    break;
                case "filterContainer":
                    updatePositionTopForStickyElements();
                    break;
            }
        }
    });

    observerUpdatePositionTopForStickyElements.observe(document.getElementById("filterContainer"));

    updatePositionTopForStickyElements();

    window.addEventListener("scroll", () => {
        updatePositionTopForStickyElements();
    });
}