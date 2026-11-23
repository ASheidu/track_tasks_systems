# Local integration test results

Tester: Rohit Kumar Yadav
Environment: Windows, Wampserver64 MySQL, PHP local server at localhost:8000.

Rohit reported that all checks in the combined local testing guide passed.
These results describe his local copy containing the account pages,
Dashboard, and updated My Tasks page. They do not certify the merged
GitHub download or the deployed website.

| Area | Checks | Reported result |
|---|---|---|
| Accounts | Registration, incorrect password rejection, valid login, displayed name | Passed |
| Access | Logout, signed-out protected routes, inactivity expiration | Passed |
| Tasks | Create, edit, complete, reopen, delete, refresh persistence | Passed |
| Search | Keyword, tags, category, priority, status, due date, clear filters | Passed |
| Dashboard | Empty account, mixed task counts, completion percentage, updates after completion | Passed |
| Ownership | Second account does not see the first account's tasks or totals | Passed |
| Validation | Duplicate registration, invalid passwords with JavaScript disabled | Passed |
| CSRF | Modified token rejected | Passed |
| Interface | Narrow layout, keyboard navigation, labels, password visibility | Passed |

## Checks still required

- Download and test the actual combined milestone3 branch after merging.
- Repeat the main account and task flow on the deployed PHP/MySQL app.
- Capture submission screenshots without credentials or session tokens.
- Record the browser and screen reader used before claiming formal
  screen-reader compatibility. Keyboard and label checks alone do not
  establish full accessibility conformance.

The uploaded Rohit ZIP alone does not contain the teammates' account
and Dashboard pages; those are supplied through their separate branches.
