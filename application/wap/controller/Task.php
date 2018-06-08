<?php
/*
 * 定时任务
 * */
namespace app\wap\controller;

use think\Cache;
use think\Db;
use think\Request;
use think\response\Json;

class Task extends Home{

    protected $bonus_total = 10000;//红包总金额 100元
    protected $bonus_count = 40;   //红包总数（默认40个）
    protected $bonus_max   = 888;  //每个小红包的最大金额 8.88元 必需大于平均值（$this->bonus_total / $this->bonus_count）
    protected $bonus_min   = 188;  //每个小红包的最小金额 1.88元

    /**
     * 生成红包数据集并保存到数据库(一天只有一个抽奖活动的情况)
     * @return Json
     * */
    public function index(){
        //当天内的抽奖活动
        $where = [
            'LotteryBeginTime' => ['between', [date('Y-m-d'), date('Y-m-d') . ' 23:59:59']],
            'Status'    => 1,
            'Type'      => 1
        ];
        $activity = Db::name('Activity')->where($where)->field('ID,LotteryCount,Money,BonusMax,BonusMin,EndTime')->find();
        !$activity && $this->ajaxReturn(['code' => 201, 'msg' => date('Y年m月d') . '日无抽奖活动']);
        if(Db::name('lottery_records')->where(['ActivityID' => $activity['ID']])->count()){
            $this->ajaxReturn(['code' => 201, 'msg' => '该活动的红包数据已初始化']);
        }

        intval($activity['Money'])    && $this->bonus_total = $activity['Money'] * 100;
        intval($activity['BonusMin']) && $this->bonus_min   = $activity['BonusMin'];
        $activity['LotteryCount']     && $this->bonus_count = $activity['LotteryCount'];
        $activity['BonusMax']         && $this->bonus_max   = $activity['BonusMax'];

        if($this->bonus_max <= $this->bonus_total / $this->bonus_count){//如果设置的最大抽奖金额小于平均数，则最大抽奖金额 = 平均数 + 最小金额
            $this->bonus_max = $this->bonus_total / $this->bonus_count + $this->bonus_min;
        }

        $activityData  = model('Activity', 'service')->getActivityUserId(false, $activity);
        $data          = $activityData['play'];
        $wallet        = $activityData['wallet'];
        $userIds       = array_merge($data, $wallet);

//        $this->bonus_count = count($data) + count($wallet);
        $bonus    = model('RedBonus', 'service')->getBonus($this->bonus_total, $this->bonus_count, $this->bonus_max, $this->bonus_min);
//            $bonus = model('RedBonus', 'Service')->randBonus($this->bonus_total, $this->bonus_count, $this->bonus_min, $this->bonus_max, 1);
        shuffle($bonus);
        $lotteryPool = array();

        foreach($bonus as $key => $val){
            $lotteryPool[$key] = [
                'UserID'     => isset($userIds[$key]) ? $userIds[$key] : '',
                'Money'      => $val / 100,
                'CreateTime' => date('Y-m-d H:i:s'),
                'ActivityID' => $activity['ID'],
            ];
        }
        logs_write('初始化红包数据', Request::instance()->controller(), Request::instance()->action(), $activity);
        $res = Db::name('lottery_records')->insertAll($lotteryPool);
        unset($lotteryPool);
        !$res && $this->ajaxReturn(['code' => 201, 'msg' => '初始化红包数据失败']);
        $this->ajaxReturn(['bonus' => $bonus, 'play' => $data, 'wallet' => $wallet]);
    }

    /**
     * 更新榜单数据
     * @return Json
     * */
    public function up(){
        $res = model('Activity', 'service')->getActivityUserId(true);
        logs_write('更新榜单数据', Request::instance()->controller(), Request::instance()->action(), ['flash' => true]);
        !$res && $this->apiReturn(201);
        $this->apiReturn(200, $res);
    }

    /**
     * 12月期间的充值活动计算用户抽奖机会次数并随机分配单次抽奖金额
     * @return Json
     * */
    public function recharge(){
        if(date('Y-m-d H:i:s') > '2018-01-01 00:00:00' && date('Y-m-d H:i:s') <= '2018-01-01 00:10:00'){
            $cacheKey = md5('recharge_2017-12-01 00:05:00');
            if(!$data = Cache::get($cacheKey)){
                $res = model('Activity', 'service')->rechargeChange(19, 199);
                !$res && $this->ajaxReturn(['code' => 0, 'msg' => '操作失败']);
                Cache::set($cacheKey, 1, 1200);//保证缓存时间充足
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            }else{
                $this->ajaxReturn(['code' => 0, 'msg' => '不能重复操作']);
            }
        }else{
            $this->ajaxReturn(['code' => 0, 'msg' => '时间非法']);
        }
    }


}
