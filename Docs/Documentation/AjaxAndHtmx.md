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
the plugin emits HTML attributes and response headers and ships no HTMX runtime of its own (the
only script it ships is the optional reCaptcha glue, see [reCaptcha](#recaptcha)):

```html
<script src="https://unpkg.com/htmx.org"></script>
```

reCaptcha
---------
**HTMX channel:** reCaptcha works with HTMX. reCaptcha assumes a full-page lifecycle
(load → render → submit → reload) that HTMX breaks in two places — a widget arriving via swap
is never auto-rendered, and the one-time token is spent when the form re-renders without a
reload — so, when the feature is enabled, `addReCaptcha()` loads a small glue script
(`reCaptchaHtmx.js`) that bridges both, for the two versions the plugin supports:

* **v2 (checkbox):** the glue renders every widget on load and after each swap
  (`grecaptcha.render`); its token rides along in the serialized form, and a failed submit swaps
  in a fresh widget, so no manual reset is needed.
* **v3 (invisible):** the plugin drops its default button-bound flow (which does a native
  `form.submit()` that bypasses HTMX) for a plain submit button plus a hidden
  `g-recaptcha-response` field; on `htmx:confirm` the glue runs `grecaptcha.execute()` and
  injects a fresh token into the request via `htmx:configRequest` before it is issued.

Nothing changes server-side — the token is validated against `siteverify` exactly as for a
normal form. This is all automatic once `Users.Ajax.enabled` is on; you only load HTMX itself.

The **v3** HTMX rendering follows the plugin's ajax context: `addReCaptcha()`/`button()` switch to
the HTMX flow only where `AjaxResponseComponent` is active (the plugin's ajax controllers set the
`ajaxEnabled` view var), so a v3 reCaptcha on a form rendered elsewhere — a custom app form, or a
non-plugin page — keeps the normal button-bound flow and works unchanged. The one case to avoid is
stripping the `hx-*` attributes from an *overridden plugin template* while the feature is on: the
reCaptcha would then render in HTMX mode but the form would submit natively with an empty token.
(v2 is unaffected — its token comes from the checkbox regardless of how the form submits.)

**JSON channel:** a programmatic JSON client runs no browser JavaScript and so can never
produce a token — such a request always fails the reCaptcha check (a tokenless request is
reported as a failed reCaptcha; note that `cakedc/auth` before the empty-token guard raised a
`500` TypeError here instead). Disable reCaptcha on any form you expose over JSON — login
(`Users.reCaptcha.login`) and registration (`Users.reCaptcha.registration`):

```php
'Users.reCaptcha.login' => false,
'Users.reCaptcha.registration' => false,
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

A failed JSON **login** returns `401` (a two-factor `verify` failure is different — it
redirects and is reported as a `200` with `success: false`; see [Notes & limitations](#notes--limitations)):

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

### Full-page progressive enhancement
When the feature is enabled, the plugin's own full-page views (`/login`, `/register`, the
password flows, two-factor `verify`) render the form inside the `#ajax-login-container` swap
target and carry the `hx-*` attributes. So — once the host app has loaded HTMX — the full page
submits via HTMX and swaps in place instead of reloading: validation/auth failures swap the
re-rendered fragment back into the container, and successes (as well as the two-factor
challenge) navigate via `HX-Redirect`. If HTMX is not loaded the same form still carries a
normal `action`/`method`, so it degrades gracefully to a standard full-page POST.

The `#ajax-login-container` wrapper is emitted on the full page only; the HTMX fragment
re-render (which is swapped *into* that container) omits it, so nothing nests or duplicates.

Load HTMX from the host app — the plugin ships no HTMX runtime. A common pattern is to load it
only while the feature is on, e.g. in your layout `<head>`:

```php
<?php if (\Cake\Core\Configure::read('Users.Ajax.enabled')) : ?>
    <script src="https://unpkg.com/htmx.org" crossorigin="anonymous"></script>
<?php endif; ?>
```

### Using it in a modal
1. Enable the feature and include HTMX (see [Enabling](#enabling)).
2. Add a trigger that loads the login form fragment into your modal body:

   ```html
   <button hx-get="/users/login" hx-target="#modal-body">Login</button>
   <div id="modal-body"></div>
   ```
3. The plugin returns the login form rendered with the `ajax` layout (fragment only).
4. The form submits via HTMX; on success the `HX-Redirect` header redirects the page.

> **reCaptcha in the modal flow:** the `ajax` layout renders only the content fragment, so the
> reCaptcha `api.js` and glue that `addReCaptcha()` registers into the `script` block are dropped
> from the fragment. When the login form is loaded into a modal on a page that is not itself one
> of the plugin's reCaptcha pages, the host page must load `https://www.google.com/recaptcha/api.js`
> (v3: `?render=<key>`, v2: `?render=explicit`) and `CakeDC/Users.reCaptchaHtmx` itself, **and**
> set the config the glue reads — `window.CakeDCUsersReCaptcha = {version: 2|3, siteKey: '<key>'}`
> (the glue gates every path on it) — or disable reCaptcha for that flow.

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

> ⚠️ **Mass-assignment on the JSON register.** On the HTML channel `FormProtection`'s field
> locking is also what stops a client from POSTing columns the form never rendered. With
> `skipFormProtectionForJson => true` that guard is gone for JSON, so a JSON `register` is bounded
> only by the User entity's `$_accessible`. The plugin's default entity is `'*' => true`, which
> lets a JSON registrant set `secret`/`secret_verified` (pre-seeding a known TOTP secret),
> `api_token`, `additional_data`, and any custom column your app added to `users`. Before exposing
> `register` over JSON, tighten `$_accessible` on your (extended) User entity — allow only the
> real registration fields — or set `skipFormProtectionForJson => false` and round-trip the form.

Events
------
This feature changes only response formatting. All plugin events
(`Users.Authentication.afterLogin`, `Users.Global.afterRegister`, etc.) still fire exactly as
before, so your existing listeners keep working. See [Events](Events.md).

Notes & limitations
-------------------
* Session-based authentication only (no JWT / token issuance).
* With two-factor login enabled (e.g. `OneTimePasswordAuthenticator.login`), a successful
  password step redirects to the `verify` action for the second factor. The middleware wraps the
  authentication and two-factor middleware, so that redirect is converted like any other: JSON
  receives `{ "success": true, "redirect": "<verify>" }` and HTMX an `HX-Redirect` to the verify
  page. A `success: true` here means "password accepted, now complete 2FA", not a finished login.
* Two-factor `verify` failures redirect to the login action rather than returning a `401` body.
  They are reported as `{ "success": false, "redirect": "<login>", "error": "..." }` (HTTP `200`),
  so a JSON client must check `success` rather than assume a redirect means the step passed.
* The feature acts only on the configured Users controller's routes — it honors a custom
  `Users.controller`, and your application's other routes are untouched.
* `socialLogin` is an OAuth browser callback (full-page navigation), not a JSON/HTMX channel,
  and is unaffected by this feature.
