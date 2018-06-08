<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\admin\validate;
use think\Validate;


class Login extends Validate{

    protected $rule = [
        'UserName'   => 'require',
        'Password'   => 'require',
    ];

    protected $message = [
        'UserName'   => '请输入用户名',
        'Password'   => '请输入密码',
    ];

}