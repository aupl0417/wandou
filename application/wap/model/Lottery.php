<?php
namespace app\wap\model;

use think\Db;
use think\Model;

class Lottery extends Model
{

    /**
     * 计算单个用户在某个活动的可抢红包次数
     * @params $userId 用户ID
     * @params $id     活动ID
     * @return int
     * */
    public function getUserLotteryCount($userId, $id){
        $count = 0;
        if(!$userId || !$id || !is_numeric($userId) || !is_numeric($id)){
            return $count;
        }

        return Db::name('lottery_records')->where(array('UserID' => $userId, 'ActivityId' => $id, 'Status' => 0))->count();
    }

    /**
     * 获取用户一条可抢红包记录（用户可能有多次机会）
     * @params $userId 用户ID
     * @params $id     活动ID
     * @return Array
     * */
    public function getOneLotteryData($userId, $id, $field = '*'){
        $data = array();
        if(!$userId || !$id || !is_numeric($userId) || !is_numeric($id)){
            return $data;
        }
        return Db::name('lottery_records')->where(['UserID' => $userId, 'ActivityId' => $id, 'Status' => 0])->field($field)->find();
    }
}