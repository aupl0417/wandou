<?php
/**
 * 运行日志
 * @param $data       数据 type : mixed
 * @param $controller 所在控制器
 * @param $action     方法
 * @param $params     参数 type : mixed
 * */
function logs_write($data, $controller, $action, $params){
    $fp = @fopen(ROOT_PATH . 'debug_' . date('Y-m-d') . ".txt", "a+");
    fwrite($fp, "运行：" . "----" . date('Y-m-d H:i:s') . "\n");
    fwrite($fp, "Data:" . (is_array($data) ? json_encode($data) : $data) . "\n");
    fwrite($fp, "Controller:" . $controller . " Action:" . $action . "\n");
    fwrite($fp, "Params:" . (is_array($params) ? json_encode($params) : $params) . "\n");
    fwrite($fp, "------------------------------------------------------------------------\n\n");
    fclose($fp);
}
