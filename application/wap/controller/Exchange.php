<?php
namespace app\wap\controller;

use app\wap\model\ExchangeRecords;
use app\wap\model\GoodsModel;
use app\wap\model\User;
use app\wap\model\UserInfo;
use think\Config;
use think\Db;
use think\Exception;
use think\Request;

class Exchange extends Home{

    public function records(){

        $user = session('UserInfo');
        $records = array();
        if($user){
            //兑换记录
            $recordModel= new ExchangeRecords();
            $records    = $recordModel->getAllDataByUserId(session('UserInfo.UserID'), 'er.ID,er.GoodsName,er.Status,g.Image,er.CreateTime');
            if($records){
                foreach($records as &$val){
                    $val['CreateTime'] = strtotime($val['CreateTime']);
                }
            }
        }

        $this->assign('records', $records);
        return $this->fetch();
    }

    public function detail(){
        $id = input('id', '', 'htmlspecialchars,strip_tags,trim');
        $id = trim($id,  '+');
        $exchangeModel = new ExchangeRecords();
        $data = $exchangeModel->getDataById($id);
        if($data){
            $data['CreateTime'] = strtotime($data['CreateTime']);
        }

        $type   = model('Dictionary')->getByTypeID(1);

        $this->assign('type', $type);
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function makeorder(){
        if(Request::instance()->isAjax()){
            $id = input('id', '', 'intval');
            $mobile = input('mobile', '', 'htmlspecialchars,strip_tags,trim');
            !$id && $this->apiReturn(201, '参数非法');

            $goodsModel = new GoodsModel();
            $goods = $goodsModel->getGoodsById($id);

            (!$goods || $goods['Status'] === 0 || $goods['IsDelete'] == 1) && $this->apiReturn(201, '商品不存在');
            $goods['Stock'] < 1 && $this->apiReturn(201, '商品库存不足');

            $address  = Db::name('Address')->where(['UserId' => $this->userId])->find();
            $userName = '';
            if($goods['Type'] == 0){
                $userName = input('username', '', 'htmlspecialchars,strip_tags,trim');
                !$userName && $this->apiReturn(201, '请输入您的真实姓名');

                !$mobile && $this->apiReturn(201, '请输入手机号码');
                !preg_match('/^1[34578]\d{9}$/',$mobile) && $this->apiReturn(201, '手机号码格式不正确');

                $address    = input('Address', '', 'htmlspecialchars,strip_tags,trim');
                !$address && $this->apiReturn(201, '请填写详细地址');
            }else if($goods['Type'] == 1){
                !$mobile && $this->apiReturn(201, '请输入手机号码');
                !preg_match('/^1[34578]\d{9}$/',$mobile) && $this->apiReturn(201, '手机号码格式不正确');
            }

            $uname = session('userId');
            !$uname && $this->apiReturn(201, '页面已过期');

            $userModel = new User();
            $user      = $userModel->getUserByUserName($uname);

            $userInfoModel = new UserInfo();
            $userInfo = $userInfoModel->getUserByUserId($user['UserID']);
            if($userInfo['WalletMoney'] < $goods['SoldPrice']){
                $this->apiReturn(203, '你的豌豆不足，请充值后再兑换');
            }

            Db::startTrans();
            try{
                $res = Db::name('Goods')->where(['ID' => $id])->setDec('Stock', 1);
                if(!$res){
                    throw new Exception('减少库存失败');
                }

                $data = array(
                    'ID'     => getTimeMarkID(),
                    'UserID' => $user['UserID'],
                    'GoodsId'=> $id,
                    'Type'   => $goods['Type'],
                    'GoodsName'  => $goods['Name'],
                    'Num'        => 1,
                    'CreateTime' => date('Y-m-d H:i:s'),
                    'Mobile'     => $mobile,
                    'UserName'   => $userName ?: $user['UserName'],
                    'Address'    => $address,
                    'ErpUserId'  => $user['ErpUserId'],
                    'BatchSn'    => $goods['BatchSn']
                );

                if($goods['Type'] == 2){
                    $res = $this->sendCoupones($goods['BatchSn']);
                    if(!$res){
                        throw new Exception('发送优惠券失败');
                    }
                    if($res['code'] != 1001){
                        throw new Exception($res['msg']);
                    }
                    $data['Status'] = 1;
                }

                $res = Db::name('exchange_records')->insert($data);
                if(!$res){
                    throw new Exception('插入兑换记录表失败');
                }

                $result = Db::table('TUserInfo')->where(['UserID' => $user['UserID']])->update(['WalletMoney' => $userInfo['WalletMoney'] - $goods['SoldPrice']]);
                if($result === false){
                    throw new Exception('扣除豌豆失败');
                }
                Db::commit();
                $this->apiReturn(200, ['message' => '兑换成功', 'stock' => $goods['Stock'] - 1]);
            }catch (\Exception $e){
                Db::rollback();
                $this->apiReturn(201, '兑换失败');
//                $this->apiReturn(201, $e->getMessage());
            }
        }else{
            $this->apiReturn(201, '非法操作');
        }
    }

    //发送优惠券
    private function sendCoupones($batchSn){
        $apiurl = Config::get('API_PARAMS.API_URL') . 'GameCoupons/sendCoupons';
        $data   = array();
        $data["uid"]     = session('UserInfo.ErpUserId');
        $data["batchSn"] = $batchSn;
        $data["token"]   = getToken();
        return json_decode(curl_post($apiurl, $data),true);
    }

}