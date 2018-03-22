<?php

/**
 * AliExpress(速卖通) 塔内数据处理
 *
 * 聚石塔下的服务器：我司购买的一台服务器,部署在聚石塔里面（淘宝天猫阿里云联合整出来的玩意）
 * 奇门（程序员眼中的“奇葩之门”）：想拿到AE平台的数据，必须经过奇门这道门槛 即：我司ERP服务器 请求奇门API 奇门发送请求到我司塔内的服务器 塔内再请求AE平台 返回数据后经过奇门返回给ERP接收）
 * 出塔数据：必须严格按照要求输出给奇门，否则奇门不让你出来（你说这货还真把自己当做门神）
 *
 * 开发指南：https://developers.aliexpress.com/doc.htm?spm=a219a.7629065.0.0.gh5qqD#?docType=1&docId=107734
 * 如发布产品信息接口：https://developers.aliexpress.com/doc.htm?spm=a219a.7629065.0.0.gh5qqD#?docType=2&docId=30168
 *
 * 错误信息集合：
 * {"code":27,"msg":"Invalid session","sub_code":"invalid-sessionkey","sub_msg":"SessionKey非法","request_id":"xxxxxxxx"}
 *
 * Class AliExpress
 */
class AliExpress
{
    private $appKey;
    private $secretKey;
    private $sessionKey;

    private $towerResponse;//聚石塔(AE)返回的数据

    private $qmParams;
    private $qmBody;

    private $tokenCacheName = 'AliExpress_Token';
    private $tokenCacheKey = 'CACHE_TOKEN';
    private $errMsg = array();

    private $debug1 = false;//调试1(默认false关闭)：奇门请求调试
    private $debug2 = false;//调试2(默认false关闭)：出塔数据调试
    private $debug3 = false;//调试3(默认false关闭)：验签关闭
    /**
     * 如：http://118.x.x.x/index.php/aliExpress/getIssueDetailByIssueId?app_key=248xxx&id=cn1xxxxxxxxxxx
     * 请注意：需要开启debug2和debug3
     * @var bool
     */
    private $debug4 = false;//调试4(默认false关闭)：返回塔内请求AE平台返回的数据

    public function __construct()
    {

    }

    /**
     * 初始化app证书信息
     * //app_key=248xxx,
     * @throws \Exception
     */
    private function initApp()
    {
        $this->qmParams = $_REQUEST;
        //{"id":"cn1xxxxxxxxxxxxx123","timestamp":"2018-03-21 12:00:00","sign":"xxxxxxxxxxxxxxx","app_key":"248xxx","method":"abcxxxxxabc.abcdef.aliexpress.lgs.getselleraddresses"}

        //print_r($_REQUEST);exit;
        if (!isset($this->qmParams['app_key']) || !isset($this->qmParams['id'])) {
            $this->errMsg['epa_code'] = '2001';
            $this->errMsg['epa_msg'] = 'App Key或账号id参数传递异常';
            return;
        }

        $this->appKey = $this->qmParams['app_key'];
        $this->secretKey = config('secret_' . $this->appKey);
        if (empty($this->secretKey)) {
            $this->errMsg['epa_code'] = '2002';
            $this->errMsg['epa_msg'] = 'App Key：' . $this->appKey . '对应的应用需要配置@技术处理';
            return;
        }

        $this->setToken($this->qmParams['id']);

        //导入SDK
        include(EXTEND . '/taobao-sdk-20180317/TopSdk.php');

        $this->qmBody = file_get_contents('php://input');

        //调试数据
        if ($this->debug1) {
            $this->debug1();
        }

        //验签
        $sip_utils = new \SpiUtils();
        if (!$this->debug3 && !$sip_utils->checkSign4TextRequest($this->qmBody, $this->secretKey)) {
            $this->verifySign();
        }

    }

    /**
     * 调试1
     */
    private function debug1()
    {
        $def_data = json_decode($this->getSign(), true);
        if(isset($def_data['sub_message'])) {
            $def_data['sub_message'] .= json_encode($this->qmBody);
            $def_data['sub_message'] .= json_encode($this->qmParams);
        }
        if(isset($def_data['error_desc'])) {
            $def_data['error_desc'] .= json_encode($this->qmBody);
            $def_data['error_desc'] .= json_encode($this->qmParams);
        }

        app_exit($def_data);
    }

