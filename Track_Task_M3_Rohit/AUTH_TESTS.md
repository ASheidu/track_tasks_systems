
# Track Task — Authentication Test Report

## Team CTRL — Milestone 3

### My Contribution

Implemented Login, Registration, and Logout functionality
using PHP and MySQL for the Track Task website.

## Tests Completed

### 1. User Registration — PASS

- Created a test account successfully.
- Registration saved user information in MySQL.
- Registration redirected to the Login page.
- The success message was displayed correctly.

### 2. Login Authentication — PASS

- Logged in using registered credentials.
- The credentials were verified successfully.
- Login redirected toward dashboard.php.
- Combined Dashboard navigation was subsequently tested by Rohit.

### 3. Protected Page Access — PASS

- Accessed tasks.php after successful login.
- Confirmed that the authenticated session was active.
- Attempted to access tasks.php after logout.
- Unauthenticated access redirected to Login.

### 4. Logout — PASS

- Logged out using the website's Logout control.
- Logout returned the user to the Login page.
- Attempted to reopen tasks.php after logout.
- Access was denied until the user signed in again.

### 5. Session Expiration — PASS

- Tested the 30-minute inactivity timeout.
- Left the authenticated session inactive.
- Refreshed tasks.php after the inactivity period.
- The application redirected to the Login page.
- The message "Your session expired. Please sign in again."
  was displayed correctly.

### 6. Invalid Password Authentication — PASS

- Entered a registered email with an incorrect password.
- The Login page displayed "Invalid email or password."
- Authentication was denied.
- The user remained on the Login page.
- The error message did not reveal whether the email
  or password was incorrect.

### 7. Duplicate Account Registration — PASS

- Submitted registration using an existing email
  and an existing username.
- The application displayed "This email is already registered."
- The application displayed "This username is already taken."
- Duplicate registration was rejected.

### 8. Independent Duplicate Validation — PASS

- Tested an existing email with a new username.
- Only the duplicate-email error appeared.
- Tested a new email with an existing username.
- Only the duplicate-username error appeared.
- Confirmed both validation checks work independently.
- Both duplicate registration attempts were rejected.

### 9. Server-Side Password Confirmation — PASS

- Opened the Registration page in Microsoft Edge.
- Disabled JavaScript using Developer Tools.
- Submitted two different passwords.
- PHP rejected the invalid registration request.
- The page displayed "Passwords must match."
- The error appeared below Confirm Password.
- Confirmed password validation works without JavaScript.

### 10. Server-Side Password Strength Validation — PASS

- Disabled JavaScript in Microsoft Edge Developer Tools.
- Submitted a password without an uppercase letter.
- PHP rejected the registration.
- The message "Use 8–64 characters with a capital letter
  and a number." appeared.
- Confirmed password requirements are enforced server-side.
- Re-enabled JavaScript and refreshed the page.
- Verified that the Show Password checkbox works.

### 11. Logout GET Request Security — PASS

- Signed in using a registered test account.
- Confirmed that the protected My Tasks page opened.
- Opened logout.php directly using a GET request.
- The server displayed "Logout requires a POST request."
- Returned to the My Tasks page without signing in again.
- Confirmed the authenticated session remained active.
- Verified that GET requests cannot log users out.

## Security Features Implemented

### Password Security

- Passwords are hashed using password_hash().
- Login verifies passwords using password_verify().
- Plaintext passwords are not stored in the users table.
- Password confirmation is validated on the server.
- Password complexity is validated on the server.
- JavaScript checks password strength and confirmation.

### Database Security

- Database access uses prepared SQL statements.
- SQL queries use parameterized placeholders.
- The application uses a dedicated MySQL user.
- Database permissions are limited to SELECT,
  INSERT, UPDATE, and DELETE on the track_task database.

### Session Security

- PHP sessions maintain authenticated user information.
- Session IDs are regenerated after successful login.
- Protected pages require authentication.
- Sessions expire after 30 minutes of inactivity.
- Logout destroys the authenticated session.
- A rejected GET logout request preserves the session.

### Form Security

- Registration uses server-side validation.
- Duplicate email and username checks are implemented.
- Duplicate checks work independently.
- CSRF tokens protect POST requests.
- Authentication errors use generic messages.
- Validation works even when JavaScript is disabled.
- Logout accepts POST requests only.

## Combined local integration testing

Rohit reported that the merged project passed local checks for
registration, login, Dashboard navigation, the displayed user name,
protected pages, logout, session expiration, and account isolation.

## Remaining checks

Repeat the account flow on the deployed website and capture final
screenshots. Check the latest skip-link, error-description, and focus
changes in the local browser after downloading the updated branch.

## Conclusion

The individual authentication functionality has passed
its initial registration, login, logout, access-control,
session-expiration, invalid-password, duplicate-registration,
independent duplicate-validation, server-side password
confirmation, server-side password strength, and
POST-only logout security tests.

Testing with JavaScript disabled confirmed that the
PHP backend independently enforces password validation.

Testing a direct GET request to logout.php confirmed
that the application rejects the request while
preserving the authenticated session.

Combined local integration testing has been reported as passed by Rohit.
Deployment testing and verification of the latest accessibility changes
remain required before submission.
