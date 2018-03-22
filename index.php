<?php
/**
 * 简单MVC架构
 */
date_default_timezone_set('Asia/Shanghai');

//定义application路径
define('APPPATH', trim(__DIR__ . '/'));
define('CORE', trim(__DIR__ . '/core/'));
define('EXTEND', trim(__DIR__ . '/extend/'));
define('PUBLICDIR', trim(__DIR__ . '/public/'));
define('RUNTIME', trim(__DIR__ . '/runtime/'));
define('CACHE', RUNTIME . '/cache/');
define('LOG', RUNTIME . '/log/');

define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
defined('APP_DEBUG') or define('APP_DEBUG', false); // 是否调试模式

//加载助手函数
include(CORE . '/helper.php');

function loadCoreFile()
{
    $files_arr = include(CORE . '/load.php');
    foreach ($files_arr as $key => $files) {
        foreach ($files as $file) {
            if (!file_exists_case($file)) {
                $err = array('epa_code' => '-1001', 'epa_msg' => 'file(' . rtrim(basename($file), '.php') . ') load error');
                app_exit($err);
            }
            include_once($file);
        }
    }
}
//加载其它核心文件
loadCoreFile();

//获得请求地址
$root = $_SERVER['SCRIPT_NAME'];
$request = $_SERVER['REQUEST_URI'];
$URI = array();

//获得index.php 后面的地址
$url = trim(str_replace($root, '', $request), '/');
$url = str_replace('/\\', '/', $url);
$url = str_replace('//', '/', $url);

//如果为空，则是访问根地址
if (empty($url)) {
    //默认控制器和默认方法
    $class = 'hello';
    $func = 'welcome';
} else {
    $URI = explode('/', $url);

    //如果function为空 则默认访问index
    if (count($URI) < 2) {
        $class = $URI[0];
        $func = 'index';
    } else {
        $class = $URI[0];
        $func = $URI[1];
        if (preg_match('#[?]{1}(.*=.*&?)+#', $func, $matches) === 1) {
            $func = str_replace($matches[0], '', $func);
        }
        //echo $func;exit;
    }
}

//控制器加载
$c_file = APPPATH  . '/application/controllers/' . $class . '.php';
//echo $c_file ;exit();
if (!file_exists_case($c_file)) {
    $err = array('epa_code' => '0001', 'epa_msg' => 'controller(' . $class . ') not exists');
    app_exit($err);
}
//把class加载进来
include($c_file);

//实例化->将控制器首字母大写
// 使用前检查类是否存在
if (class_exists($class)) {
    $class_name = ucfirst($class);
    $obj = new $class_name;
}

if (!method_exists($obj, $func)) {
    $err = array('epa_code' => '0002', 'epa_msg' => 'The method(' . $func . ') under the controller(' . $class . ') does not exist');
    app_exit($err);
}

//加载应用配置
$config = new Config();
$config_file = APPPATH . '/application/config.php';
$config->load($config_file);


call_user_func_array(
    //调用内部function
    array($obj, $func),
    //传递参数
    array_slice($URI, 2)
);
