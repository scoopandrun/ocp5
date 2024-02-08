/**
 * @param {string} endpoint
 * @param {number} pageNumber
 * @param {number} pageSize
 */
export async function fetchItems(endpoint, pageNumber, pageSize) {
  const url = new URL(endpoint, location.protocol + "//" + location.host);

  url.search = new URLSearchParams({
    page: pageNumber,
    limit: pageSize,
  });

  try {
    const response = await fetch(url, {
      headers: { Accept: "application/json" },
    });

    if (!response.ok) {
      throw new Error("Erreur");
    }

    return await response.json();
  } catch (error) {
    console.error(error);
  }
}

/**
 * Rebuild the pagination of an admin table.
 *
 * @param {object}   paginationInfo
 * @param {number}   paginationInfo.pageSize
 * @param {number}   paginationInfo.currentPage
 * @param {number}   paginationInfo.previousPage
 * @param {number}   paginationInfo.nextPage
 * @param {number}   paginationInfo.lastPage
 * @param {number}   paginationInfo.firstItem
 * @param {number}   paginationInfo.lastItem
 * @param {number}   paginationInfo.itemCount
 * @param {string}   paginationInfo.itemName
 * @param {Function} refreshTable Function to execute on on link navigation
 */
export function refreshPagination(paginationInfo, refreshTable) {
  document.getElementById("first-item").textContent = paginationInfo.firstItem;
  document.getElementById("last-item").textContent = paginationInfo.lastItem;
  document.getElementById("item-count").textContent = paginationInfo.itemCount;

  const paginationList = document.querySelector(".dataTable-pagination-list");
  /** @type {HTMLAnchorElement} */
  const previousPageLink = document.getElementById("previous-page");
  /** @type {HTMLAnchorElement} */
  const nextPageLink = document.getElementById("next-page");

  const url = location.pathname;

  previousPageLink.href = url + "?page=" + paginationInfo.previousPage;
  previousPageLink.dataset.page = paginationInfo.previousPage;
  nextPageLink.href = url + "?page=" + paginationInfo.nextPage;
  nextPageLink.dataset.page = paginationInfo.nextPage;

  paginationList
    .querySelectorAll(".page-link")
    .forEach((item) => item.remove());

  for (let i = 1; i <= paginationInfo.lastPage; i++) {
    const li = document.createElement("li");
    const a = document.createElement("a");

    li.classList.add("page-link");

    if (i == paginationInfo.currentPage) {
      li.classList.add("active");
    }

    a.href = url + "?page=" + i;
    a.dataset.page = i;
    a.textContent = i;

    a.addEventListener("click", (e) => {
      e.preventDefault();

      refreshTable(a.dataset.page);
    });

    li.appendChild(a);

    nextPageLink.parentElement.before(li);
  }
}

/**
 * @param {HTMLTableSectionElement} tableBody
 * @param {string}                  endpoint
 * @param {string}                  confirmationMessage
 */
export function registerDeleteButtons(
  tableBody,
  endpoint,
  confirmationMessage
) {
  const rows = tableBody.rows;

  for (const row of rows) {
    const id = row.dataset.id;
    const deleteURI = endpoint + id;

    const deleteButton = row.querySelector("button[data-delete]");

    if (!deleteButton) continue;

    deleteButton.addEventListener("click", async (e) => {
      e.preventDefault();

      const deleteConfirmed = confirm(confirmationMessage);

      if (!deleteConfirmed) return;

      try {
        deleteButton.setAttribute("disabled", true);
        deleteButton.querySelector(".text").classList.toggle("d-none");
        deleteButton.querySelector(".spinner").classList.toggle("d-none");

        const response = await fetch(deleteURI, {
          method: "DELETE",
        });

        if (!response.ok) {
          throw new Error("Erreur lors de la suppression");
        }

        row.remove();
      } catch (e) {
        console.error(e);
        deleteButton.removeAttribute("disabled");
        deleteButton.querySelector(".text").classList.toggle("d-none");
        deleteButton.querySelector(".spinner").classList.toggle("d-none");
      }
    });
  }
}

/**
 * @param {Function} refreshTable Function to execute on link navigation
 */
export function registerPaginationLinks(refreshTable) {
  /** @type {HTMLUListElement} */
  const paginationList = document.querySelector(".dataTable-pagination-list");

  paginationList.addEventListener("click", async (e) => {
    e.preventDefault();

    /** @type {HTMLAnchorElement} */
    const link = e.target;

    refreshTable(link.dataset.page);
  });
}

/**
 * @param {Function} refreshTable Function to execute on page size change
 */
export function registerPageSizeSelector(refreshTable) {
  /** @type {HTMLDivElement} */
  const datatableTop = document.querySelector(".dataTable-top");

  if (!datatableTop) return;

  datatableTop.removeAttribute("hidden");

  /** @type {HTMLSelectElement} */
  const pageSizeSelector = datatableTop.querySelector(".dataTable-selector");

  if (!pageSizeSelector) return;

  pageSizeSelector.addEventListener("change", refreshTable);
}
