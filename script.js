"use strict";

document.addEventListener("DOMContentLoaded", () => {
  setupMobileNavigation();
  setupThemeButton();
  setupWelcomeMessage();
  setupHomeInteractions();
  setupFormValidation();
  setupGallerySelection();
  setupCatalogueSearch();
});

function setupMobileNavigation() {
  const header = document.querySelector("header");
  const navigation = header?.querySelector("nav");
  if (!header || !navigation) return;

  navigation.id ||= "main-navigation";

  const button = document.createElement("button");
  button.type = "button";
  button.className = "nav-toggle";
  button.setAttribute("aria-controls", navigation.id);
  button.setAttribute("aria-expanded", "false");
  button.innerHTML = '<span aria-hidden="true">☰</span><span>Menu</span>';

  header.classList.add("nav-enhanced");
  navigation.before(button);

  const closeMenu = () => {
    header.classList.remove("nav-open");
    button.setAttribute("aria-expanded", "false");
    button.querySelector("span:last-child").textContent = "Menu";
  };

  button.addEventListener("click", () => {
    const isOpen = header.classList.toggle("nav-open");
    button.setAttribute("aria-expanded", String(isOpen));
    button.querySelector("span:last-child").textContent = isOpen ? "Close" : "Menu";
  });

  navigation.addEventListener("click", (event) => {
    if (event.target.closest("a")) closeMenu();
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && header.classList.contains("nav-open")) {
      closeMenu();
      button.focus();
    }
  });

  window.matchMedia("(min-width: 561px)").addEventListener("change", (event) => {
    if (event.matches) closeMenu();
  });
}

function setupCatalogueSearch() {
  const form = document.querySelector("#catalogue-search");
  const input = document.querySelector("#search");
  const clearButton = document.querySelector("#clear-search");
  const status = document.querySelector("#search-status");
  if (!form || !input || !clearButton || !status) return;

  const rows = [...document.querySelectorAll(".book-row")];
  const filterBooks = () => {
    const query = input.value.trim().toLowerCase();
    let matches = 0;
    rows.forEach((row) => {
      const visible = !query || row.textContent.toLowerCase().includes(query);
      row.hidden = !visible;
      if (visible) matches += 1;
    });
    status.textContent = query
      ? `${matches} book${matches === 1 ? "" : "s"} found for “${input.value.trim()}”.`
      : `Showing all ${matches} catalogue books.`;
    status.classList.toggle("success-message", Boolean(query && matches));
    status.classList.toggle("error-message", Boolean(query && !matches));
  };

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    filterBooks();
  });
  input.addEventListener("input", filterBooks);
  clearButton.addEventListener("click", () => {
    input.value = "";
    filterBooks();
    input.focus();
  });
}

function setupThemeButton() {
  const navigation = document.querySelector("nav");

  if (!navigation) return;

  let savedTheme = "";
  try {
    savedTheme = localStorage.getItem("moonlightTheme") || "";
  } catch (error) {
    savedTheme = "";
  }

  const systemPrefersWarm = window.matchMedia("(prefers-color-scheme: light)").matches;
  const warmThemeIsOn = savedTheme ? savedTheme === "warm" : systemPrefersWarm;
  document.body.classList.toggle("warm-theme", warmThemeIsOn);

  const button = document.createElement("button");
  button.type = "button";
  button.className = "theme-button";
  button.setAttribute("aria-pressed", String(warmThemeIsOn));
  button.textContent = warmThemeIsOn ? "Navy theme" : "Warm theme";
  navigation.appendChild(button);

  button.addEventListener("click", () => {
    const warmThemeIsOn = document.body.classList.toggle("warm-theme");
    button.setAttribute("aria-pressed", String(warmThemeIsOn));
    button.textContent = warmThemeIsOn ? "Navy theme" : "Warm theme";
    try {
      localStorage.setItem("moonlightTheme", warmThemeIsOn ? "warm" : "navy");
    } catch (error) {
      // The theme still works for the current page when storage is unavailable.
    }
  });
}

