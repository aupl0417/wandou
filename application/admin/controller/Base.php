<?php
namespace app\admin\controller;

use think\Cache;
use think\Config;
use think\Controller;
use think\Db;
use think\Request;
use think\Hook;

class Base extends Controller {

    public function __construct(Request $request = null)
    {
        header('Content-type:text/html;charset=utf-8');
        parent::__construct($request);
        if(!session('?user')){
            $this->redirect('login/index');
        }

        $this->assign('user', session('user'));
        $this->assign('module', Request::instance()->controller());
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

    /*
	* 	返回数据到客户端
	*	@param $code type : int		状态码
	*   @param $info type : string  状态信息
	*	@param $data type : mixed	要返回的数据
	*	return json
	*/
    public function apiReturn($code, $data = null){
        header('Content-Type:application/json; charset=utf-8');//返回JSON数据格式到客户端 包含状态信息

        $jsonData = array(
            'code' => $code,
            'data' => $data ? $data : null
        );

        exit(json_encode($jsonData));
    }

}