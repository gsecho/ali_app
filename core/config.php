<?php

/**
 * Class Config
 */
class Config
{
    /**
     * @var array 配置参数
     */
    private static $config = array();

    /**
     * 加载配置文件
     * @param $file
     */
    public function load($file)
    {
        if (!file_exists_case($file)) {
            $err = array('epa_code' => '-1002', 'epa_msg' => 'file(' . rtrim(basename($file), '.php') . ') load error');
            app_exit($err);
        }
        self::$config = include_once($file);
    }

    /**
     * 获取配置内容
     * @param null $name
     * @return bool
     */
    public function get($name = null)
    {
        if (is_null($name) || empty($name)) {
            return false;
        }

        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }

        return false;
    }

}