function setupWelcomeMessage() {
  const welcomeMessage = document.querySelector("#welcome-message");
  const welcomeNote = document.querySelector("#welcome-note");
  const welcomeForm = document.querySelector("#welcome-form");
  const nameInput = document.querySelector("#visitor-name");
  const clearNameButton = document.querySelector("#clear-name");

  if (!welcomeMessage || !welcomeNote || !welcomeForm || !nameInput || !clearNameButton) return;

  let savedName = "";

  try {
    savedName = localStorage.getItem("moonlightVisitorName") || "";
  } catch (error) {
    savedName = "";
  }

  const saveName = () => {
    const cleanName = nameInput.value.trim().replace(/\s+/g, " ").slice(0, 40);

    if (!cleanName) {
      welcomeMessage.textContent = "Welcome, reader!";
      welcomeNote.textContent = "Enter your name if you would like a personalised welcome.";
      nameInput.focus();
      return;
    }

    savedName = cleanName;
    welcomeMessage.textContent = `Welcome, ${cleanName}!`;
    welcomeNote.textContent = "We saved a seat for you among the stories.";

    try {
      localStorage.setItem("moonlightVisitorName", cleanName);
    } catch (error) {
      // The personalised message still works when browser storage is unavailable.
    }
  };

  if (savedName) {
    welcomeMessage.textContent = `Welcome back, ${savedName}!`;
    welcomeNote.textContent = "It is good to see you among the shelves again.";
    nameInput.value = savedName;
  }

  welcomeForm.addEventListener("submit", (event) => {
    event.preventDefault();
    saveName();
  });

  clearNameButton.addEventListener("click", () => {
    savedName = "";
    nameInput.value = "";
    welcomeMessage.textContent = "Welcome to Moonlight Library!";
    welcomeNote.textContent = "Your saved name has been removed.";
    try {
      localStorage.removeItem("moonlightVisitorName");
    } catch (error) {
      // The visible welcome is still cleared when storage is unavailable.
    }
    nameInput.focus();
  });
}

function setupHomeInteractions() {
  const recommendationButton = document.querySelector("#recommend-book");
  const recommendation = document.querySelector("#book-recommendation");
  const factButton = document.querySelector("#toggle-fact");
  const fact = document.querySelector("#library-fact");

  const books = [
    "Try Things Fall Apart by Chinua Achebe — a landmark African novel.",
    "Try Dust by Yvonne Adhiambo Owuor — a powerful Kenyan family story.",
    "Try The Little Prince by Antoine de Saint-Exupéry — a short story with big ideas.",
    "Try A Brief History of Time by Stephen Hawking — explore the mysteries of our universe.",
    "Try Weep Not, Child by Ngũgĩ wa Thiong'o — history seen through a young Kenyan life."
  ];

  if (recommendationButton && recommendation) {
    recommendationButton.addEventListener("click", () => {
      const currentText = recommendation.textContent;
      const alternatives = books.filter((book) => book !== currentText);
      recommendation.textContent = alternatives[Math.floor(Math.random() * alternatives.length)];
      recommendation.classList.add("content-highlight");
      window.setTimeout(() => recommendation.classList.remove("content-highlight"), 650);
    });
  }

  if (factButton && fact) {
    factButton.addEventListener("click", () => {
      const isNowHidden = fact.classList.toggle("is-hidden");
      factButton.setAttribute("aria-expanded", String(!isNowHidden));
      factButton.textContent = isNowHidden ? "Show library fact" : "Hide library fact";
    });
  }
}

