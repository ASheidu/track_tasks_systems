
/* Track Task - Team CTRL - Milestone 3 */
/* Login and Registration JavaScript */

document.addEventListener("DOMContentLoaded", function () {
    // Focus the first field rejected by server-side validation.
    const firstInvalidField =
        document.querySelector('.account-field [aria-invalid="true"]');
    const loginError = document.getElementById("login-error");

    if (firstInvalidField) {
        firstInvalidField.focus();
    } else if (loginError) {
        document.getElementById("loginEmail").focus();
    }


    // Show and Hide Password
    document.querySelectorAll("[data-show-password]").forEach(function (toggle) {
        toggle.addEventListener("change", function () {
            const passwordInput = document.getElementById(
                toggle.dataset.showPassword
            );

            if (passwordInput) {
                passwordInput.type = toggle.checked ? "text" : "password";
            }
        });
    });

    // Password Strength Indicator
    const password = document.getElementById("pass");
    const strength = document.getElementById("password-strength");

    if (password && strength) {
        password.addEventListener("input", function () {
            const value = password.value;
            let score = 0;

            if (value.length >= 8) score++;
            if (/[A-Z]/.test(value)) score++;
            if (/[a-z]/.test(value)) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;

            if (value.length === 0) {
                strength.textContent = "";
            } else if (score <= 2) {
                strength.textContent = "Password strength: Weak";
            } else if (score <= 4) {
                strength.textContent = "Password strength: Medium";
            } else {
                strength.textContent = "Password strength: Strong";
            }
        });
    }

    // Login Form Validation
    const loginForm = document.getElementById("loginForm");

    if (loginForm) {
        loginForm.addEventListener("submit", function (event) {
            const email = document.getElementById("loginEmail");
            const password = document.getElementById("loginPassword");

            if (!email.value.trim() || !email.checkValidity()) {
                event.preventDefault();
                alert("Please enter a valid email address.");
                email.focus();
                return;
            }

            if (!password.value) {
                event.preventDefault();
                alert("Please enter your password.");
                password.focus();
            }
        });
    }

    // Registration Form Validation
    const registrationForm = document.getElementById("regForm");

    if (registrationForm) {
        registrationForm.addEventListener("submit", function (event) {
            const fullname = document.getElementById("fullname");
            const email = document.getElementById("email");
            const username = document.getElementById("username");
            const password = document.getElementById("pass");
            const confirmation = document.getElementById("pass2");

            if (
                fullname.value.trim().length < 2 ||
                fullname.value.trim().length > 50
            ) {
                event.preventDefault();
                alert("Full Name must be between 2 and 50 characters.");
                fullname.focus();
                return;
            }

            if (!email.checkValidity() || !email.value.trim()) {
                event.preventDefault();
                alert("Please enter a valid email address.");
                email.focus();
                return;
            }

            if (!/^[A-Za-z0-9_]{4,20}$/.test(username.value.trim())) {
                event.preventDefault();
                alert("Username must be 4–20 letters, numbers, or underscores.");
                username.focus();
                return;
            }

            if (
                password.value.length < 8 ||
                password.value.length > 64 ||
                !/[A-Z]/.test(password.value) ||
                !/[0-9]/.test(password.value)
            ) {
                event.preventDefault();
                alert("Password must have 8–64 characters, a capital letter, and a number.");
                password.focus();
                return;
            }

            if (password.value !== confirmation.value) {
                event.preventDefault();
                alert("Passwords do not match.");
                confirmation.focus();
            }
        });

        // Reset password-strength text when resetting form
        registrationForm.addEventListener("reset", function () {
            if (strength) {
                strength.textContent = "";
            }
        });
    }
});
