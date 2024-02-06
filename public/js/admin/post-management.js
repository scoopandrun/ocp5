const postsTable = document.getElementById("datatable");
const postRows = postsTable.querySelectorAll("tbody > tr");

postRows.forEach((row) => {
  const postId = row.dataset.postId;
  const deleteURI = "/admin/posts/" + postId;

  const deleteButton = row.querySelector("button[data-delete]");

  if (!deleteButton) return;

  deleteButton.addEventListener("click", async (e) => {
    e.preventDefault();

    const deleteConfirmed = confirm(
      "Êtes-vous sûr(e) de vouloir supprimer ce post ?"
    );

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
});
