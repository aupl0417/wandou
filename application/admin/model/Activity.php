<?php
namespace app\admin\model;

use think\Db;
use think\Model;

class Activity extends Model{


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

    public function saveData($data){
        if(isset($data['ID']) && !empty($data['ID'])){
            $where['ID'] = $data['ID'];
            unset($data['ID']);
        }
        if(!$where){
            return false;
        }
        $res = Db::name('Activity')->where($where)->update($data);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    public function createData($data){
        if(!$data || !is_array($data)){
            return false;
        }

        return Db::name('Activity')->insert($data);
    }

    public function getActivityDataAll($field = '*', $order = 'ID desc'){
        $data =  Db::name('Activity')->field($field)->order($order)->select();
        if($data){
            foreach ($data as $key => &$val){
                foreach ($val as $k => $v){
                    if(strpos($k,'Time') !== false){
                        $val[$k] = date('Y-m-d H:i:s', strtotime($val[$k]));
                    }
                }
            }
        }
        return $data;
    }

    public function getActivityById($id, $field = '*'){
        if(!$id || !is_numeric($id)){
            return false;
        }
        return Db::name('Activity')->where(['ID' => $id])->field($field)->find();
    }

}