<?php
namespace app\wap\controller;

use think\Cache;
use think\Db;
use think\Request;
use think\response\Json;

class Lottery extends Home{

    protected $activityService;
    protected $cache_time = 86400;
    protected $user_lottery_count;

    public function __construct(Request $request = null){
        parent::__construct($request);
        $this->activityService    = model('Activity', 'service');
        $this->user_lottery_count = md5('user_lottery_count_' . $this->userId);
    }

    /**
     * 抢红包页面
     * */
    public function index(){
        $id   = input('id', 1, 'intval');
        $activity = model('Activity')->getActivityById($id, 'ID,BeginTime,EndTime,LotteryCount,Type,WalletCounts,PlayCounts');
        if(!$activity){
            $this->redirect(url('Activity/lottery'));
        }

        $data = $this->activityService->getActivityUserId(false, $activity);
        $lottery_count = model('Lottery')->getUserLotteryCount($this->userId, $id);

        $this->assign('active', date('Y-m-d H') > $activity['BeginTime'] ? 1 : 0);//抽奖时间未到，按钮不可点击
        $this->assign('lottery_count', $lottery_count);
        $this->assign('total_lottery_count', $activity['LotteryCount']);
        $this->assign('id', $id);
        return $this->fetch();
    }

    /**
     * 抢红包处理
     * 由于是只有榜单上的人才有抢红包的机会，所以不会存在抢不到红包的机会
     * 每当一次触发时，红包数据集中先去除最前面一个数据，如果中间处理失败，则将数据返回给红包数据集
     * @return Json
     * */
    public function lotteryDraw(){
        $id   = input('id', 0, 'intval');
        $activity = model('Activity')->getActivityById($id, 'ID,BeginTime,EndTime,LotteryCount,LotteryBeginTime,LotteryEndTime');
        if(!$activity){
            $this->apiReturn(201, '活动不存在');
        }

        (date('Y-m-d H:i:s') < $activity['LotteryBeginTime'] || date('Y-m-d H:i:s') > $activity['LotteryEndTime']) && $this->apiReturn(201, "开奖时间：{$activity['LotteryBeginTime']}-{$activity['LotteryEndTime']}");

        $lottery_count = model('Lottery')->getUserLotteryCount($this->userId, $id);
        $lottery_count == 0 && $this->apiReturn(201, '你没有获得抽奖资格，继续加油哦！');

        $lottery = model('Lottery')->getOneLotteryData($this->userId, $id, 'ID,Money');
        $res     = Db::name('lottery_records')->where(['ID' => $lottery['ID']])->update(['Status' => 1, 'ReceiveTime' => date('Y-m-d H:i:s')]);
        $res     === false && $this->apiReturn(201, '抽奖失败');
        $this->apiReturn(200, ['bonus' => floatval($lottery['Money']), 'lotteryCount' => $lottery_count - 1]);
    }

    /**
     * 判定是否有抢红包机会,如有,则跳转到抢红包页面
     * */
    public function getChance(){
        if(Request::instance()->isAjax()){
            $id   = input('id', 0, 'intval');
            $activity = model('Activity')->getActivityById($id, 'ID,BeginTime,EndTime,LotteryCount,LotteryBeginTime,LotteryEndTime');
            if(!$activity){
                $this->apiReturn(201, '活动不存在');
            }

            (date('Y-m-d H:i:s') < $activity['LotteryBeginTime'] || date('Y-m-d H:i:s') > $activity['LotteryEndTime']) && $this->apiReturn(201, "开奖时间：{$activity['LotteryBeginTime']}-{$activity['LotteryEndTime']}");

            $lottery_count = model('Lottery')->getUserLotteryCount($this->userId, $id);
            $lottery_count == 0 && $this->apiReturn(201, '您无抽奖资格或资格已用完,继续加油哦！');

            $this->apiReturn(200);
        }else{
            $this->apiReturn(201, '非法操作');
        }
    }





}
