<?php 
namespace App\Lib\Helper;

class HUrl implements IBase
{
	/**
	 * 上个页面网址
	 *
	 * @return string|bool 网址
     */
	public static function referer()
	{
		if( isset($_SERVER['HTTP_REFERER']) );
		{
			return $_SERVER['HTTP_REFERER'];
		}
		
		return FALSE;
	}
	
	/**
	 * 产生完整的网址
	 *
	 * @access globe
	 *
	 * @param string $file 本站链接
	 * @param bool $extra 是否输出
	 *
	 * @return string
	 */
	public static function to($file = null,$extra = null,$secret = FALSE)
	{
		if($file === null)
		{
			$file = self::request_uri();
		}
		
		$url = rtrim(APP_URL,'/').'/'.ltrim($file,'/');
		
		if( $extra === null )
		{
			return $url;
		}
		
		if(strpos($url,'?') === false)
		{
			$url .= '?'.$extra;
		}else {
			$url .= '&'.$extra;
		}
		
		return $url;
	}
	
	/**
	 * 获取网址
	 *
	 * @return string 真实显示的网址
     */
	public static function request_uri()
	{
		$uri = '';
		if ( isset($_SERVER['REQUEST_URI'] ) )
		{
			$uri = $_SERVER['REQUEST_URI'];
		}
		else
		{
			if ( isset( $_SERVER['argv'] ) )
			{
				$uri = $_SERVER['REQUEST_URI'];
			}
			else
			{
				if (isset($_SERVER['argv']))
				{
					$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
				}
				else
				{
					$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
				}
			}
		}
		return $uri;
	}
}