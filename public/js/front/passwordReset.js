/**
 * Password reset (ask e-mail).
 */

/** @type {HTMLFormElement} */
const passwordResetEmailForm = document.getElementById(
  "passwordResetEmailForm"
);

if (passwordResetEmailForm) {
  /** @type {HTMLInputElement} */
  const emailInput = passwordResetEmailForm.querySelector("#email");

  emailInput.oninput = (e) => emailInput.setCustomValidity("");

  const sendButton = passwordResetEmailForm.querySelector("button");

  /** @type {HTMLDivElement} */
  const passwordResetEmailMessage = passwordResetEmailForm.querySelector(
    "#passwordResetEmailMessage"
  );

  const passwordResetEmailMessageCss = {
    success: "fw-bold",
    error: "invalid-feedback",
  };

  passwordResetEmailForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Clear previous messages
    passwordResetEmailMessage.classList.add("d-none");
    passwordResetEmailMessage.classList.remove(
      passwordResetEmailMessageCss.success,
      passwordResetEmailMessageCss.error
    );

    // Check form validity
    if (emailInput.value === "") {
      emailInput.setCustomValidity("Veuillez renseigner l'adresse e-mail.");
      emailInput.reportValidity();
      return;
    }

    // If email OK, send the request
    sendButton.textContent = "Envoi en cours...";
    sendButton.setAttribute("disabled", true);

    const url = "/passwordReset";

    try {
      const response = await fetch(url, {
        method: "POST",
        body: JSON.stringify({
          passwordResetEmailForm: {
            email: emailInput.value,
          },
        }),
        headers: {
          Accept: "application/json",
        },
      });

      if (!response.ok) {
        const message = (await response.json()).message;
        throw new Error(message);
      }

      passwordResetEmailMessage.textContent = (await response.json()).message;
      passwordResetEmailMessage.classList.add(
        passwordResetEmailMessageCss.success
      );
      passwordResetEmailMessage.classList.remove("d-none");
    } catch (error) {
      // Display error message
      passwordResetEmailMessage.textContent = error.message;
      passwordResetEmailMessage.classList.add(
        passwordResetEmailMessageCss.error
      );
      passwordResetEmailMessage.classList.remove("d-none");
    } finally {
      sendButton.textContent = "Envoyer";
      sendButton.removeAttribute("disabled");
    }
  });
}

/**
 * Password reset (change password).
 */

/** @type {HTMLFormElement} */
const passwordResetForm = document.getElementById("passwordResetForm");

if (passwordResetForm) {
  /** @type {HTMLInputElement} */
  const newPasswordInput = passwordResetForm.querySelector("#new-password");
  /** @type {HTMLInputElement} */
  const passwordConfirmInput =
    passwordResetForm.querySelector("#password-confirm");

  newPasswordInput.oninput = (e) => newPasswordInput.setCustomValidity("");
  passwordConfirmInput.oninput = (e) =>
    passwordConfirmInput.setCustomValidity("");

  const sendButton = passwordResetForm.querySelector("button");

  /** @type {HTMLDivElement} */
  const passwordResetMessage = passwordResetForm.querySelector(
    "#passwordResetMessage"
  );

  const passwordResetMessageCss = {
    success: "fw-bold",
    error: "invalid-feedback",
  };

  passwordResetForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Clear previous messages
    passwordResetMessage.classList.add("d-none");
    passwordResetMessage.classList.remove(
      passwordResetMessageCss.success,
      passwordResetMessageCss.error
    );

    // Check form validity
    if (newPasswordInput.value === "") {
      newPasswordInput.setCustomValidity("Le mot de passe est obligatoire.");
      newPasswordInput.reportValidity();
      return;
    }

    if (passwordConfirmInput.value === "") {
      passwordConfirmInput.setCustomValidity(
        "Le mot de passe doit être retapé dans ce champ."
      );
      passwordConfirmInput.reportValidity();
      return;
    }

    if (newPasswordInput.value !== passwordConfirmInput.value) {
      passwordConfirmInput.setCustomValidity(
        "Le mot de passe n'a pas été correctement retapé."
      );
      passwordConfirmInput.reportValidity();
      return;
    }

    // If fields OK, send the request
    sendButton.textContent = "Changement en cours...";
    sendButton.setAttribute("disabled", true);

    const token = window.location.href.split("/").findLast((part) => part);

    const url = "/passwordReset/" + token;

    try {
      const response = await fetch(url, {
        method: "POST",
        body: JSON.stringify({
          passwordResetForm: {
            "new-password": newPasswordInput.value,
            "password-confirm": passwordConfirmInput.value,
          },
        }),
        headers: {
          Accept: "application/json",
        },
      });

      if (!response.ok) {
        const message = (await response.json()).message;
        throw new Error(message);
      }

      passwordResetMessage.textContent = (await response.json()).message;
      passwordResetMessage.classList.add(passwordResetMessageCss.success);
      passwordResetMessage.classList.remove("d-none");
      sendButton.remove();
    } catch (error) {
      // Display error message
      passwordResetMessage.textContent = error.message;
      passwordResetMessage.classList.add(passwordResetMessageCss.error);
      passwordResetMessage.classList.remove("d-none");
      sendButton.textContent = "Envoyer";
      sendButton.removeAttribute("disabled");
    }
  });
}
