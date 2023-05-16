<?php

namespace app\common\model\Comment;

use think\Model;


class Comment extends Model
{


    // 表名
    protected $name = 'comment';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
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


    public function getReplyCount($list)
    {
        $com=new self();
        $count=[];

        foreach ($list as $item){

            $num=$com::where("type",3)
                ->where("parent_id",$item["id"])
                ->count();
            

        }
        return $count;

    }


}
