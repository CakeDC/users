<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Network\Exception\ForbiddenException;
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
        //set a specific table for API auth, set as null to use Users.table
        'table' => null,
        //set a specific finder for API auth, set as null to use Auth.authenticate.all.finder
        'finder' => null,
    ];

    /**
     * ApiKeyAuthenticate constructor.
     * @param ComponentRegistry $registry registry
     * @param array $config config
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
    }

    /**
     * Authenticate callback
     * Reads the API Key based on configuration and login the user
     *
     * @param \Cake\Http\ServerRequest $request request object.
     * @param Response $response response object.
     * @return mixed
     */
    public function authenticate(ServerRequest $request, Response $response)
    {
        return $this->getUser($request);
    }

    /**
     * Stateless Authentication System
     * http://book.cakephp.org/3.0/en/controllers/components/authentication.html#creating-stateless-authentication-systems
     *
     * Config:
     *   $this->Auth->config('storage', 'Memory');
     *   $this->Auth->config('unauthorizedRedirect', 'false');
     *   $this->Auth->config('checkAuthIn', 'Controller.initialize');
     *   $this->Auth->config('loginAction', false);
     *
     * @param \Cake\Http\ServerRequest $request Cake request object.
     * @return mixed
     */
    public function getUser(ServerRequest $request)
    {
        $type = $this->getConfig('type');
        if (!in_array($type, $this->types)) {
            throw new OutOfBoundsException(__d('CakeDC/Users', 'Type {0} is not valid', $type));
        }

        if (!is_callable([$this, $type])) {
            throw new OutOfBoundsException(__d('CakeDC/Users', 'Type {0} has no associated callable', $type));
        }

        $apiKey = $this->$type($request);
        if (empty($apiKey)) {
            return false;
        }

        if ($this->getConfig('require_ssl') && !$request->is('ssl')) {
            throw new ForbiddenException(__d('CakeDC/Users', 'SSL is required for ApiKey Authentication', $type));
        }

        $this->_config['fields']['username'] = $this->getConfig('field');
        $this->_config['userModel'] = $this->getConfig('table') ?: Configure::read('Users.table');
        $this->_config['finder'] = $this->getConfig('finder') ?:
            Configure::read('Auth.authenticate.all.finder') ?:
                'all';
        $result = $this->_query($apiKey)->first();

        if (empty($result)) {
            return false;
        }

        return $result->toArray();
    }

    /**
     * Get the api key from the querystring
     *
     * @param \Cake\Http\ServerRequest $request request
     * @return string api key
     */
    public function querystring(ServerRequest $request)
    {
        $name = $this->getConfig('name');

        return $request->getQuery($name);
    }

    /**
     * Get the api key from the header
     *
     * @param \Cake\Http\ServerRequest $request request
     * @return string api key
     */
    public function header(ServerRequest $request)
    {
        $name = $this->getConfig('name');
        if (!empty($request->getHeader($name))) {
            return $request->getHeaderLine($name);
        }

        return null;
    }
}
