<?php
namespace app\wap\controller;

use app\wap\model\GoodsModel;
use app\wap\model\UserInfo;
use think\Db;

class Index extends Home{

    public function index(){
        $cid = input('cid', 0, 'intval');
        $goodsModel = new GoodsModel();
        $realGoods  = $goodsModel->getGoodsByType(0);
        $mobileFee  = $goodsModel->getGoodsByType(1);
        $coupon     = $goodsModel->getGoodsByType(2);
        $address    = Db::name('Address')->where(['UserId' => $this->userId])->find();
        $userInfoModel = new UserInfo();
        $userInfo   = $userInfoModel->getUserByUserId($this->userId, 'WalletMoney');
//        dump($this->inWhiteList);die;
        $this->assign('inWhiteList', $this->inWhiteList);
        $this->assign('userInfo', $userInfo);
        $this->assign('address',  $address);
        $this->assign('realGoods', $realGoods);
        $this->assign('mobileFee', $mobileFee);
        $this->assign('coupon',    $coupon);
        $this->assign('cid',       $cid);
        return $this->fetch();
    }


}
