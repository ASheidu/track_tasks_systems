# Dashboard test results

Tested locally with PHP 8.6.0.

## Tests run

| Test | Observed result | Outcome |
|---|---|---|
| Run `php -l public/dashboard.php` | No syntax errors detected | Passed |
| Request `/dashboard.php` while signed out | HTTP 303 See Other with `Location: login.php` | Passed |

## Tests still to run

- Sign in with an empty account and verify “No tasks yet,” zero counts, and 0% completion.
- Add a mix of To-Do, In Progress, and Completed tasks. Check overdue dates and the completion percentage.
- Complete an overdue task on My Tasks, return to Dashboard, and verify the updated totals.
- Refresh, sign out, and sign back in to confirm the totals persist.
- Sign in with a second account and verify that each account has its own totals.
- Check keyboard navigation, the progress bar’s screen-reader label, and the mobile layout. Capture screenshots of the running Dashboard.

The signed-in tests are pending integration of the account pages and development database configuration.