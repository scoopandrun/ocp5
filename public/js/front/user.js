/**
 * Account deletion.
 */

/** @type {HTMLAnchorElement} */
const deleteAccountButton = document.getElementById("deleteAccountButton");

if (deleteAccountButton) {
  deleteAccountButton.addEventListener("click", async (e) => {
    e.preventDefault();

    const deleteConfirmed = confirm(
      "Êtes-vous sûr(e) de vouloir supprimervotre compte ?"
    );

    if (!deleteConfirmed) return;

    deleteAccountButton.setAttribute("disabled", true);

    try {
      const response = await fetch("/user", {
        method: "DELETE",
        headers: {
          Accept: "application/json",
        },
      });

      if (!response.ok) {
        const message = (await response.json()).message;
        throw new Error(message);
      }

      window.location = "/";
    } catch (error) {
      // Display error message
      /** @type {HTMLDivElement} */
      const deleteAccountErrorMessage = document.getElementById(
        "deleteAccountErrorMessage"
      );
      deleteAccountErrorMessage.textContent = error.message;
      deleteAccountErrorMessage.classList.toggle("d-none");

      deleteAccountButton.removeAttribute("disabled");
    }
  });
}
