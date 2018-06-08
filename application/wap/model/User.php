<?php
namespace app\wap\model;

use think\Db;
use think\Model;

class User extends Model
{
    public function getUserByUserId($id, $field = '*'){
        if(!is_numeric($id) || !$id){
            return false;
        }

        return Db::table('TUsers')->field($field)->where(['UserID' => $id])->find();
    }

    public function getUserByErpUserId($userId, $field = '*'){
        if(strlen($userId) != 32 || !$userId){
            return false;
        }

        return Db::table('TUsers')->field($field)->where(['ErpUserId' => $userId])->find();
    }

    public function getUserByUserName($userName, $field = '*'){
        if(!$userName){
            return false;
        }

        return Db::table('TUsers')->field($field)->where(['UserName' => $userName])->find();
    }
}