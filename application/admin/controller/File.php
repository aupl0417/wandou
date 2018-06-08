<?php
namespace app\admin\controller;

use think\Config;

class File extends Base {

    public function upload(){
        $rule = [
            'size'=>'4096000',
            'ext'=>'jpg,png,gif'
        ];

        $file = request()->file('logo');
        $info = $file->rule($rule)->move(Config::get('upload_folder'));

        if ($info){
            $filename = $info->getSaveName();
            return $this->apiReturn(200, ['filename' => '/uploads/' . $filename, 'message' => '上传成功']);
        }else{
            return $this->apiReturn(201, ['filename' => '', 'message' => '上传失败']);
        }
    }

}