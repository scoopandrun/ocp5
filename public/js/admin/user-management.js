const usersTable = document.getElementById("datatable");
const userRows = usersTable.querySelectorAll("tbody > tr");

userRows.forEach((row) => {
  const userId = row.dataset.userId;
  const deleteURI = "/admin/users/" + userId;

  const deleteButton = row.querySelector("button[data-delete]");

  deleteButton.addEventListener("click", async (e) => {
    e.preventDefault();

    const deleteConfirmed = confirm(
      "Êtes-vous sûr(e) de vouloir supprimer cet utilisateur ?"
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
