<?php

namespace kult_engine;

use kult_engine\debugable;
use kult_engine\singleton;
use kult_engine as k;

	class router
	{
		use debugable;
		use singleton;
		public static $_a_asked;
		public static $_asked;
		public static $_method;
		public static $_route;
		public static $_global_route;
		public static $_argex = '|<!';
		private static $_auto_executor;
		public static $_global_routing = 1;

		public static function setter()
		{
			router::$_method = strtoupper($_SERVER['REQUEST_METHOD']);
			router::$_asked = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
			router::$_a_asked = router::read_asked(router::$_asked);
			router::$_route = array();
			router::$_auto_executor = new router_executor();
		}
		public static function read_asked($brut)
		{
			if(k\contains('|<\\_**_', $brut))
			{
				trigger_error('"|<\\_**_" is reserved by kult_engine\\router', E_USER_ERROR);
				#since you cant get something containing a \, it shouldnt happen
			}
			if($brut === "*" || $brut === "")
			{
				return ['|<\\_**_'];
			}
			if(substr($brut,0,1) !== '/')
			{
				return;
			}
			$brut = substr($brut, 1);
			if(!k\contains('/', $brut))
			{
				return $brut != "" ? [$brut] : ['*'];
			}
			$array = explode('/', $brut);

			if($array[count($array)-1] === "")
			{
				unset($array[count($array)-1]);
			}
			return $array;
		}

		public static function set_route($route,$func,$method='GET')
		{
			router::$_route[count(router::$_route)] = [$route,$func,strtoupper($method)];
		}

		public static function exec()
		{
			if(router::$_global_routing)
			{
				router::disable_global_routing();
				foreach (router::$_global_route as $route)
				{
					router::exec_route($route);
				}
			}

			foreach (router::$_route as $route)
			{
				router::exec_route($route);
			}

		}

		public static function exec_route($route)
		{
			if($route[2] === router::$_method)
				{
					$tmp = router::is_route_applicable($route[0]);
					if($tmp !== 0)
					{
						call_user_func_array($route[1],$tmp);
					}
			}
		}

		public static function disable_global_routing($bool=0)
		{
			router::$_global_routing = $bool;
		}



		public static function is_route_applicable($route)
		{
			$translated_route = router::read_asked($route);
			$args = array();

			if(count($translated_route) ===1 &&$translated_route[0]==='|<\\_**_')
			{
				return $args;
			}

			if(count($translated_route) > count(router::$_a_asked) &&  (count($translated_route)-1 == count(router::$_a_asked) && $translated_route[count($translated_route)-1] != '*'))
			{
				# if route is longuer than uri, route is probably not applicable
				#and if route is just 1 arg longuer than uri, this arg has to be *
				return 0;
			}

			for ($i=0; $i < count($translated_route) ; $i++)
			{ 
				if($translated_route[$i] != '*' && !k\contains(router::$_argex, $translated_route[$i]) && $translated_route[$i] != router::$_a_asked[$i])
				{
					var_dump($translated_route);
					k\echo_br();
					var_dump(router::$_a_asked);
					return 0;
				}
				if(k\contains(router::$_argex, $translated_route[$i]))
				{
					$args[intval(substr($translated_route[$i], strlen(router::$_argex)))] = router::$_a_asked[$i];
				}
			}
			if($translated_route[0] === '*' && router::$_a_asked[0] === '|<\\_**_')
			{
				return 0;
			}

			return $args;
		}
	}


	class router_executor
	{
	  public function __destruct()
	  {
	    router::exec();
	  }
	}

	class global_route
	{
		public $_route;

		public function __construct($route,$func,$method='GET')
		{
			router::$_global_route[count(router::$_global_route)] = [$route,$func,$method];
		}

	}