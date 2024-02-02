/**
 * Comment deletion.
 */

/** @type {HTMLUListElement} */
const commentList = document.getElementById("commentList");

if (commentList) {
  /** @type {NodeListOf<HTMLLIElement>} */
  const comments = commentList.querySelectorAll(".js-comment");

  const deleteFormResultMessageCss = {
    success: ["fw-bold", "text-success"],
    error: "invalid-feedback",
  };

  comments.forEach((comment) => {
    const commentId = comment.dataset.commentId;

    console.log({ commentId });

    /** @type {HTMLFormElement} */
    const deleteForm = comment.querySelector(".js-delete-form");

    if (!deleteForm) return;

    const deleteButton = deleteForm.querySelector("button");

    /** @type {HTMLDivElement} */
    const deleteFormResult = comment.querySelector(".js-delete-form-result");

    deleteForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      deleteFormResult.classList.add("d-none");

      try {
        deleteButton.textContent = "Suppression...";
        deleteButton.setAttribute("disabled", true);

        const response = await fetch("/posts/0/comments/" + commentId, {
          method: "DELETE",
          headers: {
            Accept: "application/json",
          },
        });

        const message =
          (await response.json())?.message || "Une erreur est survenue.";

        if (!response.ok) {
          throw new Error(message);
        }

        comment.textContent = message;
        comment.classList.add(...deleteFormResultMessageCss.success);
      } catch (error) {
        deleteFormResult.textContent = error.message;
        deleteFormResult.classList.add(deleteFormResultMessageCss.error);
        deleteFormResult.classList.remove("d-none");
        deleteButton.textContent = "Supprimer";
        deleteButton.removeAttribute("disabled");
      }
    });
  });
}
