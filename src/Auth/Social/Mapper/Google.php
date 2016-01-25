<?php
/**
 * Created by PhpStorm.
 * User: ajibarra
 * Date: 10/16/15
 * Time: 7:02 AM
 */

namespace CakeDC\Users\Auth\Social\Mapper;

class Google extends AbstractMapper
{
    /**
     * Map for provider fields
     * @var
     */
    protected $_mapFields = [
        'avatar' => 'image.url',
        'full_name' => 'displayName',
        'email' => 'emails.0.value',
        'first_name' => 'name.givenName',
        'last_name' => 'name.familyName',
        'bio' => 'aboutMe',
        'link' => 'url'
    ];
}
