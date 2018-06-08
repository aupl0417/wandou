<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Config;
use think\Cache;
// 应用公共文件

//得到高强度不可逆的加密字串
function getSuperMD5($str) {
    return MD5(SHA1($str) . '@$^^&!##$$%%$%$$^&&asdtans2g234234HJU');
}

//得到流水25位时间戳
function getTimeMarkID() {
    list($usec, $sec) = explode(" ", microtime());
    return sprintf('%s%06d%03d', date('YmdHis',$sec),$usec * 1000000,rand(10000, 99999));
}

/**
 * Created by PhpStorm.
 * User: James
 * Date: 2017/10/14
 * Time: 14:26
 */

function curl_post($url,$data,$param=null){
    $curl = curl_init($url);// 要访问的地址
    //curl_setopt($curl, CURLOPT_REFERER, $param['referer']);

    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 MSIE 8.0'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_USERAGENT, 'spider'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    //curl_setopt($curl, CURLOPT_ENCODING, ''); // handle all encodings
    //curl_setopt($curl, CURLOPT_HTTPHEADER, $refer);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
    //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
    //curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址

    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    curl_setopt($curl,CURLOPT_POST,true); // post传输数据
    curl_setopt($curl,CURLOPT_POSTFIELDS,$data);// post传输数据

    //是否为上传文件
    if(!is_null($param)) curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
    $res = curl_exec($curl);
    //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($curl);

    return $res;
}

function curl_get($url){
    $curl = curl_init($url);// 要访问的地址
    //curl_setopt($curl, CURLOPT_REFERER, $param['referer']);

    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 MSIE 8.0'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_USERAGENT, 'spider'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    //curl_setopt($curl, CURLOPT_ENCODING, ''); // handle all encodings
    //curl_setopt($curl, CURLOPT_HTTPHEADER, $refer);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
    //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
    //curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址

    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    $res = curl_exec($curl);
    //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($curl);

    return $res;
}


/**
 * 生成签名
 * @param array $data 要进行签名的数据
 * @param string $field 要进行签字的键名,为空是表是所有key
 * @param integer $type　1表示$field为不进行签名的Key
 */
function _sign($data,$field='',$type=1){
    //$data=array_merge($data,$this->api_cfg);
    //清除不进行签名的字段
    if(isset($data['random'])) unset($data['random']);
    if(!empty($field) && $type==1){
        $field=is_array($field)?$field:@explode(',',$field);
        foreach($data as $key=>$val){
            if(in_array($key, $field)) unset($data[$key]);
        }
    }elseif(!empty($field) && $type!=1){
        $field=is_array($field)?$field:@explode(',',$field);
        foreach($data as $key=>$val){
            if(!in_array($key, $field)) unset($data[$key]);
        }
    }

    ksort($data);
    $query=http_build_query($data).'&'.$data['sign_code'];
    $query=urldecode($query);
    return md5($query);
}

function getToken(){
    Cache::set('TAMLL_TOKEN', null);
    $token = Cache::get('TAMLL_TOKEN');

    if (empty($token)){
        $apiurl = Config::get('API_PARAMS.API_URL') . 'Auth/token';
        $data = array();
        $data["appid"]      = Config::get('API_PARAMS.APPID');
        $data["access_key"] = Config::get('API_PARAMS.ACCESS_KEY');
        $data["secret_key"] = Config::get('API_PARAMS.SECRET_KEY');
        $data["sign_code"]  = Config::get('API_PARAMS.SIGN_CODE');
        $data["device_id"]  = "0000000000000000";
        $data["erp_uid"]    = session_id();
        $data['sign']       = _sign($data);

        $res = json_decode(curl_post($apiurl, $data),true);

        if (!empty($res) && $res['code'] == 1){
            Cache::set('TAMLL_TOKEN', $res['data']['token'], 1800);
            return $res['data']['token'];
        }
    }

    return $token;
}

