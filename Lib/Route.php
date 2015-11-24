<?php 
namespace App\Lib;
/*****************************************************
* 路由
*
*
*
********************************************************/
use App\Lib\Helper\HUrl;
use App\Lib\Object\OString;
use App\Lib\Object\OArray;
use App\Lib\Enum\ERoute;

defined("APP_URL")         or define('APP_URL', Base::config('app.host'));
defined('APP_MODE')        or define('APP_MODE', Base::config('app.mode'));
defined("APP_CONTROLLER")  or define('APP_CONTROLLER', Base::config('app.controller', 'Controller'));
defined('APP_MODEL')       or define('APP_MODEL', Base::config('app.model', 'Model'));
defined('APP_ACTION')      or define('APP_ACTION', Base::config('app.action', 'Action'));

final class Route {
	
	public static $route;
	/**
	 * 加载控制器和视图
	 *
	 * @access globe
	 * @param $c string 控制器的名称
	 * @param $v string 视图所在的方法名
	 */
	public static function load() {
		$routes      = self::get();
		$controllers = $routes['controller'];
		$action      = $routes['action'];
		$values      = isset($routes['value']) ? $routes['value'] : array();
		unset($routes);
		if(self::call_func($controllers, $action, $values)) {
			return ;
		}
		if ($action == 'index' && count($controllers) == 1 && self::call_func(array('Home'), strtolower($controllers[0]), $values)) {
			return ;
		}
		$tem = $controllers;
		$tem[] = $action;
		if(self::call_func($tem, 'index', $values)) {
			return ;
		}
		unset($tem);
		for ($i = 0, $len = count($controllers); $i < $len; $i ++) {
			array_unshift($values, $action);
			$action = array_pop($controllers);
			if (empty($controllers)) {
				$controllers = array('home');
			}
			if (self::call_func($controllers, $action, $values)) {
				return ;
			}
		}
		array_unshift($values, $action);
		$action = 'index';
		if (self::call_func($controllers, $action, $values)) {
			return ;
		}
		Base::error(0, ' 请保证默认HomeController->indexAction存在！', __FILE__ ,__LINE__);
		return;
		
	}
	
