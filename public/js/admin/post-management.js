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

const endpoint = "/admin/posts";
let currentPage = 1;

const deleteConfirmationMessage =
  "Êtes-vous sûr(e) de vouloir supprimer ce post ?";

// On page load
registerDeleteButtons(tableBody, endpoint, deleteConfirmationMessage);
registerPaginationLinks(refreshTable);
registerPageSizeSelector(refreshTable);

async function refreshTable(pageNumber = currentPage) {
  /** @type {HTMLSelectElement} */
  const pageSizeSelector = document.querySelector(".dataTable-selector");

  const { posts, paginationInfo } = await fetchItems(
    endpoint,
    pageNumber,
    pageSizeSelector?.value || 10
  );

  currentPage = paginationInfo.currentPage;

  displayPosts(posts);

  refreshPagination(paginationInfo, refreshTable);
}

function displayPosts(posts) {
  tableBody.innerHTML = "";

  for (const post of posts) {
    const tr = document.createElement("tr");
    tr.dataset.id = post.id;

    // Column 1
    const column1 = document.createElement("td");

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

    const title = document.createElement("h6");
    col1div0div0.appendChild(title);
    title.classList.add("mb-0", "text-xs");
    title.textContent = post.title;

    const leadParagraph = document.createElement("p");
    col1div0div0.appendChild(leadParagraph);
    leadParagraph.classList.add("text-xs", "text-secondary", "mb-0");
    leadParagraph.textContent = post.leadParagraph;

    const mobileOnly = document.createElement("div");
    col1div0div0.appendChild(mobileOnly);
    mobileOnly.classList.add("d-md-none", "mt-1");

    if (post.category) {
      const mobileOnlyCategory = document.createElement("p");
      mobileOnly.appendChild(mobileOnlyCategory);
      mobileOnlyCategory.classList.add("text-xs", "text-secondary", "mb-0");
      mobileOnlyCategory.textContent = "Dans " + post.category;
    }

    const mobileOnlyCreatedAt = document.createElement("p");
    mobileOnly.appendChild(mobileOnlyCreatedAt);
    mobileOnlyCreatedAt.classList.add("text-xs", "text-secondary", "mb-0");
    mobileOnlyCreatedAt.textContent =
      "Créé le " + new Date(post.createdAt).toLocaleDateString();

    if (post.updatedAt) {
      const mobileOnlyUpdatedAt = document.createElement("p");
      mobileOnly.appendChild(mobileOnlyUpdatedAt);
      mobileOnlyUpdatedAt.classList.add("text-xs", "text-secondary", "mb-0");
      mobileOnlyUpdatedAt.textContent =
        "Modifié le " + new Date(post.updatedAt).toLocaleDateString();
    }

    const mobilOnlyIsPublished = document.createElement("span");
    mobileOnly.appendChild(mobilOnlyIsPublished);
    mobilOnlyIsPublished.classList.add(
      "badge",
      "badge-sm",
      post.isPublished ? "badge-success" : "badge-secondary"
    );
    mobilOnlyIsPublished.textContent = post.isPublished
      ? "Publié"
      : "Non publié";

    // Column 2
    const column2 = document.createElement("td");
    column2.classList.add("d-none", "d-md-table-cell");

    const col2CreatedAt = document.createElement("p");
    column2.appendChild(col2CreatedAt);
    col2CreatedAt.classList.add("text-xs", "font-weight-bold", "mb-0");
    col2CreatedAt.textContent = new Date(post.createdAt).toLocaleDateString();

    if (post.updatedAt) {
      const col2UpdatedAt = document.createElement("p");
      column2.appendChild(col2UpdatedAt);
      col2UpdatedAt.classList.add("text-xs", "text-secondary", "mb-0");
      col2UpdatedAt.textContent = new Date(post.updatedAt).toLocaleDateString();
    }

    // Column 3
    const column3 = document.createElement("td");
    column3.classList.add("d-none", "d-md-table-cell");

    const col3Category = document.createElement("p");
    column3.appendChild(col3Category);
    col3Category.classList.add("text-xs", "mb-0");
    col3Category.textContent = post.category;

    // Column 4
    const column4 = document.createElement("td");
    column4.classList.add(
      "align-middle",
      "text-center",
      "text-sm",
      "d-none",
      "d-md-table-cell"
    );

    const col4IsPublished = document.createElement("span");
    column4.appendChild(col4IsPublished);
    col4IsPublished.classList.add(
      "badge",
      "badge-sm",
      post.isPublished ? "badge-success" : "badge-secondary"
    );
    col4IsPublished.textContent = post.isPublished ? "Publié" : "Non publié";

    // Column 5
    const column5 = document.createElement("td");
    column5.classList.add("align-middle");

    const col5div0 = document.createElement("div");
    column5.appendChild(col5div0);
    col5div0.classList.add("row", "text-center");

    const col5div0div0 = document.createElement("div");
    col5div0.appendChild(col5div0div0);
    col5div0div0.classList.add("col");

    const editLink = document.createElement("a");
    col5div0div0.appendChild(editLink);
    editLink.classList.add("text-secondary", "font-weight-normal", "text-xs");
    editLink.href = "/admin/posts/" + post.id;
    editLink.title = "Modifier le post";
    editLink.textContent = "Modifier";

    const col5div0div1 = document.createElement("div");
    col5div0.appendChild(col5div0div1);
    col5div0div1.classList.add("col");

    const deleteButton = document.createElement("button");
    col5div0div1.appendChild(deleteButton);
    deleteButton.classList.add("btn", "btn-outline-danger", "btn-sm", "mb-0");
    deleteButton.type = "button";
    deleteButton.title = "Supprimer le post";
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

    tr.appendChild(column1);
    tr.appendChild(column2);
    tr.appendChild(column3);
    tr.appendChild(column4);
    tr.appendChild(column5);

    tableBody.appendChild(tr);
  }

  // Refresh delete buttons functionality
  registerDeleteButtons(tableBody, endpoint, deleteConfirmationMessage);
}
