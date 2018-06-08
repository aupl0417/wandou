<?php
namespace app\wap\model;

use think\Db;
use think\Model;
use think\Request;
use think\Cache;

class Area extends Model
{
    /**
     * 根据父ID获取所属地区信息
     */
    public function getAreaByPid($pid){

        if (empty($pid)){
            [['value'=>'0','label'=>'选择省']];
        }
        $cacheId ='area_'.$pid;
        if (Cache::has($cacheId)){
            return Cache::get($cacheId);
        }else{
            $data = Db::name('Area')->field('a_id,a_name')->where(['a_parenId'=>$pid,'a_status'=>0])->select();
            if (!empty($data)){
                $items =[];
                foreach ($data as $item){
                    $items[]=['value'=>$item['a_id'],'label'=>$item['a_name']];
                }
                Cache::set($cacheId, $items, 3600);
                return $items;
            }
            /*else{
                return [['value'=>'0','label'=>'未选择']];
            }*/
        }
    }

    public function getAreaById($id, $field = '*'){
        if(!is_numeric($id) || !$id){
            return false;
        }
        return Db::name('Area')->where(['a_id' => $id])->field($field)->find();
    }

}