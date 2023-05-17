<?php

namespace app\common\model\Comment;

use think\Exception;
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

    //设置返回结果类型
    protected $resultSetType = 'collection';
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


    /**获取评论回复数量
     * @param $list
     * @return mixed
     * @throws \think\Exception
     */
    public function getReplyCount($list)
    {
        $com=new self();

        foreach ($list as $item){
            $num=$com::where("type",3)
                ->where("parent_id",$item["id"])
                ->count();
            //查找当前元素在数组中的位置
            $len=array_search($item,$list);
            //将回复数量插入到数组尾部
            $list[$len]["reply_num"]=$num>0?"共".$num."条回复":"";

        }

        return $list;

    }
    public function getReplyNickname($list)
    {
        $com=new self();
        $form_nick='';
        try {
            foreach ($list as  &$item){
                $from_nick=\app\common\model\Comment\Comment::where("type",3)
                    ->where("parent_id",$item["parent_id"])
                    ->alias(["ln_user"=>"user","ln_comment"=>"comment"])
                    ->join("ln_user","comment.user_id=user.id")
                    ->field("comment.id,nickname")
                    ->select()
                    ->toArray();
                $to_nick=\app\common\model\Comment\Comment::where("type",3)
                    ->where("parent_id",$item["parent_id"])
                    ->alias(["ln_user"=>"user","ln_comment"=>"comment"])
                    ->join("ln_user","comment.reply_id=user.id")
                    ->field("comment.id,nickname")
                    ->select()
                    ->toArray();
                foreach ($from_nick as $item1){
                    if ($item1["id"]==$item["id"]){
                        $item["from_nickname"]=empty($item1["nickname"])?"":$item1["nickname"];
                    }
                }
                foreach ($to_nick as $item2){
                    if ($item2["id"]==$item["id"]){
                        $item["to_nickname"]=empty($item2["nickname"])?"":$item2["nickname"];
                    }
                }

            }

        }catch (Exception $e){
            return $this->error($e->getMessage(),[],400,"json");
        }

        return $list;
    }


}
