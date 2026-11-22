
# Track Task — Authentication Test Report

## Team CTRL — Milestone 3

### My Contribution
Login, Registration, and Logout using PHP and MySQL.

## Tests Completed

### 1. User Registration — PASS
- Created a test account successfully.
- User information was saved in MySQL.
- Redirected to Login after registration.

### 2. Login Authentication — PASS
- Logged in using registered credentials.
- Authentication redirected toward dashboard.php.
- Dashboard integration is still pending.

### 3. Protected Page Access — PASS
- Accessed tasks.php after successful login.
- Unauthenticated access was redirected to Login.

### 4. Logout — PASS
- Logged out successfully.
- Returned to the Login page.
- Could not access tasks.php without signing in again.

### 5. Session Expiration — PASS
- Tested the 30-minute inactivity timeout.
- Session expired correctly.
- The Login page displayed the expiration message.

## Pending Integration Tests
- Complete Login to Dashboard flow.
- Verify Dashboard functionality after team integration.
- Final end-to-end testing.

## Conclusion
The individual authentication features have passed
their initial functional tests. Team integration
testing is still required.
