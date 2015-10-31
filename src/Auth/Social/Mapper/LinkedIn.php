<?php
/**
 * Created by PhpStorm.
 * User: ajibarra
 * Date: 10/16/15
 * Time: 7:02 AM
 */

namespace CakeDC\Users\Auth\Social\Mapper;

class LinkedIn extends AbstractMapper
{
    /**
     * Map for provider fields
     * @var
     */
    protected $_mapFields = [
        'avatar' => 'pictureUrl',
        'first_name' => 'firstName',
        'last_name' => 'lastName',
        'email' => 'emailAddress',
        'bio' => 'headline',
        'link' => 'publicProfileUrl'
    ];
}