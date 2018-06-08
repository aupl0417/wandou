<?php
namespace app\wap\controller;

use app\wap\model\GoodsModel;
use app\wap\model\UserInfo;
use think\Db;

class Api extends Home{

    public function getWalletMoney(){

        $usreInfoModel = new UserInfo();
        $userInfo      = $usreInfoModel->getUserByUserId($this->userId, 'UserID,WalletMoney');
        !$userInfo && $this->apiReturn(201, '用户不存在');
        $userInfo['UserName'] = $this->username;
        unset($userInfo['ROW_NUMBER']);
        $this->apiReturn(200, $userInfo);
    }


}
