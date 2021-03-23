<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// Route::miss(function () {
//     $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
//     $mobile_browser = '0';
//     if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
//         $mobile_browser++;

//     if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))
//         $mobile_browser++;

//     if(isset($_SERVER['HTTP_X_WAP_PROFILE']))
//         $mobile_browser++;

//     if(isset($_SERVER['HTTP_PROFILE']))
//         $mobile_browser++;

//     $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));

//     $mobile_agents = array(
//         'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
//         'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
//         'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
//         'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
//         'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
//         'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
//         'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
//         'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
//         'wapr','webc','winw','winw','xda','xda-'
//     );

//     if(in_array($mobile_ua, $mobile_agents))
//         $mobile_browser++;

//     if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
//         $mobile_browser++;

//     if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
//         $mobile_browser=0;

//     if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
//         $mobile_browser++;

//     if($_SERVER['SERVER_NAME'] == 'wujin.3todo.com'){
//         // return view(app()->getRootPath() . 'public' . DS . 'index.html');
//     }else{
//         // return view(app()->getRootPath() . 'public' . DS . 'index_pc.html');
//     }

//     if($mobile_browser>0) {
//         // return view(app()->getRootPath() . 'public' . DS . 'index.html');
//     } else {
//         // return view(app()->getRootPath() . 'public' . DS . 'index_pc.html');
//     }
// });
