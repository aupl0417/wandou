<?php
namespace app\admin\controller;

use think\Config;

class Publics extends Base {

    public function getBatchInfo(){

        $batchSn = input('batchSn', '', 'htmlspecialchars,strip_tags,trim');
        $apiurl  = Config::get('API_PARAMS.API_URL') . 'GameCoupons/CouponsBetchDetail';
        $data    = array();
        $data["batchSn"] = $batchSn;
        $data["token"]   = getToken();
        $res = json_decode(curl_post($apiurl,$data),true);

        $this->ajaxReturn($res);
    }

}