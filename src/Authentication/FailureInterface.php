<?php
/**
 * Copyright (c) Phauthentic (https://github.com/Phauthentic)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Phauthentic (https://github.com/Phauthentic)
 * @link          https://github.com/Phauthentic
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CakeDC\Users\Authentication;

interface FailureInterface
{

    /**
     * Returns failed authenticator.
     *
     * @return \Authentication\Authenticator\AuthenticatorInterface
     */
    public function getAuthenticator();

    /**
     * Returns failed result.
     *
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function getResult();
}