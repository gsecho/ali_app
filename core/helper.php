<?php

if (!function_exists('json')) {
    /**
     * 区分大小写的文件存在判断
     * @param string $filename 文件地址
     * @return boolean
     */
    function file_exists_case($filename)
    {
        if (is_file($filename)) {
            if (IS_WIN && APP_DEBUG) {
                if (basename(realpath($filename)) != basename($filename))
                    return false;
            }
            return true;
        }
        return false;
    }
}

if (!function_exists('exception')) {
    /**
     * 抛出异常处理
     * @param string    $msg  异常消息
     * @param integer   $code 异常代码 默认为0
     * @throws Exception
     */
    function exception($msg, $code = 0)
    {
        throw new \Exception($msg, $code);
    }
}

if (!function_exists('json')) {
    /**
     * 返回Json数据
     * @param mixed   $data 返回的数据
     * @return string
     */
    function json($data = array())
    {
        return json_encode($data);
    }

}

if (!function_exists('app_exit')) {
    /**
     * 返回错误信息
     * @param mixed   $data 返回的数据
     * @return string Json数据
     */
    function app_exit($data = array())
    {
        exit(json($data));
    }

}

if (!function_exists('config')) {
    /**
     * 获取和设置配置参数
     * @param string|array  $name 参数名
     * @return mixed
     */
    function config($name = '')
    {
        if (is_string($name)) {
            return Config::get($name);
        }

        return false;
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存管理
     * @param mixed     $name 缓存名称，如果为数组表示进行缓存设置
     * @param bool     $clear true清理缓存
     * @return mixed
     */
    function cache($name, $clear = false)
    {
        if ($clear) {
            return  Cache::clearCache($name);
        }

        if (is_array($name)) {
            $file_name = $name['name'];
            $data = $name['data'];
            // 缓存设置
            return Cache::setCache($file_name, $data);
        } else {
            //取缓存
            return  Cache::getCache($name);
        }
    }
}


if (!function_exists('getEncryptionToken')) {
    /**
     * 获取token
     * @param string $secret_key 签名密钥
     * @param $epc_signature 签名因子
     * @return string
     */
    function getEncryptionToken($secret_key = '#api_token#', $epc_signature)
    {
        $token = strtoupper(bin2hex(hash_hmac('sha1', $epc_signature, $secret_key, true)));

        return $token;
    }
}

if (!function_exists('encryptionVerify')) {
    /**
     * token效验
     * @param string $secret_key 签名密钥
     * @param $epc_signature 签名因子
     * @param $token TOKEN(令牌)
     * @return string
     */
    function encryptionVerify($secret_key, $epc_signature, $token)
    {
        if (empty($secret_key) || empty($epc_signature) || empty($token)) {
            return false;
        }

        if ($token == getEncryptionToken($secret_key, $epc_signature)) {
            return true;
        } else {
            return false;
        }

    }
}