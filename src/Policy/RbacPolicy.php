<?php
namespace CakeDC\Users\Policy;

use CakeDC\Auth\Rbac\Rbac;
use Psr\Http\Message\ServerRequestInterface;

class RbacPolicy
{
    /**
     * Check rbac permission
     *
     * @param \Authorization\IdentityInterface|null $identity user identity
     * @param ServerRequestInterface $resource server request
     * @return bool
     */
    public function canAccess($identity, $resource)
    {
        $rbac = $resource->getAttribute('rbac') ?? new Rbac();

        $user = $identity ? $identity->getOriginalData()->toArray() : [];

        return (bool)$rbac->checkPermissions($user, $resource);
    }
}
