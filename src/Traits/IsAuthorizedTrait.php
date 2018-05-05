<?php

namespace CakeDC\Users\Traits;

use Cake\Http\ServerRequest;
use Cake\Routing\Exception\MissingRouteException;
use Cake\Routing\Router;
use CakeDC\Auth\Rbac\Rbac;
use Cake\Utility\Hash;
use Zend\Diactoros\Uri;

trait IsAuthorizedTrait
{
    /**
     * Returns true if the target url is authorized for the logged in user
     *
     * @param string|array|null $url url that the user is making request.
     *
     * @return bool
     */
    public function isAuthorized($url = null)
    {
        if (empty($url)) {
            return false;
        }

        if (is_array($url)) {
            return $this->checkRbacPermissions(Router::normalize(Router::reverse($url)));
        }

        try {
            //remove base from $url if exists
            $normalizedUrl = Router::normalize($url);

            return $this->checkRbacPermissions($url);
        } catch (MissingRouteException $ex) {
            //if it's a url pointing to our own app
            if (substr($normalizedUrl, 0, 1) === '/') {
                throw $ex;
            }

            return true;
        }
    }

    /**
     * Check if current user permissions of url
     *
     * @param string $url to check permissions
     *
     * @return bool
     */
    protected function checkRbacPermissions($url)
    {
        $uri = new Uri($url);
        $Rbac = $this->request ? $this->request->getAttribute('rbac') : null;
        if ($Rbac === null) {
            $Rbac = new Rbac();
        }
        $targetRequest = new ServerRequest([
            'uri' => $uri
        ]);
        $params = Router::parseRequest($targetRequest);
        $targetRequest = $targetRequest->withAttribute('params',  $params);

        $user = $this->request->getAttribute('identity');
        $userData = [];
        if ($user) {
            $userData = Hash::get($user, 'User', []);
            $userData = is_object($userData) ? $userData->toArray() : $userData;
        }

        return $Rbac->checkPermissions($userData, $targetRequest);
    }

}