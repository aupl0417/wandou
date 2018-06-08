<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\admin\validate;
use think\Validate;


class AddActivity extends Validate{

    protected $rule = [
        'Name'         => 'require',
        'WalletCounts' => 'requireIf:Type,1|number|checkWalletCounts',
        'PlayCounts'   => 'requireIf:Type,1|number|checkPlayCounts',
        'BeginTime'    => 'require|date',
        'EndTime'      => 'require|date|checkEndTime',
        'RechargeMoney'=> 'requireIf:Type,2|number|checkRechargeMoney',
        'Money'        => 'requireIf:Type,1|number|checkMoney',
        'BonusMax'         => 'require|number|checkMoney|checkBonusMax',
        'BonusMin'         => 'require|number|checkMoney|checkBonusMin',
        'LotteryCount'     => 'requireIf:Type,1|checkLotteryCount',
        'LotteryBeginTime' => 'require|date|checkLotteryBeginTime',
        'LotteryEndTime'   => 'require|date|checkLotteryEndTime',
        'Image'            => 'require',
    ];

    protected $message = [
        'Name.require'            => '请输入活动名称',
        'WalletCounts.requireIf'  => '请输入豌豆数排名数',
        'WalletCounts.number'     => '豌豆数排名数必须是数字',
        'PlayCounts.requireIf'    => '请输入局数排名数',
        'PlayCounts.number'       => '局数排名数必须是数字',
        'BeginTime.require'       => '请选择活动开始时间',
        'BeginTime.date'          => '活动开始时间格式非法',
        'EndTime.require'         => '请选择活动结束时间',
        'EndTime.date'            => '活动结束时间格式非法',
        'RechargeMoney.requireIf' => '请输入最低充值金额',
        'RechargeMoney.number'    => '最低充值金额必须是数字',
        'RechargeMoney.checkRechargeMoney'=> '最低充值金额必须大于1',
        'Money.requireIf'         => '请输入抽奖总金额',
        'Money.number'            => '抽奖总金额必须是数字',
        'Money.checkMoney'        => '抽奖总金额必须大于1元',
        'BonusMax.require'        => '请输入最大抽奖金额',
        'BonusMax.number'         => '最大抽奖金额必须是数字',
        'BonusMax.checkMoney'     => '最大抽奖金额必须大于1分',
        'BonusMin.require'        => '请输入最小抽奖金额',
        'BonusMin.number'         => '最小抽奖金额必须是数字',
        'BonusMin.checkMoney'         => '最大抽奖金额必须大于1分',
        'BonusMin.checkBonusMin'      => '最小抽奖金额必须是数字',
        'LotteryBeginTime.require'    => '请选择抽奖开始时间',
        'LotteryBeginTime.date'       => '抽奖开始时间格式非法',
        'LotteryEndTime.require'      => '请选择抽奖结束时间',
        'LotteryEndTime.date'         => '抽奖结束时间格式非法',
        'LotteryCount.requireIf'      => '请输入抽奖次数',
        'Image.require'               => '请上传图片',
    ];

    protected $scene = [
        'create' => ['Name', 'WalletCounts', 'PlayCounts', 'BeginTime', 'EndTime', 'RechargeMoney', 'Money', 'BonusMax', 'BonusMin', 'LotteryCount', 'LotteryBeginTime', 'LotteryEndTime', 'Image'],
        'edit'  =>  ['Name', 'WalletCounts', 'PlayCounts', 'BeginTime', 'EndTime', 'RechargeMoney', 'Money', 'BonusMax', 'BonusMin', 'LotteryCount', 'LotteryBeginTime', 'LotteryEndTime', 'Image'],
    ];

    public function checkMoney($money){
        if($_POST['Type'] == 1) {
            if (!$money || !is_numeric($money) || $money < 1) {
                return false;
            }
        }
        return true;
    }

    public function checkRechargeMoney($money){
        if($_POST['Type'] == 2) {
            if (!$money || !is_numeric($money) || $money < 1) {
                return false;
            }
        }

        return true;
    }

    public function checkBonusMax($money){
        if($_POST['Type'] == 1){
            $average = $_POST['Money'] / $_POST['LotteryCount'] * 100;
            if($average > $money){
                return '最大抽奖金额必须大于平均抽奖金额' . $average . '分';
            }

            if($_POST['Money'] * 100 <= $_POST['BonusMax']){
                return '最大抽奖金额不能超过抽奖总金额';
            }
        }

        return true;
    }

    public function checkBonusMin($money){
        if($_POST['Type'] == 1){
            $average = $_POST['Money'] / $_POST['LotteryCount'] * 100;
            if($average < $money){
                return '最小抽奖金额不能大于平均抽奖金额' . $average . '分';
            }

            if($_POST['Money'] * 100 <= $_POST['BonusMax']){
                return '最小抽奖金额不能超过抽奖总金额';
            }
        }

        if($_POST['BonusMax'] <= $_POST['BonusMin']){
            return '最小抽奖金额不能超过最大抽奖金额';
        }

        return true;
    }

    public function checkCount($count){
        $count = intval($count);
        if($_POST['Type'] == 1){
            if(!$count){
                return false;
            }
        }

        return true;
    }

    public function checkLotteryCount($count){
        $count = intval($count);
        if($_POST['Type'] == 1){
            if(!$count){
                return '请填写豌豆排名名次数或局数排名名次数';
            }
            if(intval($_POST['WalletCounts']) + intval($_POST['PlayCounts']) != $count){
                return '输入排名数错误';
            }
        }

        return true;
    }

    public function checkEndTime($time){
        if($_POST['EndTime'] < $_POST['BeginTime']){
            return '活动结束时间时间必须大于活动开始时间';
        }
        return true;
    }

    public function checkLotteryBeginTime($time){
        if($_POST['EndTime'] > $time){
            return '抽奖开始时间必须大于活动结束时间';
        }
        return true;
    }

    public function checkLotteryEndTime($time){
        if($_POST['LotteryBeginTime'] > $time){
            return '抽奖结束时间必须大于抽奖开始时间';
        }
        return true;
    }

    public function checkWalletCounts($walletCount){
        if($_POST['Type'] == 1){
            if(!is_numeric($walletCount)){
                return '豌豆排名名次数必须是数字';
            }
            if(empty($_POST['WalletCounts']) && empty($_POST['PlayCounts'])){
                return '豌豆数排名数不能为空';
            }
        }
        return true;
    }

    public function checkPlayCounts($playCount){
        if($_POST['Type'] == 1){
            if(!is_numeric($playCount)){
                return '局数排名名次数必须是数字';
            }
            if(empty($_POST['WalletCounts']) && empty($_POST['PlayCounts'])){
                return '局数排名数不能为空';
            }
        }
        return true;
    }

}