
"use strict";

// Track Task - Milestone 2
// Frontend-only form validation

// Show error under an input field
function showError(inputId, errorId, message) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);

    error.textContent = message;
    input.setAttribute("aria-invalid", message ? "true" : "false");
}

// Show form message
function showMessage(id, message, type) {
    const element = document.getElementById(id);

    element.textContent = message;
    element.className = "message " + type;
}

// Validate email
function validEmail(email) {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(email) && email.length <= 254;
}

// Validate password strength
function validPassword(password) {
    return password.length >= 8 &&
           /[A-Z]/.test(password) &&
           /[a-z]/.test(password) &&
           /[0-9]/.test(password);
}

// LOGIN FORM

const loginForm = document.getElementById("loginForm");

if (loginForm) {

    loginForm.addEventListener("submit", function(event) {
        event.preventDefault();

        const email = document.getElementById("loginEmail").value.trim();
        const password = document.getElementById("loginPassword").value;

        let valid = true;
        let firstError = null;

        // Check email
        if (email === "") {
            showError("loginEmail", "loginEmailError",
                      "Email is required.");
            valid = false;
            firstError = "loginEmail";
        } else if (!validEmail(email)) {
            showError("loginEmail", "loginEmailError",
                      "Enter a valid email address.");
            valid = false;
            firstError = "loginEmail";
        } else {
            showError("loginEmail", "loginEmailError", "");
        }

        // Check password
        if (password === "") {
            showError("loginPassword", "loginPasswordError",
                      "Password is required.");
            valid = false;
            if (!firstError) firstError = "loginPassword";
        } else {
            showError("loginPassword", "loginPasswordError", "");
        }

        if (valid) {
            showMessage(
                "loginMessage",
                "Validation successful! This is a frontend demo. No login was performed.",
                "success"
            );
        } else {
            showMessage(
                "loginMessage",
                "Please correct the errors above.",
                "error-message"
            );

            document.getElementById(firstError).focus();
        }
    });

    // Forgot password button
    document.getElementById("forgotPassword")
        .addEventListener("click", function() {

            showMessage(
                "loginMessage",
                "Password recovery is not available in this frontend prototype.",
                "info"
            );
        });
}

// REGISTRATION FORM

const registerForm = document.getElementById("registerForm");

if (registerForm) {

    registerForm.addEventListener("submit", function(event) {
        event.preventDefault();

        const name = document.getElementById("fullName").value.trim();
        const email = document.getElementById("registerEmail").value.trim();
        const password = document.getElementById("registerPassword").value;
        const confirm = document.getElementById("confirmPassword").value;

        let valid = true;
        let firstError = null;

        // Validate full name
        if (name.length < 2 || name.length > 60) {
            showError("fullName", "fullNameError",
                      "Name must contain 2 to 60 characters.");
            valid = false;
            firstError = "fullName";
        } else if (!/^[\p{L}\p{M} .'-]+$/u.test(name)) {
            showError("fullName", "fullNameError",
                      "Name contains invalid characters.");
            valid = false;
            firstError = "fullName";
        } else {
            showError("fullName", "fullNameError", "");
        }

        // Validate email
        if (!validEmail(email)) {
            showError("registerEmail", "registerEmailError",
                      "Enter a valid email address.");
            valid = false;
            if (!firstError) firstError = "registerEmail";
        } else {
            showError("registerEmail", "registerEmailError", "");
        }

        // Validate password
        if (!validPassword(password)) {
            showError(
                "registerPassword",
                "registerPasswordError",
                "Use 8+ characters with uppercase, lowercase, and a number."
            );
            valid = false;
            if (!firstError) firstError = "registerPassword";
        } else {
            showError("registerPassword", "registerPasswordError", "");
        }

        // Confirm passwords match
        if (confirm === "") {
            showError("confirmPassword", "confirmPasswordError",
                      "Please confirm your password.");
            valid = false;
            if (!firstError) firstError = "confirmPassword";
        } else if (password !== confirm) {
            showError("confirmPassword", "confirmPasswordError",
                      "Passwords do not match.");
            valid = false;
            if (!firstError) firstError = "confirmPassword";
        } else {
            showError("confirmPassword", "confirmPasswordError", "");
        }

        // Final result
        if (valid) {
            showMessage(
                "registerMessage",
                "Validation successful! This is a frontend demo. No account was created.",
                "success"
            );

            registerForm.reset();

            ["fullName", "registerEmail",
             "registerPassword", "confirmPassword"].forEach(function(id) {
                document.getElementById(id).removeAttribute("aria-invalid");
            });

        } else {
            showMessage(
                "registerMessage",
                "Please correct the errors above.",
                "error-message"
            );

            document.getElementById(firstError).focus();
        }
    });
}
