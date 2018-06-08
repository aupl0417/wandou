<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\wap\validate;
use think\Validate;


class Address extends Validate{

    protected $rule = [
        'UserName'  => 'require',
        'Mobile'    => 'require|checkMobile',
        'Address'   => 'require'
    ];

    protected $message = [
        'UserName.require'   => '请输入真实姓名',
        'Mobile.require'     => '请输入手机号码',
        'Mobile.checkMobile' => '手机号码格式不正确',
        'Address.require'    => '请输入收货地址',
    ];

    public function checkMobile($mobile){
        if(!is_numeric($mobile) || !$mobile || !preg_match('/^1[34578]\d{9}$/',$mobile)){
            return false;
        }

        return true;
    }

}