<?php
App::uses('BaseAuthenticate', 'Controller/Component/Auth');
App::uses('AuthComponent', 'Controller/Component');
App::uses('Router', 'Routing');

/**
 * An authentication adapter for AuthComponent.  Provides the ability to authenticate using COOKIE
 *
 * {{{
 *	$this->Auth->authenticate = array(
 *		'Authenticate.Cookie' => array(
 *			'fields' => array(
 *				'username' => 'username',
 *				'password' => 'password'
 *	 		),
 *			'userModel' => 'User',
 *			'scope' => array('User.active' => 1),
 *			'crypt' => 'rijndael', // Defaults to rijndael(safest), optionally set to 'cipher' if required
 *			'cookie' => array(
 *				'name' => 'RememberMe',
 *				'time' => '+2 weeks',
 *			)
 *		)
 *	)
 * }}}
 *
 * @author Ceeram
 * @copyright Ceeram
 * @license MIT
 * @link https://github.com/ceeram/Authenticate
 */
class CookieAuthenticate extends BaseAuthenticate {

	public function __construct(ComponentCollection $collection, $settings) {
		$this->settings['cookie'] = array(
			'name' => 'RememberMe',
			'time' => '+2 weeks',
			'base' => Router::getRequest()->base
		);
		$this->settings['crypt'] = 'rijndael';
		parent::__construct($collection, $settings);
	}

/**
 * Authenticates the identity contained in the cookie.  Will use the `settings.userModel`, and `settings.fields`
 * to find COOKIE data that is used to find a matching record in the `settings.userModel`.  Will return false if
 * there is no cookie data, either username or password is missing, of if the scope conditions have not been met.
 *
 * @param CakeRequest $request The unused request object
 * @return mixed False on login failure. An array of User data on success.
 * @throws CakeException
 */
	public function getUser(CakeRequest $request) {
		if (!isset($this->_Collection->Cookie) || !$this->_Collection->Cookie instanceof CookieComponent) {
			throw new CakeException('CookieComponent is not loaded');
		}

		$this->_Collection->Cookie->type($this->settings['crypt']);
		list(, $model) = pluginSplit($this->settings['userModel']);

		$data = $this->_Collection->Cookie->read($model);
		if (empty($data)) {
			return false;
		}

		extract($this->settings['fields']);
		if (empty($data[$username]) || empty($data[$password])) {
			return false;
		}

		$user = $this->_findUser($data[$username], $data[$password]);
		if ($user) {
			$this->_Collection->Session->write(AuthComponent::$sessionKey, $user);
			return $user;
		}
		return false;
	}

	public function authenticate(CakeRequest $request, CakeResponse $response) {
		return $this->getUser($request);
	}

	public function logout($user) {
		$this->_Collection->Cookie->destroy();
	}

}