	private static function call_func($controllers, $action, $values) {
		$controller = APP_MODULE. '\\Controller\\'. implode('\\', $controllers). APP_CONTROLLER;
		if ( class_exists($controller)) {
			$controller = new $controller();
			if (method_exists($controller, $action. APP_ACTION)) {
				$controller -> before($action);
				self::$route = array(
					'controller' => $controllers,
					'action'     => $action,
					'value'      => $values
				);
				call_user_func_array( array($controller, $action. APP_ACTION), $values);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 分类获取路由
	 * @return \App\Lib\array|multitype:\App\Lib\array |\App\Lib\Ambigous|\App\Lib\unknown
	 */
	private static function get() {
		$url = new Route();
		if (!empty(Base::$request) && Base::$request->isCli()) {
			//return $url->cli();	
		}
		switch (APP_MODE) {
			case ERoute::COMMON:
				return $url -> c();
				break;
			case ERoute::R:
				return $url -> r();
				break;
			case ERoute::PHP:
				return $url -> u();
				break;
			case ERoute::SHORT:
				return $url -> s();
				break;
			case ERoute::GRACE:
				return $url -> y();
				break;
			case ERoute::REGEX:
				return $url -> p();
				break;
			case ERoute::CONFIG:
			    return $url -> d();
			    break;
			default:
				return $url -> c();
				break;
		}
	} 
	
	/**
	 * 根据配置文件中自定义的路由解析
	 */
	private function d() {
		$url    = HUrl::request_uri();
		$url    = explode('?', $url)[0];
		$url    = trim($url, '/');
		$routes = Base::config('route');
		if (!is_array($routes)) {
			return $this->getRoute($url);
		}
		if (array_key_exists($url, $routes)) {
			return $this->getRoute($routes[$url]);
		}
		foreach ($routes as $key => $value) {
		    $pattern = str_replace(':num', '[0-9]+', $key);
		    $pattern = str_replace(':any', '[^/]+', $pattern);
			$pattern = str_replace('/', '\\/', $pattern);
		    $matchs  = array();
		    preg_match('/'.$pattern.'/i', $url, $matchs);
		    if(count($matchs) > 0 && $matchs[0] === $url) {
		        $route = $value;
		        foreach ($matchs as $k => $val) {
		          $route = str_replace('$'.$k, $val, $route);
		        }
		        return $this->getRoute($route);
		    }
		}
		return $this->getRoute($url);
	}
	
	/**
	 * 获取c、v的参数解析   ?c=home&v=index
	 * @return multitype:array NULL
	 */
	private function c() {
		$values = explode('/', Base::$request->get('v' , 'index'));
		$routes = array(
			'controller' => OArray::ucFirst(explode('/', Base::$request->get('c' , 'home'))),
			'action'   => array_shift($values),
			'value'      => $values
		);
		return $routes;
	}
	
	/**
	 * 获取r的参数解析     格式?r=home/index
	 */
	private function r() {
		return $this->getRoute(Base::$request->get('r', 'home/index'));
	}
	
	/**
	 * 带index。php的链接解析，格式 index.php/home/index
	 */
	private function u() {
		$url = HUrl::request_uri();
		$arr = OString::toArray($url, '.php', 2, array('', '/home/index'));
		return $this->getRoute($arr[1]);
	}
	
	/**
	 * 优雅链接解析
	 */
	private function y() {
	    $url    = HUrl::request_uri();
	    $url    = explode('?', $url)[0];
		return $this->getRoute($url);
	}
	
	/**
	 * 自定义正则匹配路由
	 * @return unknown
	 */
	private function p() {
		$url = HUrl::request_uri();
		preg_match($preg, $url, $result);
		return $result;
	}
	
	/**
	 * 短链接匹配
	 * 
	 * @return Ambigous <array, string>
	 */
	private function s() {
		$key = Base::$request->get('s');
		if ($key === null) {
			$url = HUrl::request_uri();
			$ar  = explode('/', $url, 2);
			$ar  = explode('?', $ar[1], 2);
			$key = $ar[0];
			$key = $key == '' ? '*' : $key;
		}
		if (strlen($key) < 4) {
			$short = Base::config('short.'.$key);
			$arr   = OString::toArray($short , '.', 2, array('home', 'index')); 
		} else {
			
		}
		
		return $arr;
	}
	
	/**
	 * 控制台的路由
	 */
	private function cli() {
		$url = Base::$request->server('argv')[0];
		return $this->getRoute($url);
	}
	
	/**
	 * 获取路由 home/index
	 * @param string $url
	 * @return array
	 */
	private function getRoute($url = 'home/index') {
		$url    = trim($url, '/');
		$routes = explode('/', $url);
	    if (!isset($routes[0]) || $routes[0] === '') {
	        $routes[0] = 'home';
	    }
	    if (!isset($routes[1]) || $routes[1] === '') {
	        $routes[1] = 'index';
	    }
	    return $this->getValue($routes);
	}
	
	/**
	 * 根据数字判断是否带值
	 * @param array $routes
	 * @return array:
	 */
	private function getValue($routes) {
	    $values = array();
	    for ($i = 0, $len = count($routes); $i < $len; $i++) {
	        if (is_numeric($routes[$i])) {
	            $values = array_splice($routes, $i);
	            break;
	        }
	    }
	    switch (count($routes)) {
			case 0:
				$routes[] = 'home';
			case 1:
				$routes[] = 'index';
				break;
			default:
				break;
		}
	    return array(
		    'action'   => array_pop($routes),
		    'controller' => OArray::ucFirst($routes),
	        'value'      => $values
		);
	}
	
	/**
	 * 判断当前网址是否是url
	 * @param string $url
	 * @return boolean
	 */
	public static function judge($url = null) {
		$route = implode('/', self::$route['controller']);
		if ($url === $route) {
			return true;
		}
		$route .= '/'. self::$route['action'];
		if (empty($url) || $url == '/') {
			return $route == 'Home/index';
		}
		if ($url === $route) {
			return true;
		}
		$route .= '/'. implode('/', self::$route['value']);
		if ($url === $route) {
			return true;
		}
		return false;
	}
}