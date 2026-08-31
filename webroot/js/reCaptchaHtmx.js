/**
 * CakeDC/Users - reCaptcha <-> HTMX glue.
 *
 * reCaptcha assumes a full-page lifecycle (load -> render -> submit -> reload).
 * HTMX breaks that in two places: a form arriving via swap is never auto-rendered,
 * and the one-time token is spent when the form is re-rendered without a reload.
 * This script bridges both, for the two versions the plugin supports:
 *
 *   v2 (checkbox): renders every `.g-recaptcha` widget on load and after each swap.
 *                  The token lives in the widget's hidden field, so HTMX serializes
 *                  it with the form; a failed submit swaps in a fresh unrendered
 *                  widget, which we render again -> fresh token, no manual reset.
 *
 *   v3 (invisible): on `htmx:confirm` we defer the request, run grecaptcha.execute()
 *                   (a fresh token every submit), write it into the hidden
 *                   `g-recaptcha-response` field, then let HTMX issue the request.
 *
 * Config is published by the helper as `window.CakeDCUsersReCaptcha = {version, siteKey}`.
 */
(function () {
    'use strict';

    function cfg() {
        return window.CakeDCUsersReCaptcha || {};
    }

    function grecaptchaReady(cb, attempt) {
        if (typeof grecaptcha !== 'undefined' && grecaptcha.ready) {
            grecaptcha.ready(cb);
        } else if ((attempt || 0) < 40) {
            // api.js not loaded yet - retry shortly (capped at ~6s so a blocked
            // api.js does not spin forever; the v3 path also has its own timeout).
            setTimeout(function () { grecaptchaReady(cb, (attempt || 0) + 1); }, 150);
        }
    }

    // --- v2: render unrendered widgets under `root` (defaults to document) ---
    function renderV2(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var widgets = scope.querySelectorAll('.g-recaptcha:not([data-rendered])');
        if (!widgets.length) {
            return;
        }
        grecaptchaReady(function () {
            widgets.forEach(function (el) {
                if (el.getAttribute('data-rendered')) {
                    return;
                }
                try {
                    // data-theme / data-size only apply to auto-render, so forward
                    // them explicitly to render().
                    grecaptcha.render(el, {
                        sitekey: cfg().siteKey,
                        theme: el.getAttribute('data-theme') || undefined,
                        size: el.getAttribute('data-size') || undefined,
                    });
                    el.setAttribute('data-rendered', '1');
                } catch (e) {
                    // Already rendered or transient error - ignore.
                }
            });
        });
    }

    // --- v3: resolve the form element a request originated from ---
    function formOf(el) {
        if (!el) {
            return null;
        }
        if (el.matches && el.matches('form')) {
            return el;
        }
        return el.closest ? el.closest('form') : null;
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (cfg().version === 2) {
            renderV2(document);
        }
    });

    document.addEventListener('htmx:afterSwap', function (evt) {
        if (cfg().version === 2) {
            renderV2(evt.detail && evt.detail.target);
        }
    });

    // v3 tokens waiting to be attached to a form's next issued request. HTMX snapshots
    // the form parameters *before* htmx:confirm, so setting the hidden field there is
    // too late - the token has to be injected in htmx:configRequest instead.
    var pendingTokens = new WeakMap();

    document.addEventListener('htmx:confirm', function (evt) {
        if (cfg().version !== 3) {
            return;
        }
        var form = formOf(evt.detail && evt.detail.elt);
        if (!form || !form.querySelector('[name="g-recaptcha-response"]')) {
            // Not one of our reCaptcha forms - let HTMX proceed normally.
            return;
        }
        evt.preventDefault();

        // Guard against grecaptcha.execute() never settling (e.g. a site key not
        // registered for this domain): after a timeout, issue the request anyway so
        // the form never hangs. The server then rejects the empty token and swaps the
        // error fragment back in. issueRequest is latched so it fires at most once.
        var issued = false;
        function issue(token) {
            if (issued) {
                return;
            }
            issued = true;
            if (token) {
                pendingTokens.set(form, token);
            }
            evt.detail.issueRequest(true); // true = skip re-confirmation
        }
        var timer = setTimeout(function () { issue(null); }, 4000);

        grecaptchaReady(function () {
            try {
                grecaptcha.execute(cfg().siteKey, { action: 'submit' }).then(function (token) {
                    clearTimeout(timer);
                    issue(token);
                }).catch(function () {
                    clearTimeout(timer);
                    issue(null);
                });
            } catch (e) {
                clearTimeout(timer);
                issue(null);
            }
        });
    });

    // Inject the freshly-minted token into the request HTMX is about to send.
    document.addEventListener('htmx:configRequest', function (evt) {
        if (cfg().version !== 3) {
            return;
        }
        var form = formOf(evt.detail && evt.detail.elt);
        if (form && pendingTokens.has(form)) {
            evt.detail.parameters['g-recaptcha-response'] = pendingTokens.get(form);
            pendingTokens.delete(form);
        }
    });
})();
