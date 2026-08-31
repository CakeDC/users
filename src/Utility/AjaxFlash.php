<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Utility;

use Cake\Http\Session;

/**
 * Reads and classifies the first pending flash message for ajax/JSON responses.
 *
 * Both AjaxResponseComponent (render path) and AjaxRedirectMiddleware (redirect
 * path) consume the flash through here, so the JSON `flash` envelope and the
 * success/failure decision are derived the same way in both places. The flash
 * `type` is taken from the message `element` name, matching how CakePHP's
 * FlashComponent stores it (`error()` -> element `error`, `success()` ->
 * `success`, etc.).
 *
 * `consume()` drains the whole Flash stack on purpose: a JSON/ajax response is a
 * terminal transition for the client, so any queued message is delivered in the
 * body and must not linger to be rendered on a later full-page load.
 */
class AjaxFlash
{
    /**
     * Read the first pending flash message and clear the Flash stack.
     *
     * @param \Cake\Http\Session $session The session holding the Flash stack.
     * @return array|null `['type' => 'error'|'success'|'info', 'message' => string]`,
     *   or null when no flash is set.
     */
    public static function consume(Session $session): ?array
    {
        $stack = $session->read('Flash');
        if (empty($stack)) {
            return null;
        }

        $result = null;
        foreach ($stack as $messages) {
            foreach ((array)$messages as $entry) {
                if (!is_array($entry) || !isset($entry['message'])) {
                    continue;
                }
                $element = (string)($entry['element'] ?? '');
                $type = 'info';
                if (str_contains($element, 'error')) {
                    $type = 'error';
                } elseif (str_contains($element, 'success')) {
                    $type = 'success';
                }
                $result = ['type' => $type, 'message' => $entry['message']];
                break 2;
            }
        }
        $session->delete('Flash');

        return $result;
    }
}
