<?php
namespace app\wap\model;

use think\Db;
use think\Model;

class GoodsModel extends Model
{
    public function getGoodsById($id, $field = '*'){
        if(!is_numeric($id) || !$id){
            return false;
        }

        return Db::name('Goods')->field($field)->where(['ID' => $id])->find();
    }

    public function getGoodsByType($type, $field = '*', $isSale = 1){
        if(!in_array($type, [0, 1, 2])){
            return false;
        }

        return Db::name('Goods')->field($field)->where(['Type' => $type, 'Status' => $isSale, 'IsDelete' => 0])->select();
    }
}