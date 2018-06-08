<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
/*
 * 活动管理
 * */
class Activity extends Base{

    public function index(){
        $activityModel = new \app\admin\model\Activity();
        $data = $activityModel->getActivityDataAll();

        $this->assign('data', $data);
        $this->assign('title', '活动管理-豌豆后台管理');
        $this->assign('nav', '活动管理');
        return $this->fetch();
    }

    public function create(){
        $this->assign('title', '添加活动-豌豆后台管理');
        $this->assign('nav', '添加活动');
        return $this->fetch();
    }

    public function createSave(){
        if(Request::instance()->isAjax()){
            $data = input('post.');
            $result = $this->validate($data, 'AddActivity.create');
            if($result !== true){
                $this->ajaxReturn(['code' => 201,  'message' => $result]);
            }

            $activityModel = new \app\admin\model\Activity();
            $res = $activityModel->createData($data);
            !$res && $this->ajaxReturn(['code' => 201,  'message' => '添加活动失败']);
            $this->ajaxReturn(['code' => 200,  'message' => '添加活动成功']);
        }
    }

    public function edit(){
        $id = input('id', 0, 'intval');
        $activeModel = new \app\admin\model\Activity();
        $data = $activeModel->getActivityById($id);

        $this->assign('title', '编辑活动-豌豆后台管理');
        $this->assign('nav', '编辑活动');
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function editSave(){
        if(Request::instance()->isAjax()){
            $data = input('post.');
            $result = $this->validate($data, 'AddActivity.edit');
            if($result !== true){
                $this->ajaxReturn(['code' => 201,  'message' => $result]);
            }
            $data['UpdateTime'] = date('Y-m-d H:i:s');

            $activityModel = new \app\admin\model\Activity();
            $res           = $activityModel->saveData($data);
            $res === false && $this->ajaxReturn(['code' => 201,  'message' => '编辑活动失败']);
            $this->ajaxReturn(['code' => 200,  'message' => '编辑活动成功']);
        }
    }

    public function view(){
        $id = input('id', 0, 'intval');
        $activeModel = new \app\admin\model\Activity();
        $data = $activeModel->getActivityById($id);

        $this->assign('title', '查看活动-豌豆后台管理');
        $this->assign('nav', '查看活动');
        $this->assign('data', $data);
        return $this->fetch();
    }

    /*
     * 处理活动状态
     * */
    public function deal(){
        $id   = input('id', 0, 'intval');
        $type = input('type', '', 'trim');
        (!$type || !$id || !in_array($type, ['begin', 'end'])) && $this->ajaxReturn(['code' => 201, 'message' => '参数非法']);

        $status = $type == 'begin' ? 1 : 2;

        if(Db::name('Activity')->where(['ID' => $id])->count()){
            $res = Db::name('Activity')->where(['ID' => $id])->update(['Status' => $status]);
            $res === false && $this->ajaxReturn(['code' => 201, 'message' => '操作失败']);
            $this->ajaxReturn(['code' => 200, 'message' => '操作成功']);
        }else{
            $this->ajaxReturn(['code' => 201, 'message' => '数据不存在']);
        }
    }


}
