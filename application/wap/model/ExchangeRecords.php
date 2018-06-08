<?php
namespace app\wap\model;

use think\Db;
use think\Model;

class ExchangeRecords extends Model
{

    public function getDataByUserId($userId, $field = '*'){
        if(!is_numeric($userId) || !$userId){
            return false;
        }

        return Db::name('exchange_records')->field($field)->where(['UserID' => $userId])->select();
    }

    public function getAllDataByUserId($userId, $field = '*', $order = 'ID desc', $limit = ''){
        if(!is_numeric($userId) || !$userId){
            return false;
        }

        $object =  Db::name('exchange_records er')
                ->field($field)->where(['UserID' => $userId])
                ->join('Goods g', 'g.ID=er.GoodsId');
        if($order){
            $object->order($order);
        }

        if($limit){
            $object->limit($limit);
        }
        return $result = $object->select();
    }

    public function getDataById($id, $field = '*'){
        if(!is_numeric($id) || !$id){
            return false;
        }
        return Db::name('exchange_records')->where(['ID' => $id])->field($field)->find();
    }
}