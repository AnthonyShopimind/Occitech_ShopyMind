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
 * @version    $Id: Ip.php 8064 2008-02-16 10:58:39Z thomas $
 */


/**
 * @see SZend_Validate_Abstract
 */
require_once dirname(__FILE__) . '/../Validate/Abstract.php';


/**
 * @category   SZend
 * @package    SZend_Validate
 * @copyright  Copyright (c) 2005-2008 SZend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class SZend_Validate_Ip extends SZend_Validate_Abstract
{

    const NOT_IP_ADDRESS = 'notIpAddress';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_IP_ADDRESS => "'%value%' does not appear to be a valid IP address"
    );

    /**
     * Defined by SZend_Validate_Interface
     *
     * Returns true if and only if $value is a valid IP address
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        if (ip2long($valueString) === false) {
            $this->_error();
            return false;
        }

        return true;
    }

}
