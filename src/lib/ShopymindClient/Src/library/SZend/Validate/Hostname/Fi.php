<?php

/**
 * SZend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   SZend
 * @package    SZend_Validate
 * @copyright  Copyright (c) 2005-2008 SZend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Fi.php 8064 2008-02-16 10:58:39Z thomas $
 */


/**
 * @see SZend_Validate_Hostname_Interface
 */
require_once dirname(__FILE__) . '/../../Validate/Hostname/Interface.php';


/**
 * @category   SZend
 * @package    SZend_Validate
 * @copyright  Copyright (c) 2005-2008 SZend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class SZend_Validate_Hostname_Fi implements SZend_Validate_Hostname_Interface
{

    /**
     * Returns UTF-8 characters allowed in DNS hostnames for the specified Top-Level-Domain
     *
     * @see http://www.ficora.fi/en/index/palvelut/fiverkkotunnukset/aakkostenkaytto.html Finland (.FI)
     * @return string
     */
    static function getCharacters()
    {
        return '\x{00E5}\x{00E4}\x{00F6}';
    }

}
