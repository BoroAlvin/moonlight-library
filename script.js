"use strict";

document.addEventListener("DOMContentLoaded", () => {
  setupThemeButton();
  setupWelcomeMessage();
  setupHomeInteractions();
  setupFormValidation();
  setupGallerySelection();
});

function setupThemeButton() {
  const navigation = document.querySelector("nav");

  if (!navigation) return;

  const button = document.createElement("button");
  button.type = "button";
  button.className = "theme-button";
  button.setAttribute("aria-pressed", "false");
  button.textContent = "Warm theme";
  navigation.appendChild(button);

  button.addEventListener("click", () => {
    const warmThemeIsOn = document.body.classList.toggle("warm-theme");
    button.setAttribute("aria-pressed", String(warmThemeIsOn));
    button.textContent = warmThemeIsOn ? "Navy theme" : "Warm theme";
  });
}

function setupWelcomeMessage() {
  const welcomeMessage = document.querySelector("#welcome-message");
  const welcomeNote = document.querySelector("#welcome-note");
  const changeNameButton = document.querySelector("#change-name");

  if (!welcomeMessage || !welcomeNote || !changeNameButton) return;

  let savedName = "";

  try {
    savedName = localStorage.getItem("moonlightVisitorName") || "";
  } catch (error) {
    savedName = "";
  }

  const askForName = () => {
    const answer = window.prompt("Welcome to Moonlight Library! What is your name?", savedName);

    if (answer === null) {
      welcomeMessage.textContent = savedName
        ? `Welcome back, ${savedName}!`
        : "Welcome to Moonlight Library!";
      welcomeNote.textContent = "Explore books, events, and welcoming spaces made for readers.";
      return;
    }

    const cleanName = answer.trim().replace(/\s+/g, " ").slice(0, 40);

    if (!cleanName) {
      welcomeMessage.textContent = "Welcome, reader!";
      welcomeNote.textContent = "Your next great read may be only one click away.";
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
  } else {
    askForName();
  }

  changeNameButton.addEventListener("click", askForName);
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
  document.querySelectorAll("form").forEach((form) => {
    let message = form.querySelector(".form-message");

    if (!message) {
      message = document.createElement("div");
      message.className = "form-message";
      message.setAttribute("aria-live", "polite");
      form.prepend(message);
    }

    form.addEventListener("submit", (event) => {
      event.preventDefault();
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
        uniqueInvalidFields.forEach((field) => {
          field.classList.add("input-error");
          field.setAttribute("aria-invalid", "true");
        });
        message.className = "form-message error-message";
        message.textContent = `Please complete ${uniqueInvalidFields.length} required field${uniqueInvalidFields.length === 1 ? "" : "s"} before submitting.`;
        uniqueInvalidFields[0].focus();
        return;
      }

      message.className = "form-message success-message";
      message.textContent = form.id === "membership-form"
        ? "Application checked successfully! Thank you for joining Moonlight Library."
        : "Thank you! Your information has been received successfully.";
      form.reset();
      message.scrollIntoView({ behavior: "smooth", block: "center" });
    });

    form.addEventListener("input", (event) => {
      const field = event.target;
      if (field.matches("input, select, textarea")) {
        field.classList.remove("input-error");
        field.removeAttribute("aria-invalid");
      }
    });
  });
}

function clearFieldErrors(form) {
  form.querySelectorAll(".input-error").forEach((field) => {
    field.classList.remove("input-error");
    field.removeAttribute("aria-invalid");
  });
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
  cards.forEach((card) => card.classList.remove("selected-card"));
  selectedCard.classList.add("selected-card");
  const caption = selectedCard.querySelector("figcaption");
  status.textContent = `${caption ? caption.textContent : "This space"} is now your favourite library space!`;
  status.classList.add("success-message");
}
