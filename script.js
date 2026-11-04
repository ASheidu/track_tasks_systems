
"use strict";

// Track Task - Milestone 2
// Frontend-only prototype
// No real accounts or passwords are stored.

// Show / Hide Password
document.querySelectorAll(".toggle-password").forEach(function (toggle) {
  toggle.addEventListener("change", function () {
    const input = document.getElementById(toggle.dataset.target);

    if (input) {
      input.type = toggle.checked ? "text" : "password";
    }
  });
});

// Registration Page
const regForm = document.getElementById("regForm");

if (regForm) {
  const fullname = document.getElementById("fullname");
  const email = document.getElementById("email");
  const username = document.getElementById("username");
  const pass = document.getElementById("pass");
  const pass2 = document.getElementById("pass2");
  const agree = document.getElementById("agree");
  const notes = document.getElementById("notes");

  const passErr = document.getElementById("passErr");
  const pass2Err = document.getElementById("pass2Err");
  const agreeErr = document.getElementById("agreeErr");
  const notesCount = document.getElementById("notesCount");
  const successMsg = document.getElementById("successMsg");
  const passwordStrength = document.getElementById("passwordStrength");

  // Validate Full Name
  function validateName() {
    if (fullname.value.trim().length < 2) {
      fullname.setCustomValidity(
        "Please enter at least 2 characters for your name."
      );
    } else {
      fullname.setCustomValidity("");
    }
  }

  // Validate Email
  function validateEmail() {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email.value.trim())) {
      email.setCustomValidity(
        "Please enter a valid email such as name@example.com."
      );
    } else {
      email.setCustomValidity("");
    }
  }

  // Validate Username
  function validateUsername() {
    const usernamePattern = /^[A-Za-z0-9_]{4,20}$/;

    if (!usernamePattern.test(username.value)) {
      username.setCustomValidity(
        "Use 4-20 letters, numbers, or underscores."
      );
    } else {
      username.setCustomValidity("");
    }
  }

  // Live Password Strength Indicator
  function updatePasswordStrength() {
    if (!passwordStrength) return;

    const password = pass.value;

    if (password.length === 0) {
      passwordStrength.textContent = "";
      passwordStrength.style.color = "";
      return;
    }

    let score = 0;

    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    if (score <= 2) {
      passwordStrength.textContent = "Password strength: Weak";
      passwordStrength.style.color = "#EF4444";
    } else if (score <= 4) {
      passwordStrength.textContent = "Password strength: Medium";
      passwordStrength.style.color = "#F59E0B";
    } else {
      passwordStrength.textContent = "Password strength: Strong";
      passwordStrength.style.color = "#15803D";
    }
  }

  // Validate Password
  function validatePassword() {
    const passwordPattern = /^(?=.*[A-Z])(?=.*\d).{8,64}$/;
    const valid = passwordPattern.test(pass.value);

    if (!valid) {
      pass.setCustomValidity(
        "Use 8-64 characters with a capital letter and a number."
      );

      passErr.textContent =
        "Password must include a capital letter and a number.";
    } else {
      pass.setCustomValidity("");
      passErr.textContent = "";
    }

    validatePasswordMatch();
  }

  // Confirm Password
  function validatePasswordMatch() {
    if (pass2.value === "") {
      pass2.setCustomValidity("Please confirm your password.");
      pass2Err.textContent = "";
    } else if (pass.value !== pass2.value) {
      pass2.setCustomValidity("Passwords do not match.");
      pass2Err.textContent = "Passwords do not match.";
    } else {
      pass2.setCustomValidity("");
      pass2Err.textContent = "";
    }
  }

  // Live Validation
  fullname.addEventListener("input", validateName);
  email.addEventListener("input", validateEmail);
  username.addEventListener("input", validateUsername);
  pass.addEventListener("input", validatePassword);
  pass.addEventListener("input", updatePasswordStrength);
  pass2.addEventListener("input", validatePasswordMatch);

  // Character Counter
  notes.addEventListener("input", function () {
    notesCount.textContent =
      notes.value.length + " / 150 characters";
  });

  // Agreement Validation
  agree.addEventListener("change", function () {
    agreeErr.textContent = agree.checked
      ? ""
      : "Please confirm this is a demo.";
  });

  // Registration Submission
  regForm.addEventListener("submit", function (event) {
    event.preventDefault();

    successMsg.hidden = true;

    validateName();
    validateEmail();
    validateUsername();
    validatePassword();
    validatePasswordMatch();

    agreeErr.textContent = agree.checked
      ? ""
      : "Please confirm this is a demo.";

    if (!regForm.checkValidity()) {
      regForm.reportValidity();

      const firstInvalid = regForm.querySelector(":invalid");

      if (firstInvalid) {
        firstInvalid.focus();
      }

      return;
    }

    // No information is saved or sent to a server.
    regForm.reset();

    successMsg.hidden = false;
    successMsg.focus();
  });

  // Reset Form
  regForm.addEventListener("reset", function () {
    [fullname, email, username, pass, pass2].forEach(function (field) {
      field.setCustomValidity("");
    });

    passErr.textContent = "";
    pass2Err.textContent = "";
    agreeErr.textContent = "";
    notesCount.textContent = "0 / 150 characters";

    if (passwordStrength) {
      passwordStrength.textContent = "";
      passwordStrength.style.color = "";
    }

    document.querySelectorAll(".toggle-password").forEach(function (toggle) {
      toggle.checked = false;

      const input = document.getElementById(toggle.dataset.target);

      if (input) {
        input.type = "password";
      }
    });
  });
}

// Login Page
const loginForm = document.getElementById("loginForm");

if (loginForm) {
  loginForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const loginEmail = document.getElementById("loginEmail");
    const loginPassword = document.getElementById("loginPassword");
    const loginError = document.getElementById("loginError");

    loginError.textContent = "";

    if (
      !loginEmail.checkValidity() ||
      !loginPassword.checkValidity()
    ) {
      loginError.textContent =
        "Enter a valid email and a demo password with at least 8 characters.";

      loginForm.reportValidity();
      return;
    }

    // Demo navigation only.
    // No authentication or credential storage.
    window.location.href = "dashboard.html";
  });
}
