<?php

namespace CakeDC\Users\Authenticator;


use Authentication\Authenticator\Result;

interface AuthenticatorFeedbackInterface
{
    /**
     * Get the last result of authenticator
     *
     * @return Result|null
     */
    public function getLastResult();

}