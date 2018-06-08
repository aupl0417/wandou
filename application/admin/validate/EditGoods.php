<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\admin\validate;
use think\Validate;


class EditGoods extends Validate{

    protected $rule = [
        'Name'      => 'require',
        'ShotName'  => 'require',
        'BatchSn'   => 'requireIf:Type,2',
        'Price'     => 'require|number',
        'SoldPrice' => 'require|number',
        'Stock'     => 'require|number',
        'Status'    => 'number|in:0,1',
        'Image'     => 'require',
    ];

    protected $message = [
        'Name.require'      => '请输入物品名称',
        'ShotName'          => '请输入物品简称',
        'BatchSn'           => '请输入批次代码',
        'Price.require'     => '请输入面额',
        'Price.number'      => '面额必须是数字',
        'SoldPrice.require' => '请输入售价',
        'SoldPrice.number'  => '售价必须是数字',
        'Stock.require'     => '请输入库存',
        'Stock.number'      => '库存必须是数字',
        'Status.number'     => '必须是数字',
        'Status.in'         => '状态有误',
        'Image.require'     => '请上传图片',
    ];

}