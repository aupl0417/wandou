<?php
namespace app\admin\model;

use think\Model;
use think\Db;

class User extends Model
{
    public function getUserByUserId($id, $field = '*'){
        if(!is_numeric($id) || !$id){
            return false;
        }

        return Db::table('TUsers')->field($field)->where(['UserID' => $id])->find();
    }
}