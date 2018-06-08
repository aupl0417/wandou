<?php
namespace app\admin\model;

use think\Db;
use think\Model;

class ExchangeRecords extends Model{


    public function getByID($id,  $field = '*', $isJoin = false){

        if(!is_numeric($id) || !$id){
            return false;
        }

        $object = Db::name('exchange_records er')->where(['er.ID' =>  $id])->field($field);
        if($isJoin){
            $object->join('Goods g', 'g.ID=er.GoodsId');
        }
        return $object->find();
    }

    /*
     * 编辑记录
     * @params $data  type array        编辑数据
     * @params $where type string/array 保存记录条件
     * return bool
     * */
    public function saveData($data, $where = ''){
        if(isset($data['ID']) && !empty($data['ID'])){
            $where['ID'] = $data['ID'];
            unset($data['ID']);
        }
        if(!$where){
            return false;
        }
        $res = Db::name('exchange_records')->where($where)->update($data);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }


    /*
     *查询除用户表数据外所有兑换记录数据
     * @params $field  type string 查询字段
     * @params $where  type string 查询条件
     * @params $order  type string 排序
     * @params $isJoin type bool   是否有表连接
     * return  array / bool
     * * */
    public function getAllData($field = '*', $where = '', $isJoin = false, $order = 'ID desc', $limit = ''){
        $object = Db::name('exchange_records er')->field($field);
        if($where){
            $object->where($where);
        }
        if($order){
            $object->order($order);
        }

        if($isJoin){
            $object->join('Goods g', 'g.ID=er.GoodsId');
        }

        if($limit){
            $object->limit($limit);
        }

        return $object->select();
    }

    /*
     * @params $field type string 查询字段
     * @params $where type string 查询条件
     * @params $order type string 排序
     * return  array / bool
     * */
    public function getAllDataBySql($field = '*', $where = '1=1', $order = 'ID desc'){
        $where = ' WHERE ' . $where;
        $sql = "Select {$field}  FROM Gwd_exchange_records er LEFT JOIN Gwd_Goods g ON g.ID=er.GoodsId LEFT JOIN TUsers tu on tu.UserID=er.UserID {$where} Order By {$order}";
        return Db::query($sql);
    }

}