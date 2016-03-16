<?php
namespace Zodream\Service;
/**
* 启动
* 
* @author Jason
* @time 2015-12-19
*/
use Zodream\Domain\Html\VerifyCsrfToken;
use Zodream\Domain\Routing\Router;
use Zodream\Domain\Autoload;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Request;

defined('VERSION') or define('VERSION', 2.0);
defined('APP_DIR') or define('APP_DIR', dirname(dirname(__FILE__)).'/');

class Application {
	/**
	 * 程序启动
	 */
	public static function main() {
		Autoload::getInstance()->setError()->shutDown();
		if (Config::getInstance()->get('app.safe', false) && !Request::getInstance()->isGet()) {
			VerifyCsrfToken::verify();
		}
		Router::run();
	}
}