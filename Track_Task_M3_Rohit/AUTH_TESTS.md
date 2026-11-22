
# Track Task — Authentication Test Report

## Team CTRL — Milestone 3

### My Contribution

Implemented Login, Registration, and Logout functionality
using PHP and MySQL for the Track Task website.

## Tests Completed

### 1. User Registration — PASS

- Created a test account successfully.
- User information was saved in MySQL.
- Registration redirected to the Login page.
- The success message was displayed correctly.

### 2. Login Authentication — PASS

- Logged in using registered credentials.
- The credentials were verified successfully.
- Login redirected toward dashboard.php.
- Dashboard integration is still pending.

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

- Submitted the registration form using an existing email.
- The application displayed "This email is already registered."
- Submitted an existing username.
- The application displayed "This username is already taken."
- Duplicate registration was prevented.
- The validation was performed using the MySQL database.

## Security Features Implemented

### Password Security

- Passwords are hashed using password_hash().
- Login verifies passwords using password_verify().
- Plaintext passwords are not stored in the users table.

### Database Security

- Database access uses prepared SQL statements.
- SQL queries use parameterized placeholders.
- The application uses a dedicated MySQL user.
- Database permissions are limited to SELECT,
  INSERT, UPDATE, and DELETE.

### Session Security

- PHP sessions maintain authenticated user information.
- Session IDs are regenerated after successful login.
- Protected pages require authentication.
- Sessions expire after 30 minutes of inactivity.
- Logout destroys the authenticated session.

### Form Security

- Registration uses server-side validation.
- Duplicate email and username checks are implemented.
- CSRF tokens protect POST requests.
- Authentication errors use generic messages.

## Pending Integration Tests

- Complete Login-to-Dashboard navigation.
- Verify the Dashboard displays the correct signed-in user.
- Confirm Dashboard and My Tasks integration.
- Final end-to-end testing after team integration.
- Final responsive design and accessibility checks.

## Known Limitations

### Dashboard Integration

The Login page redirects to dashboard.php after
successful authentication.

The Dashboard is maintained by another team member
and has not yet been integrated into the local branch.

Until integration is complete, the Dashboard URL
may return a 404 Not Found response.

### Duplicate Registration Testing

The duplicate email and username checks both passed.

Both fields contained existing values during the test.
Additional independent tests can verify each duplicate
condition separately.

## Conclusion

The individual authentication functionality has
passed its initial registration, login, logout,
access-control, session-expiration, invalid-password,
and duplicate-registration tests.

Final Dashboard integration and full application
testing are still required before Milestone 3
submission.