    /**
     * 调试1
     */
    private function debug2()
    {
        if ($this->debug4) {
            echo '<pre>';die(print_r($this->towerResponse));
        }

        $def_data = json_decode($this->getSign(), true);
        if(isset($def_data['sub_message'])) {
            $def_data['sub_message'] .= json_encode($this->qmBody);
            $def_data['sub_message'] .= json_encode($this->qmParams);
            $def_data['sub_message'] .= json_encode($this->towerResponse);
        }
        if(isset($def_data['error_desc'])) {
            $def_data['error_desc'] .= json_encode($this->qmBody);
            $def_data['error_desc'] .= json_encode($this->qmParams);
            $def_data['error_desc'] .= json_encode($this->towerResponse);
        }

        app_exit($def_data);
    }

    /**
     * 设置token即sessionKey
     * @param string $account_id
     */
    private function setToken($account_id = '')
    {
        if (empty($account_id)) {
            $this->errMsg['epa_code'] = '2003';
            $this->errMsg['epa_msg'] = 'cn账号为空,参数异常';//大多账号是cn开头的除xxxxxxxxx
            return;
        }

        $res = cache($this->tokenCacheName);
        if (!$res || empty($res)) {
            $this->errMsg['epa_code'] = '2004';
            $this->errMsg['epa_msg'] = 'Token缓存不存在';
            return;
        }

        //var_dump($res);exit;
        if (!isset($res[$account_id])) {
            $this->errMsg['epa_code'] = '2005';
            $this->errMsg['epa_msg'] = $account_id . '账号Token获取失败';
            return;
        }

        $this->sessionKey = $res[$account_id];
    }

    public function test()
    {
        $data = array('epa_code' => '300', 'epa_msg' => 'Test Test Test');
        app_exit($data);
    }

    //#############################################缓存token即sessionKey
    public function cacheToken()
    {
        $page = $_REQUEST;
        if (!isset($page['request'])) {
            $data = array('epa_code' => '3001', 'epa_msg' => 'Token request error!');
            app_exit($data);
        }
        $request = json_decode(base64_decode($page['request']), true);

        //效验能否操作
        $this->encryptionData($request['token'], $request['epc_signature']);

        //清理缓存
        cache($this->tokenCacheName, true);

        //缓存Token
        $options = array(
            'name' => $this->tokenCacheName,
            'data' => $request['smt_token_data'],
        );
        $res = cache($options);
        if (!$res || empty($res)) {
            $data = array('epa_code' => '3001', 'epa_msg' => 'Token caching error!');
            app_exit($data);
        }

        $data = array('epa_code' => '200', 'epa_msg' => 'Token caching success!');
        app_exit($data);

    }

    /**
     * token效验
     * @param $token TOKEN
     * @param $epc_signature 签名因子
     * @return bool
     */
    private function encryptionData($token, $epc_signature)
    {
        if (encryptionVerify($this->tokenCacheKey, $epc_signature, $token)) {
            return true;
        } else {
            $data = array('epa_code' => '3001', 'epa_msg' => 'Token is error!');
            app_exit($data);
        }
    }

    /**
     * 记录日志
     * 日志目录如：/tmp/logs/文件名+appKey+日期.log
     * @param $data 需要记录的数据
     * @param string $file_name 文件名
     */
    private function log($data, $file_name = 'api_debug') {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $logger = new TopLogger;
        $logger->conf["log_file"] = rtrim(TOP_SDK_WORK_DIR, '\\/') . '/' . "logs/{$file_name}" . $this->appkey . "_" . date("Y-m-d") . ".log";
        $logger->log(array(
            date("Y-m-d H:i:s"),
            $data
        ));
    }

    //#############################################验签
    /**
     * 验签
     */
    private function verifySign()
    {
        exit($this->getSign());
    }

