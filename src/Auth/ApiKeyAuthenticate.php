<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Request;
use Cake\Network\Response;
use \OutOfBoundsException;

/**
 * Class ApiKeyAuthenticate. Login the uses by API Key
 */
class ApiKeyAuthenticate extends BaseAuthenticate
{

    const TYPE_QUERYSTRING = 'querystring';
    const TYPE_HEADER = 'header';

    public $types = [self::TYPE_QUERYSTRING, self::TYPE_HEADER];

    protected $_defaultConfig = [
        //type, can be either querystring or header
        'type' => self::TYPE_QUERYSTRING,
        //name to retrieve the api key value from
        'name' => 'api_key',
        //db field where the key is stored
        'field' => 'api_token',
        //require SSL to pass the token. You should always require SSL to use tokens for Auth
        'require_ssl' => true,
    ];

    /**
     * Authenticate callback
     * Reads the API Key based on configuration and login the user
     *
     * @param Request $request Cake request object.
     * @param Response $response Cake response object.
     * @return mixed
     */
    public function authenticate(Request $request, Response $response)
    {
        $type = $this->config('type');
        if (!in_array($type, $this->types)) {
            throw new OutOfBoundsException(__d('Users', 'Type {0} is not valid', $type));
        }

        if (!is_callable([$this, $type])) {
            throw new OutOfBoundsException(__d('Users', 'Type {0} has no associated callable', $type));
        }

        $apiKey = $this->$type($request);
        if (empty($apiKey)) {
            return false;
        }

        if ($this->config('require_ssl') && !$request->is('ssl')) {
            throw new ForbiddenException(__d('Users', 'SSL is required for ApiKey Authentication', $type));
        }

        $this->_config['fields']['username'] = $this->config('field');
        $this->_config['userModel'] = Configure::read('Users.table');
        $this->_config['finder'] = 'all';
        $result = $this->_query($apiKey)->first();

        if (empty($result)) {
            return false;
        }

        return $result->toArray();
        //idea: add array with checks to be passed to $request->is(...)
    }

    /**
     * Get the api key from the querystring
     *
     * @param Request $request request
     * @return string api key
     */
    public function querystring(Request $request)
    {
        $name = $this->config('name');
        return $request->query($name);
    }

    /**
     * Get the api key from the header
     *
     * @param Request $request request
     * @return string api key
     */
    public function header(Request $request)
    {
        $name = $this->config('name');
        return $request->header($name);
    }
}
