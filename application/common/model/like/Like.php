<?php

namespace app\common\model\like;

use app\common\model\Comment\Comment;
use app\common\model\Dynamic\Dynamic;
use app\common\model\information\Information;
use think\Db;
use think\Exception;
use think\Model;


class Like extends Model
{

    // 表名
    protected $name = 'like';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    /**增加点赞数
     * @param $user_id
     * @param $parent_id
     * @param $like_type
     * @return bool
     */
    public function incLikes($user_id='', $parent_id="", $like_type)
    {
        if (empty($user_id)){
            return $this->error("用户id参数错误",[],400,"json");
        }
        if (empty($parent_id)){
            return $this->error("id参数错误",[],400,"json");
        }
        $like=new self;
        if ($like_type==1){
            Db::startTrans();
            try {
                $count=$like::where('user_id',"=",$user_id)
                    ->where("parent_id",'=',$parent_id)
                    ->where("like_type",'=',$like_type)
                    ->count();
                if ($count){
                    return $this->error("请勿重复点赞",[],204,"json");
                }else{
                    Information::where("id","=",$parent_id)
                        ->setInc('likes');
                }
                $data=[
                    "user_id"=>$user_id,
                    "parent_id"=>$parent_id,
                    "like_type"=>$like_type,
                ];
                $like::create($data);
                Db::commit();
                return true;
            }catch (Exception $e){
                Db::rollback();
                return false;
            }

        }else if ($like_type==2){
            Db::startTrans();
            try {
                $count=$like::where('user_id',"=",$user_id)
                    ->where("parent_id",'=',$parent_id)
                    ->where("like_type",'=',$like_type)
                    ->count();
                if ($count){
                    return $this->error("请勿重复点赞",[],204,"json");
                }else{
                    Dynamic::where("id","=",$parent_id)
                        ->setInc('likes');
                }

                $data=[
                    "user_id"=>$user_id,
                    "parent_id"=>$parent_id,
                    "like_type"=>$like_type,
                ];
                $like::create($data);
                Db::commit();
                return true;
            }catch (Exception $e){
                Db::rollback();
                return false;
            }
        }else if ($like_type==3){
            Db::startTrans();
            try {
                $count=$like::where('user_id',"=",$user_id)
                    ->where("parent_id",'=',$parent_id)
                    ->where("like_type",'=',$like_type)
                    ->count();
                if ($count){
                    return $this->error("请勿重复点赞",[],204,"json");
                }else{
                    Comment::where("id","=",$parent_id)
                        ->setInc('likes');
                }
                $data=[
                    "user_id"=>$user_id,
                    "parent_id"=>$parent_id,
                    "like_type"=>$like_type,
                ];
                $like::create($data);
                Db::commit();
                return true;
            }catch (Exception $e){
                Db::rollback();
                return false;
            }
        }else{
            return false;
        }

    }


}
