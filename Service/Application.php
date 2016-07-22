<?php
namespace Zodream\Service;
/**
* 启动
* 
* @author Jason
* @time 2015-12-19
*/
use Zodream\Domain\Autoload;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Cookie;
use Zodream\Infrastructure\DomainObject\ResponseObject;
use Zodream\Infrastructure\Factory;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\EventManager\EventManger;

defined('VERSION') or define('VERSION', 2.0);
defined('APP_DIR') or define('APP_DIR', dirname(dirname(__FILE__)).'/');

class Application {
	/**
	 * 程序启动
	 * @param array $configs
	 * @return ResponseObject
	 */
	public static function main($configs = []) {
		Config::getInstance($configs);
		Autoload::getInstance()
			->setError()
			->shutDown();
		Cookie::restore();
		EventManger::getInstance()->run('appRun');
		$route = Factory::router()->run();
		return $route->run();
	}
}