function setupFormValidation() {
  document.querySelectorAll('#membership-form, form[data-demo-form]').forEach((form) => {
    form.noValidate = true;
    let message = form.querySelector(".form-message");

    if (!message) {
      message = document.createElement("div");
      message.className = "form-message";
      message.setAttribute("aria-live", "polite");
      form.prepend(message);
    }

    form.addEventListener("submit", (event) => {
      clearFieldErrors(form);

      const invalidFields = [...form.querySelectorAll("[required]")].filter((field) => {
        if (field.type === "radio") {
          return !form.querySelector(`input[name="${field.name}"]:checked`);
        }
        if (field.type === "checkbox") return !field.checked;
        return !field.value.trim() || !field.checkValidity();
      });

      const uniqueInvalidFields = invalidFields.filter((field, index, fields) =>
        fields.findIndex((item) => item.name === field.name) === index
      );

      if (uniqueInvalidFields.length > 0) {
        event.preventDefault();
        uniqueInvalidFields.forEach((field) => {
          field.classList.add("input-error");
          field.setAttribute("aria-invalid", "true");
          showFieldError(field);
        });
        message.className = "form-message error-message";
        message.textContent = `Please complete ${uniqueInvalidFields.length} required field${uniqueInvalidFields.length === 1 ? "" : "s"} before submitting.`;
        uniqueInvalidFields[0].focus();
        return;
      }

      if (form.id === "membership-form") {
        message.className = "form-message success-message";
        message.textContent = "Validation passed. Sending the application securely to PHP...";
        return;
      }

      event.preventDefault();
      message.className = "form-message success-message";
      message.textContent = "Demo complete: your information passed validation, but it was not sent or saved.";
      form.reset();
    });

    form.addEventListener("input", (event) => {
      const field = event.target;
      if (field.matches("input, select, textarea")) {
        field.classList.remove("input-error");
        field.removeAttribute("aria-invalid");
        removeFieldError(field);
      }
    });

    form.addEventListener("reset", () => {
      clearFieldErrors(form);
      message.className = "form-message";
      message.textContent = "";
    });
  });
}

function clearFieldErrors(form) {
  form.querySelectorAll(".input-error").forEach((field) => {
    field.classList.remove("input-error");
    field.removeAttribute("aria-invalid");
  });
  form.querySelectorAll(".field-error").forEach((error) => error.remove());
}

function showFieldError(field) {
  const error = document.createElement("span");
  const errorId = `${field.id || field.name}-error`;
  error.id = errorId;
  error.className = "field-error";
  error.textContent = getFieldErrorMessage(field);

  if (field.type === "radio") {
    const group = field.closest("p");
    group.appendChild(error);
    field.form.querySelectorAll(`input[name="${field.name}"]`).forEach((radio) => {
      radio.setAttribute("aria-describedby", errorId);
    });
  } else {
    field.insertAdjacentElement("afterend", error);
    field.setAttribute("aria-describedby", errorId);
  }
}

function removeFieldError(field) {
  const errorId = `${field.id || field.name}-error`;
  document.getElementById(errorId)?.remove();
  field.form.querySelectorAll(`[name="${field.name}"]`).forEach((item) => {
    item.removeAttribute("aria-describedby");
    item.classList.remove("input-error");
    item.removeAttribute("aria-invalid");
  });
}

function getFieldErrorMessage(field) {
  const label = field.labels?.[0]?.textContent.replace(/[:.]\s*$/, "").trim() || "This field";
  if (field.validity.typeMismatch && field.type === "email") return "Enter a valid email address.";
  if (field.validity.patternMismatch) return `${label} is not in the expected format.`;
  if (field.type === "checkbox") return "Select this checkbox to continue.";
  if (field.type === "radio") return "Choose a membership type.";
  if (field.tagName === "SELECT") return `Choose an option for ${label.toLowerCase()}.`;
  return `Enter ${label.toLowerCase()}.`;
}

function setupGallerySelection() {
  const cards = document.querySelectorAll(".flip-card");
  const status = document.querySelector("#gallery-status");

  if (!cards.length || !status) return;

  cards.forEach((card) => {
    card.addEventListener("click", () => selectGalleryCard(card, cards, status));
    card.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        selectGalleryCard(card, cards, status);
      }
    });
  });
}

function selectGalleryCard(selectedCard, cards, status) {
  const willOpen = !selectedCard.classList.contains("is-flipped");
  cards.forEach((card) => {
    card.classList.remove("selected-card", "is-flipped");
    card.setAttribute("aria-expanded", "false");
  });
  selectedCard.classList.toggle("is-flipped", willOpen);
  selectedCard.classList.toggle("selected-card", willOpen);
  selectedCard.setAttribute("aria-expanded", String(willOpen));
  const caption = selectedCard.querySelector("figcaption");
  status.textContent = willOpen
    ? `${caption ? caption.textContent : "This space"} is now your favourite library space!`
    : "Select a card to mark your favourite library space.";
  status.classList.toggle("success-message", willOpen);
}
