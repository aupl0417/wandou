<?php
namespace app\admin\model;

use think\Db;
use think\Model;

class Dictionary extends Model{


    public function getByTypeID($id){

        if(!is_numeric($id) || !$id){
            return false;
        }

        $result = Db::name('dictionary')->where(['TypeId' =>  $id])->select();
        if($result){
            $result = array_column($result, 'Value', 'Key');
            return $result;
        }
        return false;
    }

}