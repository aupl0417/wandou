<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\admin\validate;
use think\Validate;


class Delivery extends Validate{

    protected $rule = [
        'ID'     => 'require|checkID',
        'DeliveryName'      => 'require',
        'DeliveryNum'  => 'require',
    ];

    protected $message = [
        'ID.require'     => '订单ID不能为空',
        'ID.checkID'      => '订单ID格式非法',
        'DeliveryName.require'      => '请输入物流名称',
        'DeliveryNum.require' => '请输入物流单号',
    ];

    public function checkID($id){
        if(!is_numeric($id) || !$id){
            return false;
        }

        return true;
    }

}