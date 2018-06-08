<?php
namespace app\wap\controller;

use app\wap\model\User;
use think\Controller;
use think\Request;
use think\Hook;

class Home extends Controller{

    protected $app = array(
        '1' => 'D8OZLSE2NEDC0FR4XTGBKHY67UJZ8IK9', //ios
        '2' => 'DFHGKZLSE2NFDEHGFHHR4XTGBKHY67EJZ8IK9', //安卓
        '3' => '@$^^&!##$$%%$%$$^&&asdtans2g234234HJU', //web
    );

    protected $inWhiteList;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        if(in_array(Request::instance()->controller(), ['Index', 'Api'], true)){
            $params = input('');
            //验证签名串是否存在或是否为空
            (!isset($params['signValue']) || empty($params['signValue'])) && $this->apiReturn(201, '签名不能为空');
            $sign = $params['signValue'];
            unset($params['signValue']);

            //如果存在userId，则存入session中
            if((isset($params['uname']) && !empty($params['uname'])) || !session('userId')){
                $userModel = new User();
                $user      = $userModel->getUserByUserName($params['uname']);
                $this->username = $user['UserName'];
                session('userId', $params['uname']);
                session('UserInfo', $user);
            }

            $whiteList  = ['zh652443016', 'xjp360019464','aupl0401'];
            $this->inWhiteList = 1;
            if(in_array($params['uname'], $whiteList)){
                $this->inWhiteList = 1;
            }

            //验证签名
            if(!$this->signValidate($params, $sign)){
                $this->apiReturn(201, $this->newSign);//暂时显示这个签名，用于测试时
                $this->apiReturn(201, '签名错误');
            }
        }
        $this->userId = session('UserInfo.UserID');
    }

    /*
	* 	返回数据到客户端
	*	@param $code type : int		状态码
	*   @param $info type : string  状态信息
	*	@param $data type : mixed	要返回的数据
	*	return json
	*/
    public function apiReturn($code, $data = null, $msg = ''){
        header('Content-Type:application/json; charset=utf-8');//返回JSON数据格式到客户端 包含状态信息

        $jsonData = array(
            'code' => $code,
            'msg'  => $msg ?: ($code == 200 ? '操作成功' : '操作失败'),
            'data' => $data ? $data : null
        );

        exit(json_encode($jsonData));
    }

    //签名校验
    protected function signValidate($data, $sign){
        if(empty($data) || !is_array($data) || !($data['appId'] > 0) || !isset($this->app[$data['appId']])){
            return false;
        }

        $secretKey = $this->app[$data['appId']];
        ksort($data);
        $queryString = $this->http_build_string($data);
//        dump(md5("{$queryString}&{$secretKey}"));die;

        if(md5($queryString.$secretKey) != $sign){
            $this->newSign = md5($queryString.$secretKey);
            return false;
        }

        return true;
    }

    /**
     * 跟系统的http_build_str()功能相同，但不用安装pecl_http扩展
     *
     * @param array $array      需要组合的数组
     * @param string $separator 连接符
     *
     * @return string               连接后的字符串
     * eg: 举例说明
     */
    function http_build_string ( $array, $separator = '&' ) {
        $string = '';
        foreach ( $array as $key => $val ) {
            $string .= "{$key}={$val}{$separator}";
        }
        //去掉最后一个连接符
        return substr( $string, 0, strlen( $string ) - strlen( $separator ) );
    }

    protected function ajaxReturn($data,$type='',$json_option=0) {
        if(empty($type)) $type  =   'JSON';
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data,$json_option));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[config('VAR_JSONP_HANDLER')]) ? $_GET[config('VAR_JSONP_HANDLER')] : config('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data,$json_option).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return',$data);
        }
    }

}