<?php
namespace Zodream\Infrastructure\Request;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/3
 * Time: 9:29
 */
class Post extends BaseRequest {
    public function __construct() {
        $this->setValues($_POST);
    }
}