<?php

namespace CakeDC\Users\Test\TestCase\Policy;

use Authentication\Identity;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use CakeDC\Auth\Rbac\Rbac;
use CakeDC\Users\Model\Entity\User;
use CakeDC\Users\Policy\RbacPolicy;

class RbacPolicyTest extends TestCase
{
    /**
     * Test before method, with rbac returning true
     */
    public function testBeforeRbacReturnedTrue()
    {
        $user = new User([
            'id' => '00000000-0000-0000-0000-000000000001',
            'password' => '12345'
        ]);
        $identity = new Identity($user);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withAttribute('identity', $identity);
        $rbac = $this->getMockBuilder(Rbac::class)->setMethods(['checkPermissions'])->getMock();
        $request = $request->withAttribute('rbac', $rbac);
        $rbac->expects($this->once())
            ->method('checkPermissions')
            ->with(
                $this->equalTo($identity->getOriginalData()->toArray()),
                $this->equalTo($request)
            )
            ->will($this->returnValue(true));
        $policy = new RbacPolicy();
        $this->assertTrue($policy->canAccess($identity, $request));
    }

    /**
     * Test before method, with rbac returning false
     */
    public function testBeforeRbacReturnedFalse()
    {
        $user = new User([
            'id' => '00000000-0000-0000-0000-000000000001',
            'password' => '12345'
        ]);
        $identity = new Identity($user);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withAttribute('identity', $identity);
        $rbac = $this->getMockBuilder(Rbac::class)->setMethods(['checkPermissions'])->getMock();
        $request = $request->withAttribute('rbac', $rbac);
        $rbac->expects($this->once())
            ->method('checkPermissions')
            ->with(
                $this->equalTo($identity->getOriginalData()->toArray()),
                $this->equalTo($request)
            )
            ->will($this->returnValue(false));
        $request = $request->withAttribute('rbac', $rbac);
        $policy = new RbacPolicy();
        $this->assertFalse($policy->canAccess($identity, $request));
    }
}
