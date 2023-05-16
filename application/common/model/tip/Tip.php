<?php

namespace app\common\model\tip;

use app\common\model\dynamic\Dynamic;
use think\Db;
use think\Exception;
use think\Model;


class Tip extends Model
{

    

    

    // 表名
    protected $name = 'tip';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];



    /**获取类型列表
     * @return array
     */
    public function getTypeList()
    {
        return ["1" => __("Type 1"), "2" => __("Type 2"), "3" => __("Type 3")];
    }

    /**设置隐藏被举报动态的修改器
     * @param $value
     * @param $data
     * @return mixed
     */
    public function setHiddswitchAttr($value, $data)
    {
        Db::startTrans();
        try {
            if ($value==1){
                Dynamic::where("id",$data["parent_id"])
                    ->update(["hiddswitch"=>1]);
            }
            if ($value==0){
                Dynamic::where("id",$data["parent_id"])
                    ->update(["hiddswitch"=>0]);
            }
            Db::commit();
            return $value;
        }catch (Exception $e){
            Db::rollback();
            return $value;
        }

    }






}
