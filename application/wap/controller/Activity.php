<?php
namespace app\wap\controller;

use think\Cache;
use think\Db;
use think\Request;

class Activity extends Home {
    protected $cache_time = 86400;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    /**
     * 活动首页
     * */
    public function index(){
        $id = input('id', 29, 'intval');
        logs_write('活动首页', Request::instance()->controller(), Request::instance()->action(), $id);
        $activityService = model('Activity', 'service');
        $activity        = model('Activity')->getActivityById($id, 'ID,BeginTime,EndTime,Template,Type,WalletCounts,PlayCounts');
        $data            = $activityService->getActivityDataAll($activity);
        $data['play']    = $activityService->hidArrayString($data['play'], 'UserName');
        $data['wallet']  = $activityService->hidArrayString($data['wallet'], 'username');

        $this->assign('data',   $data['play']);
        $this->assign('wallet', $data['wallet']);
        $this->assign('startTime', date('Y-m-d', strtotime($activity['BeginTime'])));
        $this->assign('endTime', date('Y-m-d', strtotime('-1 days')));
        $this->assign('date',   date('m月d日 H:i:s', strtotime($activity['EndTime'])));
        $this->assign('preId', model('Activity')->getPreActivityId($id));
        return $this->fetch($activity['Template'] ?: '');
    }

    /**
     * 抢红包页面
     * */
    public function lottery(){
        $activityModel = new \app\wap\model\Activity();
        $data = $activityModel->getActivityAll('*', ['Status' => 1]);

        $this->assign('data', $data);
        $this->assign('now_time', date('Y-m-d H:i:s'));
        return $this->fetch();
    }

}
