<?php
namespace app\admin\controller;


use app\admin\model\ExchangeRecords;
use app\wap\model\GoodsModel;
use think\Db;
use think\Exception;
use think\Request;
use app\admin\model\User;

class Exchange extends Base{

    public function index(){

        $model = new ExchangeRecords();
        $field = 'er.ID,er.UserID,er.Type,er.GoodsName,er.Num,er.CreateTime,er.Status,er.Mobile,tu.UserName,er.GoodsId';
        $data  = $model->getAllDataBySql($field);
        if($data){
            foreach ($data as &$val){
                $val['CreateTime'] = strtotime($val['CreateTime']);
            }
        }

        $type   = model('Dictionary')->getByTypeID(1);

        $this->assign('type', $type);
        $this->assign('data', $data);
        $this->assign('title', '兑换记录-豌豆后台管理');
        $this->assign('nav', '兑换记录');
        return $this->fetch();
    }

    public function detail(){
        $id     = input('id', '', 'htmlspecialchars,strip_tags,trim');

        $model  = new ExchangeRecords();
        $result = $model->getByID($id);
        !$result && $this->error('记录不存在');

        $result['CreateTime'] = strtotime($result['CreateTime']);

        $type   = model('Dictionary')->getByTypeID(1);

        $this->assign('data', $result);
        $this->assign('type', $type);
        $this->assign('title', '订单详情');
        $this->assign('nav', '订单详情');
        return $this->fetch();
    }

    public function delivery(){
        $id = input('id', '', 'htmlspecialchars,strip_tags,trim');
        (!is_numeric($id) || !$id) && $this->error('非法参数', url('Exchange/index'));

        $this->assign('id', $id);
        $this->assign('title', '发货');
        $this->assign('nav', '发货');
        return $this->fetch();
    }

    public function deliveryPost(){
        if(Request::instance()->isAjax()){
            $data = array(
                'ID'           => input('id', '', 'htmlspecialchars,strip_tags,trim'),
                'DeliveryName' => input('DeliveryName', '', 'htmlspecialchars,strip_tags,trim'),
                'DeliveryNum'  => input('DeliveryNum', '', 'htmlspecialchars,strip_tags,trim'),
                'Status'       => 1,
            );
            $result = $this->validate($data, 'Delivery');
            if($result !== true){
                $this->ajaxReturn(['code' => 201,  'message' => $result]);
            }

            $exchangeModel = new ExchangeRecords();
            $res = $exchangeModel->saveData($data);
            !$res && $this->ajaxReturn(['code' => 201,  'message' => '发货失败']);
            $this->ajaxReturn(['code' => 200,  'message' => '发货成功']);
        }else{
            $this->ajaxReturn(['code' => 201, 'message' => '非法操作']);
        }
    }

    public function act(){
        if(Request::instance()->isAjax()){
            $id  = input('id', '', 'htmlspecialchars,strip_tags,trim');
            $act = input('act', '', 'htmlspecialchars,strip_tags,trim');

            (!$id || !$act || !in_array($act, ['deal', 'cancel'], true)) && $this->ajaxReturn(['code' => 201, 'message' => '非法参数']);

            $msg = $act == 'cancel' ? '撤消' : '处理';

            Db::startTrans();
            try{
                if($act == 'cancel'){
                    $recordsModel = new ExchangeRecords();
                    $records      = $recordsModel->getByID($id, 'SoldPrice,UserID,er.Status', true);
                    ($records && $records['Status'] == -1) && $this->ajaxReturn(['201', 'message' => '该记录已撤消']);
                    $userInfo     = Db::table('TUserInfo')->field('WalletMoney')->where(['UserID' => $records['UserID']])->find();
                    if($records && $userInfo){
                        $res = Db::table('TUserInfo')->where(['UserID' => $records['UserID']])->update(['WalletMoney' => $userInfo['WalletMoney'] + $records['SoldPrice']]);
                        if($res === false){
                            throw new Exception('用戶豌豆增加失敗');
                        }
                    }else{
                        throw new Exception('记录不存在或用户不存在');
                    }
                }

                $param['Status'] = $act == 'cancel' ? '-1' : '1';
                $res = Db::name('exchange_records')->where(['ID' => $id])->update($param);
                if($res === false){
                    throw new Exception('状态更改失败');
                }
                Db::commit();
                $this->ajaxReturn(['code' => 200, 'message' => $msg . '成功']);
            }catch (\Exception $e){
                Db::rollback();
                $this->ajaxReturn(['code' => 201, 'message' => $msg . '失败']);
            }
        }else{
            $this->ajaxReturn(['code' => 201, 'message' => '非法操作']);
        }
    }

}
