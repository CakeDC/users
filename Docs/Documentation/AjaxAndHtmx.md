Ajax, JSON & HTMX Responses
===========================
The plugin can return its user-facing actions (login, register, password flows, profile) as
JSON — for SPA / programmatic consumers — or as HTML fragments driven by
[HTMX](https://htmx.org/) — for modal / progressive-enhancement logins — in addition to the
normal full-page HTML responses.

The feature is **opt-in and disabled by default**. When disabled, the plugin behaves exactly
as before and no extra middleware or component is loaded.

Enabling
--------
Add this to your `config/users.php`:

```php
'Users.Ajax.enabled' => true,
'Users.Ajax.skipFormProtectionForJson' => true, // default, see "FormProtection & CSRF"
```

The host application is responsible for loading the HTMX library if you use the HTMX channel —
the plugin only emits HTML attributes and response headers, it ships no JavaScript:

```html
<script src="https://unpkg.com/htmx.org"></script>
```

How does it work
----------------
When enabled, a lightweight component and a thin middleware are loaded for the plugin's
controllers. They inspect each request and adapt the response:

* **JSON** — when the request sends `Accept: application/json`, the response body is JSON.
* **HTMX** — when the request sends the `HX-Request` header (HTMX adds it automatically), the
  response is an HTML fragment (no page skeleton), suitable for swapping into a modal or
  container.
* **Normal** — neither header present: behaves exactly as today (full-page HTML).

JSON responses
--------------
CakePHP's `JsonView` serializes each key that is listed in `_serialize` at the **top level** —
there is no `data` envelope.

`GET /users/profile` (the only action that returns a populated user on success):

```json
{ "user": { "id": "...", "username": "...", "email": "..." }, "isCurrentUser": true, "success": true, "flash": null }
```

Actions that finish with a redirect — `login`, `register`, `changePassword`, `resetPassword`,
two-factor `verify` — return the redirect target rather than a user object (the middleware
intercepts the server-side redirect and rewrites it). The HTTP status is always `200`; read the
`success` flag for the outcome:

```json
{ "success": true, "redirect": "http://example.com/dashboard", "flash": { "type": "success", "message": "..." } }
```

Not every plugin redirect means success — a failed two-factor `verify`, for instance, redirects
back to the login action. When the action queued an error flash before redirecting, the middleware
reports the redirect as a failure and echoes the message, so a client can tell the two apart:

```json
{ "success": false, "redirect": "http://example.com/login", "error": "Verification code is invalid. Try again", "flash": { "type": "error", "message": "..." } }
```

A SPA that needs the full user object after login can request `GET /users/profile` with
`Accept: application/json`.

Authentication failure (login / 2FA) returns `401`:

```json
{ "success": false, "error": "Username or password is incorrect", "flash": { "type": "error", "message": "..." } }
```

Validation failure (register, password, profile) returns `422`:

```json
{ "success": false, "errors": { "username": { "_required": "This field is required" } }, "flash": { "type": "error", "message": "..." } }
```

### Sensitive fields
The `user` object never includes `password`, `token`, `secret` or `api_token` — these are
hidden by the User entity's `$_hidden` list. To expose or hide more fields, edit `$_hidden` on
your (extended) User entity.

HTMX responses
--------------
With the feature enabled, the plugin's forms (`login`, `register`, `change_password`,
`request_reset_password`, `verify`) emit HTMX attributes so they submit via HTMX and swap the
response into a target container (default target `#ajax-login-container`).

* On a successful login the response carries an `HX-Redirect` header, so HTMX performs a
  full-page redirect.
* On a validation/authentication failure the re-rendered form fragment is returned and swapped
  back in, showing the flash error.

### Using it in a modal
1. Enable the feature and include HTMX (see [Enabling](#enabling)).
2. Add a trigger that loads the login form fragment into your modal body:

   ```html
   <button hx-get="/users/login" hx-target="#modal-body">Login</button>
   <div id="modal-body"></div>
   ```
3. The plugin returns the login form rendered with the `ajax` layout (fragment only).
4. The form submits via HTMX; on success the `HX-Redirect` header redirects the page.

FormProtection & CSRF
---------------------
The plugin protects its forms with CakePHP's `FormProtection` (field tampering) and CSRF
components. The HTMX channel posts the rendered form, so both checks pass with no extra work.

Programmatic JSON clients build the request body themselves and cannot satisfy `FormProtection`
(it locks the exact rendered fields). When `Users.Ajax.skipFormProtectionForJson` is `true`
(default), `FormProtection` is skipped for JSON-negotiated requests only; CSRF protection still
applies — send the token in the `X-CSRF-Token` header (read it from the `csrfToken` cookie). Set
the option to `false` to instead require JSON clients to round-trip the rendered form:

```php
'Users.Ajax.skipFormProtectionForJson' => false,
```

Events
------
This feature changes only response formatting. All plugin events
(`Users.Authentication.afterLogin`, `Users.Global.afterRegister`, etc.) still fire exactly as
before, so your existing listeners keep working. See [Events](Events.md).

Notes & limitations
-------------------
* Session-based authentication only (no JWT / token issuance).
* Two-factor `verify` failures redirect to the login action rather than returning a `401` body.
  They are reported as `{ "success": false, "redirect": "<login>", "error": "..." }` (HTTP `200`),
  so a JSON client must check `success` rather than assume a redirect means the step passed.
* The feature acts only on the Users plugin's own routes; your application's other routes are
  untouched.
* `socialLogin` is an OAuth browser callback (full-page navigation), not a JSON/HTMX channel,
  and is unaffected by this feature.
