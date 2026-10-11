# System notifications QA

This document covers the built-in CodeCart PRO 3.0.6.0 notification center.

The notification center displays 50 entries per page and includes the **All**, **New**, **Read** and **Resolved** filters. The header dropdown intentionally displays only the latest five active notifications; the center displays the full paginated history still present in the database.

Deleting a notification hides it without immediately re-creating an identical warning during the next cron or health scan. A changed condition becomes unread. If a previously hidden condition is fixed, its suppression marker is removed, so a later regression is reported. Clearing read/resolved notifications behaves similarly. No database schema update is required; `codecart_notification.status` already supports the `dismissed` value.

AJAX actions require a valid session user token and **Access** and **Modify** permissions for `tool/notification`. Insufficient permissions, bad IDs and server errors are displayed rather than being silently ignored.

## Verification

Run `php tools/notification-check.php` to verify SQL semantics without connecting to a store database. Then test these actions in the administrator UI against a backed-up staging database:

- List more than 200 notices, navigate pages and filter by status.
- Dismiss a recurring active health notification, refresh the page and run health cron; it should remain hidden while unchanged.
- Fix the underlying issue, run health cron, then reproduce it; the warning should return as new.
- Test Read, Read all and Clear read.
- Test as an account without Modify permission and verify that the UI displays an explanatory warning.
- Verify the header unread counter and notification menu.

Notices physically removed by older releases or automatically purged after the retention period cannot be restored by this update without a database backup.
