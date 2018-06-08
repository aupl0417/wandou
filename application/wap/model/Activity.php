<?php
namespace app\wap\model;

use think\Cache;
use think\Db;
use think\Model;
use think\Request;

class Activity extends Model
{
    protected $cache_time = 86400;//缓存时间
    protected $activityBeginTime;//活动结束时间

    /**
     * 游戏局数排名
     * @params $count 20 取数据量
     * @params $flash false 是否更新数据
     * @params $activityId  活动ID
     * @params $beginTime 开始时间
     * @params $endTime 结束时间
     * @return Array
     * */
    public function getPlayCountAll($count = 20, $flash = false, $activityId = '', $beginTime = '2017-11-01', $endTime = ''){
        $endTime    = $endTime ?: date('Y-m-d');
        $cacheKey   = md5('game_play_count_top_' . $activityId . $count);
//        Cache::set($cacheKey, null);
        $isCache = true;
        if($flash || !($data = Cache::get($cacheKey))){
            $sql  = "SELECT tem.playcount,tem.UserID,TUsers.UserName,TUsers.NickName FROM (SELECT top ({$count}) COUNT (Web_VChangeRecord.UserID) AS playcount,Web_VChangeRecord.UserID FROM
	            Web_VChangeRecord WHERE EndTime >= '" . $beginTime . "' and EndTime <= '" . $endTime . "' GROUP BY Web_VChangeRecord.UserID ORDER BY playcount DESC) as tem LEFT JOIN TUsers on tem.UserID=TUsers.UserID ORDER BY tem.playcount DESC";

            $data = Db::query($sql);
            $isCache = false;
            Cache::set($cacheKey, $data, $this->cache_time);
        }

        logs_write($data, 'Model/Activity', 'getPlayCountAll', ['count' => $count, 'flash' => $flash, 'activityId' => $activityId, 'beginTime' => $beginTime, 'endTime' => $endTime, 'isCache' => $isCache]);

        return $data;
    }

    /**
     * 豌豆数排名
     * @params $count 20 取数据量
     * @params $flash false 是否更新数据
     * @params $activityId  活动ID
     * @return Array
     * */
    public function getWalletCountAll($count = 20, $flash = false, $activityId = ''){
        $cacheKey   = md5('user_wallet_count_top_' . $activityId . $count);
        if($flash && $activityId){
            //如果活动结束时间小于当前更新时间，则不刷新缓存
            $activity = self::getActivityById($activityId, 'EndTime');
            if($activity && $activity['EndTime'] <= date('Y-m-d H:i:s')){
                $flash = false;
            }
        }
//        Cache::set($cacheKey, null);
        $isCache = true;
        if($flash || !($data = Cache::get($cacheKey))){
            $sql  = "SELECT TOP ({$count}) a.UserID,username,nickname,bankmoney,walletmoney,b.UserID,(b.BankMoney + b.WalletMoney) AS total FROM TUsers a LEFT JOIN TUserInfo b ON a.UserID = b.UserID ORDER BY total DESC";
            $data = Db::query($sql);
            $isCache = false;
            Cache::set($cacheKey, $data, $this->cache_time * 100);
        }

        logs_write($data, 'Model/Activity', 'getWalletCountAll', ['count' => $count, 'flash' => $flash, 'activityId' => $activityId, 'isCache' => $isCache]);
        return $data;
    }

    public function getActivityById($id, $field = '*'){
        if(!$id || !is_numeric($id)){
            return false;
        }
        $cacheKey = md5('activity_one_' . $id);
        Cache::set($cacheKey, null);
        if(!$data = Cache::get($cacheKey)){
            $data = Db::name('Activity')->where(['ID' => $id])->field($field)->find();
            Cache::set($cacheKey, $data, 3600);
        }
        return $data;
    }

    /**
     * 获取每天中在结束时间在当天内的活动(只考虑一天内最多只有惟一一个活动的情况)
     * @params $field 获取数据的字段
     * @return Array
     * */
    public function getActivityByEndTime($field = '*'){
        $date = date('Y-m-d');
        return Db::name('Activity')->where("Status=1 and (BeginTime <='" . $date . "' or EndTime <= '" . $date . "' )")->field($field)->order('ID Desc')->find();
    }

    /*
     * 获取所有活动（默认10条）
     * */
    public function getActivityAll($field = '*', $where = ['Status' => 1], $order = 'ID desc', $limit = '10'){
        return Db::name('Activity')->where($where)->field($field)->order($order)->limit($limit)->select();
    }

    public function getUserRechargeRecord($field = '*', $where = '', $order = 'PayID DESC'){
        return Db::table('Web_RMBCost')->where($where)->field($field)->order($order)->select();
    }

    public function getPreActivityId($id){
        if(!$id || !is_numeric($id)){
            return false;
        }

        return Db::name('Activity')->where(['ID' => ['lt', $id], 'status' => ['neq', 0]])->order('ID DESC')->value('ID');
    }

}