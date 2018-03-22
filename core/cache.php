<?php

/**
 * 文件缓存（长期有效除非删除）
 * Class Cache
 */
class Cache
{
    public function __construct()
    {

    }

    /**
     * 设置缓存
     * @param $file_name 文件名，生成的文件：文件名.php
     * @param $data 缓存数据 会进行序列化
     * @param bool|false $cache 值为true时，文件缓存长期有效除非删除
     * @return bool|mixed
     */
    public static function setCache($file_name, $data, $cache = true)
    {
        $file = CACHE . $file_name . '.php';
        if ($cache && file_exists_case($file)) {
            return self::getCache($file_name);
        } else {
            if (is_null($data) || (is_string($data) && empty($data))) {
                return false;
            }

            $dir = dirname($file);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            if (false === file_put_contents($file, serialize($data))) {
                $err = array('epa_code' => '0004', 'epa_msg' => 'Cache:WRITE ERROR');
                app_exit($err);
            } else {
                return $data;
            }

        }

    }

    /**
     * 获取缓存
     * @param $file_name 文件名
     * @return bool|mixed
     */
    public static function getCache($file_name)
    {
        $file = CACHE . $file_name . '.php';
        if (file_exists_case($file)) {
            $content = file_get_contents($file);
            return unserialize($content);
        } else {
            return false;
        }

    }

    /**
     * 清理缓存
     * @param $file_name
     * @return bool
     */
    public static function clearCache($file_name)
    {
        $file = CACHE . $file_name . '.php';
        if (file_exists_case($file)) {
            return unlink($file);
        } else {
            return true;
        }

    }

}