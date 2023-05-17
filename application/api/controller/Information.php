<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Comment\Comment;
use app\common\model\like\Like;
use think\Db;
use think\Exception;
use think\Request;
use think\response\Json;

/**资讯api接口
 *class Information
 */
class Information extends Api{

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = ['*'];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = ['*'];


    /**获取首页资讯
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShow()
    {
        $list=\app\common\model\Information::where("showswitch",'=',1)
            ->field("id,title,comments,createtime")
            ->select();
        return $this->success('数据请求成功',$list,200,"json");

    }


    /**获取资讯列表
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInformationList()
    {
        $list=\app\common\model\Information::field("id,title,comments,createtime")
            ->select();
        return $this->success('数据请求成功',$list,200,"json");
    }

    /**获取资讯详情
     * @param $id
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInformationDetail($id)
    {
        if (!is_numeric($id)){
            return $this->error("数据请求格式错误",[],400,"json");
        }
        //获取资讯列表
        $list=\app\common\model\information\Information::where("id",'=',$id)
            ->field("title,content,images,author,browses,likes")
            ->find();
        //获取当前资讯的评论
        $comment_list=Comment::where("type",1)
            ->where("parent_id",$id)
            ->alias(["ln_user"=>"user","ln_dynamic"=>"dynamic","ln_comment"=>"comment"])
            ->join("ln_user","comment.user_id=user.id")
            ->field("comment.id,user_id,content,comments,likes,type,parent_id,reply_id,comment.createtime,nickname,avatar")
            ->select();
        $count=Comment::getReplyCount($comment_list);
        return $this->success('数据请求成功',["article"=>$list,"comments"=>$count],200,"json");


    }


    /**评论资讯
     * @param Request $request
     * @return void|null
     */
    public function inforamtionComment(Request $request)
    {

        $list=$request->post();
        $parent_id=$list["id"];//资讯id
        $user_id=$list["user_id"];//评论的用户的id
        $content=$list["content"];
        $reply_id=$list["reply_id"];//回复的评论的id
        if (empty($list) || empty($parent_id) || empty($user_id) || empty($content)){//检查id是否为空
            return $this->error("数据请求格式错误",[],400,"json");
        }

        $data=[
            "parent_id"=>$parent_id,
            "user_id"=>$user_id,
            "content"=>$content,
            "comments"=>0,
            "likes"=>0,
            "type"=>1,
            "reply_id"=>$reply_id,
        ];
        Db::startTrans();
        try {
            Comment::create($data);//创建评论
            if (!empty($reply_id)){//检查是否为回复如果是则增加对应评论的评论数
                Comment::where("id",$reply_id)
                    ->setInc("comments");
            }
            //增加资讯的评论数
            \app\common\model\information\Information::where("id",$parent_id)
                ->setInc("comments");
            Db::commit();
            return $this->success("评论成功",$data,200,"json");
        }catch (Exception $e){
            Db::rollback();
            return $this->error($e->getMessage(),[],400,"json");
        }
        
    }

    /**点赞
     * @param Request $request($parent_id,$user_id,$like_type)
     * @return void|null
     */
    public function incLikes(Request $request)
    {
        $list=$request->post();
        $parent_id=$list["id"];//点赞目标的id
        $user_id=$list["user_id"];//点赞的用户id
        $like_type=$list["like_type"];//点赞类型
        if(empty($parent_id) || empty($user_id)){
            return $this->error("数据参数缺失",[],400,"json");
        }
        $likes=Like::incLikes($user_id,$parent_id,$like_type);
        if ($likes){
            return $this->success("点赞成功",200,"json");
        }

    }

}