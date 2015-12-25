<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;

class IpFilter extends FilterObject {

    protected $_defaultOption = array(
        'ipv4' => true,
        'ipv6' => true,
        'private' => true,
        'reserved' => true,
    );

    public function filter($arg) {
        $flags = 0;
        if ($this->_option['ipv4']) {
            $flags |= FILTER_FLAG_IPV4;
        }
        if ($this->_option['ipv6']) {
            $flags |= FILTER_FLAG_IPV6;
        }
        if (!$this->_option['private']) {
            $flags |= FILTER_FLAG_NO_PRIV_RANGE;
        }
        if (!$this->_option['reserved']) {
            $flags |= FILTER_FLAG_NO_RES_RANGE;
        }
        return filter_var($arg, FILTER_VALIDATE_IP, $flags);
    }
}