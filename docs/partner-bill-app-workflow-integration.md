# Partner Bill completion workflow integration

## Task for the app agent

Implement the Partner-side app behavior for overdue Partner Bills. Reuse the existing notification router, global API interceptor, order state management, and session termination flow.

## Completion reminder notification

The backend sends the first reminder two hours after the event ends and repeats it every two hours while the bill remains `confirmed` or `in_job`.

FCM data:

```json
{
  "code": "BILL_COMPLETED_REMINDER"
}
```

App requirements:

- Handle `BILL_COMPLETED_REMINDER` in foreground, background, and terminated states.
- Display the title and body supplied by FCM.
- When the notification is opened, navigate to the Partner `confirmed`/`in_job` bill list and refresh it.
- Do not create a local repeating notification. The backend controls the two-hour schedule.
- Do not automatically mark a bill as `completed`; check-in and check-out remain manual actions.
- The payload does not currently contain `bill_id`, so do not attempt to open a specific bill.
- Show a warning that the Partner must complete overdue bills and that the account may be suspended after three days.

## Locked check-in and check-out flow

Affected endpoints:

```text
POST /api/partner/bills/{bill}/mark-in-job
POST /api/partner/bills/{bill}/complete
```

When another overdue bill must be handled first, the backend returns HTTP `403`:

```json
{
  "message": "Complete overdue orders before checking in or out of other orders.",
  "code": "PARTNER_WORKFLOW_LOCKED"
}
```

App requirements:

- Map `PARTNER_WORKFLOW_LOCKED` to a dedicated domain error for both check-in and check-out.
- Display: `Bạn cần hoàn thành các đơn đang quá hạn trước khi check-in hoặc hoàn thành đơn khác.`
- Provide a `Xem đơn quá hạn` action that opens and refreshes the `confirmed`/`in_job` bill list.
- Do not show this response as a network or unknown error.
- Do not report success, persist a local status transition, or retain uploaded-media state when the request is rejected.
- Do not permanently cache the workflow lock. The backend is the source of truth and automatically unlocks the flow when all overdue bills are completed.
- Bills that are themselves overdue remain available for check-in and check-out so the Partner can resolve them.

## Suspended account contract

If an overdue bill is not completed within 72 hours of the first reminder, the backend soft-deletes the Partner account. Login attempts and API requests made with an existing token return HTTP `403`:

```json
{
  "code": "ACCOUNT_SUSPENDED",
  "message": "Account suspended.",
  "ban_reason": "tạm khóa tài khoản do nghi ngờ tài khoản không hoạt động đúng quy trình hoặc đối tác ảo",
  "suspended_at": "2026-09-01T01:30:00+07:00"
}
```

This contract applies to:

- Email/password login with valid credentials.
- Google login.
- Apple login.
- Any API request carrying a valid token belonging to a subsequently suspended account.

App requirements:

- Handle `ACCOUNT_SUSPENDED` separately from the generic `401` session-expired flow.
- Clear the access token and sensitive session state, disconnect account-specific sockets/subscriptions, and prevent automatic token refresh or request retries.
- Show a blocking suspended-account screen or dialog using `ban_reason` from the response.
- The screen should provide an action to return to login or contact support according to existing app conventions.
- Parse `suspended_at` as an optional ISO-8601 timestamp; do not calculate the suspension deadline locally.
- Do not treat every `401` or generic login failure as an account suspension.
- Accounts deleted by users and invalid/expired tokens retain the existing generic behavior.

## Acceptance criteria

- Opening `BILL_COMPLETED_REMINDER` routes to and refreshes the active Partner Bill list in every app lifecycle state.
- Neither notification handling nor local state automatically completes a bill.
- Both check-in and check-out present the dedicated locked-workflow UI for `PARTNER_WORKFLOW_LOCKED`.
- Completing all overdue bills permits subsequent operations after refreshing from the backend.
- `ACCOUNT_SUSPENDED` terminates the session without a retry loop and displays the backend-provided reason.
- Generic `401` responses continue to use the existing session-expired behavior.
- Formatter, static analysis, and relevant app tests pass according to the app repository conventions.

After implementation, report the changed files, state-management changes, navigation behavior, and verification results.