    /**
     * 获取验签内容
     * ..........请注意验签demo会变..........具体查看接口文档 或 参考API服务验证页面提示
     * ..........请注意验签demo会变..........具体查看接口文档 或 参考API服务验证页面提示
     * ..........请注意验签demo会变..........具体查看接口文档 或 参考API服务验证页面提示
     * {"result_success":"false","result_error_code":"sign-check-failure","error_desc":"Illegal request"}
     * @return string
     */
    private function getSign(){
        //验签demo
        $def_sign = '{"flag":"failure","sub_message":"Illegal request","sub_code":"sign-check-failure"}';
        $sign_data = array(
            'qimen.aliexpress.logistics.getlogisticsselleraddresses' => '{"error_desc": "Illegal request","result_error_code": "sign-check-failure","result_success": "false"}',//官方场景 获取卖家地址 验签demo
            'abcxxxxxabc.abcdef.aliexpress.lgs.getselleraddresses' => '{"result_success":"false","result_error_code":"sign-check-failure","error_desc":"Illegal request"}',//自定义验签 获取卖家地址 验签demo
            'abcxxxxxabc.abcdef.aliexpress.issue.findissuedetailbyissue' => '{"flag":"failure","sub_message":"Illegal request","sub_code":"sign-check-failure"}',
        );

        $sign = isset($sign_data[$this->qmParams['method']]) ? $sign_data[$this->qmParams['method']] : $def_sign;
        return $sign;
    }

    //#############################################API接入####################################API接入#####
    //#############################################API接入####################################API接入#####
    //####################################################################################################

    /**
     * 获取卖家地址
     */
    public function getSellerAddress()
    {
        $this->initApp();
        if (!empty($this->errMsg)) {
            app_exit($this->errMsg);
        }

        $request = json_decode($this->qmBody, true);
        $param = '';
        foreach ($request['seller_address_query'] as $item) {
            $param .= $item . ',';
        }
        $param = rtrim($param, ',');
        //$param = 'sender,pickup,refund';//临时需要调试数据

        $c = new \TopClient;
        $c->appkey = $this->appKey;
        $c->secretKey = $this->secretKey;
        $req = new \AliexpressLogisticsRedefiningGetlogisticsselleraddressesRequest;
        $req->setSellerAddressQuery($param);
        $resp = $c->execute($req, $this->sessionKey);
        $this->towerResponse = $response = json_decode(json_encode($resp), true);

        //调试数据
        if ($this->debug2) {
            $this->debug2();
        }

        $str = '{"result_error_code": 0,"result_success": true,"error_desc":"System error--出塔数据--"}';//可以输出结果
        $arr = json_decode($str, true);
        if (isset($response['result_success']) && $response['result_success'] == 0) {
            $arr['error_desc'] = $response['error_desc'];
            $arr['error_desc'] .= '--原始数据--' . json_encode($response);
            $arr['result_error_code'] = $response['result_error_code'];
        }

        if (isset($response['refund_seller_address_list']['refundselleraddresslist'])) {
            $arr['refund_seller_address_list'] = $response['refund_seller_address_list']['refundselleraddresslist'];
        }
        if (isset($response['pickup_seller_address_list']['pickupselleraddresslist'])) {
            $pickup = $response['pickup_seller_address_list']['pickupselleraddresslist'];
            $arr['pickup_seller_address_list'] = $pickup;
        }
        $sender = $response['sender_seller_address_list']['senderselleraddresslist'];
        $arr['sender_seller_address_list'] = $sender;

        app_exit($arr);

    }

    /**
     * 根据纠纷ID，获取协商数据(新版纠纷)
     */
    public function getIssueDetailByIssueId()
    {
        $this->initApp();
        if (!empty($this->errMsg)) {
            app_exit($this->errMsg);
        }

        $request = json_decode($this->qmBody, true);
        $param = $request['issue_id'];//(string)
//        $param = '400xxxxxxxxxx';//临时需要调试数据

        $c = new \TopClient;
        $c->appkey = $this->appKey;
        $c->secretKey = $this->secretKey;
        $req = new \AliexpressIssueRedefiningFindissuedetailbyissueidRequest;
        $req->setIssueId($param);
        $resp = $c->execute($req, $this->sessionKey);
        $this->towerResponse = $response = json_decode(json_encode($resp), true);

        //调试数据
        if ($this->debug2) {
            $this->debug2();
        }

        $this->log($this->qmParams);
        $this->log($this->qmBody);
        $this->log($this->towerResponse);

        app_exit($this->towerResponse);

    }


}
