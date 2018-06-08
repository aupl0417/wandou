<?php
namespace app\wap\model;

use think\Model;
use think\Db;

class UserInfo extends Model
{

    protected $table = 'TUserInfo';

    public function getUserByUserId($id, $field = '*'){
        if(!is_numeric($id) || !$id){
            return false;
        }

        return Db::table($this->table)->field($field)->where(['UserID' => $id])->find();
    }

}