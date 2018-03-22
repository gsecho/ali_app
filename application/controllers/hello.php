<?php

/**
 * 访问index.php默认请求控制器
 * Class Hello
 */
class Hello
{

    /**
     * 默认请求方法
     */
    public function welcome()
    {
        $err = array('epa_code' => '0003', 'epa_msg' => 'Hello world!Wrong Page!');
        app_exit($err);
    }

}
