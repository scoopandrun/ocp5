import {
  fetchItems,
  refreshPagination,
  registerDeleteButtons,
  registerPaginationLinks,
  registerPageSizeSelector,
} from "./tableAjax.js";

/** @type {HTMLTableElement} */
const table = document.getElementById("datatable");
const tableBody = table.querySelector("tbody");

const endpoint = "/admin/users";
let currentPage = 1;

const deleteConfirmationMessage =
  "Êtes-vous sûr(e) de vouloir supprimer cet utilisateur ?";

// On page load
registerDeleteButtons(tableBody, endpoint, deleteConfirmationMessage);
registerPaginationLinks(refreshTable);
registerPageSizeSelector(refreshTable);

async function refreshTable(pageNumber = currentPage) {
  /** @type {HTMLSelectElement} */
  const pageSizeSelector = document.querySelector(".dataTable-selector");

  const { users, paginationInfo } = await fetchItems(
    endpoint,
    pageNumber,
    pageSizeSelector?.value || 10
  );

  currentPage = paginationInfo.currentPage;

  displayUsers(users);

  refreshPagination(paginationInfo, refreshTable);
}

function displayUsers(users) {
  tableBody.innerHTML = "";

  for (const user of users) {
    const tr = document.createElement("tr");
    tr.dataset.id = user.id;

    // Column 1
    const column1 = document.createElement("td");
    tr.appendChild(column1);

    const col1div0 = document.createElement("div");
    column1.appendChild(col1div0);
    col1div0.classList.add("d-flex", "px-2", "py-1");

    const col1div0div0 = document.createElement("div");
    col1div0.appendChild(col1div0div0);
    col1div0div0.classList.add(
      "d-flex",
      "flex-column",
      "justify-content-center"
    );

    const name = document.createElement("h6");
    col1div0div0.appendChild(name);
    name.classList.add("mb-0", "text-xs");
    name.textContent = user.name;

    if (user.isAdmin) {
      const isAdmin = document.createElement("span");
      col1div0div0.appendChild(isAdmin);
      isAdmin.classList.add("badge", "badge-sm", "badge-success");
      isAdmin.textContent = "Admin";
    }

    const mobileOnly = document.createElement("div");
    col1div0div0.appendChild(mobileOnly);
    mobileOnly.classList.add("d-md-none", "mt-1");

    const mobileOnlyEmail = document.createElement("p");
    mobileOnly.appendChild(mobileOnlyEmail);
    mobileOnlyEmail.classList.add("text-xs", "text-secondary", "mb-0");
    mobileOnlyEmail.textContent = user.email;

    if (user.emailVerified) {
      const mobileOnlyEmailVerified = document.createElement("i");
      mobileOnlyEmail.appendChild(mobileOnlyEmailVerified);
      mobileOnlyEmailVerified.classList.add("text-success", "material-icons");
      mobileOnlyEmailVerified.textContent = "check";
    }

    const mobileOnlyCreatedAt = document.createElement("p");
    mobileOnly.appendChild(mobileOnlyCreatedAt);
    mobileOnlyCreatedAt.classList.add("text-xs", "text-secondary", "mb-0");
    mobileOnlyCreatedAt.textContent =
      "Inscrit(e) le " + new Date(user.createdAt).toLocaleDateString();

    // Column 2
    const column2 = document.createElement("td");
    tr.appendChild(column2);
    column2.classList.add("d-none", "d-md-table-cell");

    const col2Email = document.createElement("p");
    column2.appendChild(col2Email);
    col2Email.classList.add("text-xs", "mb-0");
    col2Email.textContent = user.email;

    if (user.emailVerified) {
      const col2EmailVerified = document.createElement("i");
      col2Email.appendChild(col2EmailVerified);
      col2EmailVerified.classList.add("text-success", "material-icons");
      col2EmailVerified.textContent = "check";
    }

    // Column 3
    const column3 = document.createElement("td");
    tr.appendChild(column3);
    column3.classList.add("d-none", "d-md-table-cell");

    const col3CreatedAt = document.createElement("p");
    column3.appendChild(col3CreatedAt);
    col3CreatedAt.classList.add("text-xs", "font-weight-bold", "mb-0");
    col3CreatedAt.textContent = new Date(user.createdAt).toLocaleDateString();

    // Last column
    const lastColumn = document.createElement("td");
    tr.appendChild(lastColumn);
    lastColumn.classList.add("align-middle");

    const lastColDiv0 = document.createElement("div");
    lastColumn.appendChild(lastColDiv0);
    lastColDiv0.classList.add("row", "text-center");

    const lastColumndiv0div0 = document.createElement("div");
    lastColDiv0.appendChild(lastColumndiv0div0);
    lastColumndiv0div0.classList.add("col");

    const editLink = document.createElement("a");
    lastColumndiv0div0.appendChild(editLink);
    editLink.classList.add("text-secondary", "font-weight-normal", "text-xs");
    editLink.href = endpoint + "/" + user.id;
    editLink.title = "Modifier l'utilisateur'";
    editLink.textContent = "Modifier";

    const lastColDiv0div1 = document.createElement("div");
    lastColDiv0.appendChild(lastColDiv0div1);
    lastColDiv0div1.classList.add("col");

    const deleteButton = document.createElement("button");
    lastColDiv0div1.appendChild(deleteButton);
    deleteButton.classList.add("btn", "btn-outline-danger", "btn-sm", "mb-0");
    deleteButton.type = "button";
    deleteButton.title = "Supprimer l'utilisateur";
    deleteButton.dataset.delete = "";

    const deleteButtonText = document.createElement("span");
    deleteButton.appendChild(deleteButtonText);
    deleteButtonText.classList.add("text");
    deleteButtonText.textContent = "Supprimer";

    const deleteButtonSpinner = document.createElement("span");
    deleteButton.appendChild(deleteButtonSpinner);
    deleteButtonSpinner.classList.add(
      "spinner",
      "spinner-border",
      "spinner-border-sm",
      "d-none"
    );
    deleteButtonSpinner.role = "status";
    deleteButtonSpinner.ariaHidden = "true";

    tableBody.appendChild(tr);
  }

  // Refresh delete buttons functionality
  registerDeleteButtons(tableBody, endpoint, deleteConfirmationMessage);
}
