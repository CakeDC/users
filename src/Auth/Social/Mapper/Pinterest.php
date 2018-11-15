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

namespace CakeDC\Users\Auth\Social\Mapper;

class Pinterest extends AbstractMapper
{
    /**
     * Map for provider fields
     * @var
     */
    protected $_mapFields = [
        'avatar' => 'image.60x60.url',
        'link' => 'url',
    ];
}
