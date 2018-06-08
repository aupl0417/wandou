<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
/*
 * 抽奖管理
 * */
class Lottery extends Base{

    public function index(){
        $sql = 'Select lr.ID,t.UserName,lr.CreateTime,lr.ReceiveTime,a.Name,lr.Money,lr.Status,a.LotteryBeginTime,a.LotteryEndTime FROM Gwd_lottery_records lr LEFT JOIN TUsers t ON lr.UserID=t.UserID LEFT JOIN Gwd_Activity a on a.ID=lr.ActivityID ORDER BY lr.ID DESC';
        $data = Db::query($sql);

        $this->assign('data', $data);
        $this->assign('title', '抽奖管理-豌豆后台管理');
        $this->assign('nav', '抽奖管理');
        return $this->fetch();
    }

    public function add(){

    }

    public function addSave(){

    }

    public function edit(){

    }

    public function editSave(){

    }

    /*
     * 处理发奖
     * */
    public function deal(){
        $id = input('id', 0, 'intval');
        if(Db::name('lottery_records')->where(['ID' => $id, 'Status' => 1])->count()){
            $res = Db::name('lottery_records')->where(['ID' => $id])->update(['Status' => 2, 'UpdateTime' => date('Y-m-d H:i:s')]);
            $res === false && $this->ajaxReturn(['code' => 201, 'message' => '操作失败']);
            $this->ajaxReturn(['code' => 200, 'message' => '操作成功']);
        }else{
            $this->ajaxReturn(['code' => 201, 'message' => '数据不存在']);
        }
    }

    /*
     * 处理活动状态
     * */
    public function deal_bac(){
        $id   = input('id', 0, 'intval');
        $type = input('type', '', 'trim');
        (!$type || !$id || !in_array($type, ['begin', 'end'])) && $this->ajaxReturn(['code' => 201, 'message' => '参数非法']);

        $status = $type == 'begin' ? 1 : 2;

        if(Db::name('lottery_records')->where(['ID' => $id])->count()){
            $res = Db::name('lottery_records')->where(['ID' => $id])->update(['Status' => $status, 'UpdateTime' => date('Y-m-d H:i:s')]);
            $res === false && $this->ajaxReturn(['code' => 201, 'message' => '操作失败']);
            $this->ajaxReturn(['code' => 200, 'message' => '操作成功']);
        }else{
            $this->ajaxReturn(['code' => 201, 'message' => '数据不存在']);
        }
    }
}
