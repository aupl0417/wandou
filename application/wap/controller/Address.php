<?php
namespace app\wap\controller;

use app\wap\model\ExchangeRecords;
use think\Db;
use think\Request;

class Address extends Home{

    public function index(){

        $address = Db::name('Address')->where(['UserId' => $this->userId])->find();

        $this->assign('address', $address);
        return $this->fetch();
    }

    public function create(){
        if(Request::instance()->isAjax()){
            $data['Mobile']   = input('mobile', '', 'htmlspecialchars,strip_tags,trim');
            $data['UserName'] = input('username', '', 'htmlspecialchars,strip_tags,trim');
            $data['Address']  = input('Address', '', 'htmlspecialchars,strip_tags,trim');
            $userId  = session('UserInfo.UserID');

            !$userId && $this->apiReturn(204, '页面已过期，2秒后将刷新页面！');

            $result  = $this->validate($data, 'Address');
            if($result !== true){
                $this->apiReturn(201, $result);
            }

            if(Db::name('Address')->where(['UserId' => $userId])->count()){
                $res = Db::name('Address')->where(['UserId' => $userId])->update($data);
                $res === false && $this->apiReturn(201, '收货地址修改失败');
                $this->apiReturn(200, '收货地址修改成功');
            }else{
                $data['UserId'] = $userId;
                $res = Db::name('Address')->insert($data);
                !$res && $this->apiReturn(201, '添加收货地址失败');
                $this->apiReturn(200, '添加收货地址成功');
            }
        }
    }

}