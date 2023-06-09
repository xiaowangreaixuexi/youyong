<?php
namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\Comment\Comment;
use app\common\model\like\Like;
use app\common\model\tip\Tip;
use think\Db;
use think\Exception;
use think\Request;

/** 动态api接口
 * class Dynamic
 */
class Dynamic extends Api{

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

    /**获取首页动态
     * @return null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShow()
    {
        $list=\app\common\model\dynamic\Dynamic::where("showswitch",1)//showswitch: 0不是首页展示,1为首页展示
            ->where("hiddswitch",0)
            ->alias(["ln_user"=>"user","ln_dynamic"=>"dynamic"])
            ->join("ln_user","dynamic.user_id=user.id")
            ->field("dynamic.id,user_id,content,images,comments,likes,showswitch,dynamic.createtime,nickname,avatar")
            ->select();
        if ($list){
            return $this->success("请求成功",$list,200,"json");
        }else{
            return $this->error("数据请求失败",[],400,"json");
        }

    }

    /**获取动态列表
     * @return null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDynamicList(Request $request)
    {
        $list=$request->post();
        if (empty($list["page"]))
            return $this->error("分页参数不能为空",[],400,"json");
        if (!is_numeric($list["page"]))
            return $this->error("分页参数错误",[],400,"json");

        $data=\app\common\model\dynamic\Dynamic::where("hiddswitch",0)
            ->alias(["ln_user"=>"user","ln_dynamic"=>"dynamic"])
            ->join("ln_user","dynamic.user_id=user.id")
            ->field("dynamic.id,user_id,content,images,comments,likes,showswitch,dynamic.createtime,nickname,avatar")
            ->limit(5)
            ->page($list["page"])
            ->select();
        return $this->success("请求成功",$data,200,"json");

    }

    /**获取动态详情
     *
     * @param $id
     * @return null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDynamicDetail(Request $request)
    {

        $data=$request->post();
        if (empty($data["page"]))
            return $this->error("分页参数不能为空",[],400,"json");
        if (empty($data["id"]))
            return $this->error("动态id不能为空",[],400,"json");
        if (!is_numeric($data["id"])){
            return $this->error("动态id错误",[],400,"json");
        }
        if (!is_numeric($data["page"])){
            return $this->error("分页参数错误",[],400,"json");
        }

        //获取动态
        $list=$list=\app\common\model\dynamic\Dynamic::where("showswitch",1)
            ->where("hiddswitch",0)
            ->alias(["ln_user"=>"user","ln_dynamic"=>"dynamic"])
            ->join("ln_user","dynamic.user_id=user.id")
            ->field("dynamic.id,user_id,content,images,comments,likes,showswitch,dynamic.createtime,nickname,avatar")
            ->select();
        //获取当前动态的评论
        $comment_list=Comment::where("type",2)
            ->where("parent_id",$data["id"])
            ->alias(["ln_user"=>"user","ln_dynamic"=>"dynamic","ln_comment"=>"comment"])
            ->join("ln_user","comment.user_id=user.id")
            ->field("comment.id,user_id,content,comments,likes,type,parent_id,reply_id,comment.createtime,nickname,avatar")
            ->limit(10)
            ->page($data["page"])
            ->select()
            ->toArray();

        $count=Comment::getReplyCount($comment_list);

        return $this->success('数据请求成功',["dynamic"=>$list,"comments"=>$count],200,"json");

    }

    /**发布新动态
     * @param Request $request
     * 必填 user_id,content
     * 选填 images
     * @return null
     */
    public function saveDynamic(Request $request)
    {
        $list=$request->post();
        if (empty($list["token"])) return $this->error("请登录后点赞",[],400,"json");
        $info=\app\common\library\Token::get($list["token"]);
        $token_bool=\app\common\library\Token::check($list["token"],$info["user_id"]);
        if (!$token_bool) return $this->error("token验证错误",[],400,"json");
        if (empty($list["user_id"]) ||!is_numeric($list["user_id"])){
            return $this->error("id参数错误",[],400,"json");
        }elseif (empty($list["content"])){
            return $this->error("动态内容不能为空",[],400,"json");
        }
        Db::startTrans();
        try {
            $data=\app\common\model\dynamic\Dynamic::create($list);
            Db::commit();
            return $this->success("动态发布成功",$data,200,"json");
        }catch (Exception $e){
            Db::rollback();
            return $this->error($e->getMessage(),[],400,"json");
        }
    }

    /**评论动态
     * @param Request $request
     * @return null
     */
    public function dynamicComment(Request $request)
    {
        $list=$request->post();
        if (empty($list["token"])) return $this->error("请登录后点赞",[],400,"json");
        $info=\app\common\library\Token::get($list["token"]);
        $token_bool=\app\common\library\Token::check($list["token"],$info["user_id"]);
        if (!$token_bool) return $this->error("token验证错误",[],400,"json");
        $parent_id=$list["id"];//动态的id
        $user_id=$list["user_id"];//评论的用户的id
        $content=$list["content"];//评论的内容
        $reply_id=$list["reply_id"];//回复的评论的id
        if (empty($list)) return $this->error("数据请求不能为空",[],400,"json");
        if (empty($parent_id)) return $this->error("资讯id不能为空",[],400,"json");
        if (empty($user_id)) return $this->error("用户id不能为空",[],400,"json");
        if (empty($content)) return $this->error("评论不能为空",[],400,"json");
        if (empty($list["type"])) return $this->error("评论类型不能为空",[],400,"json");
        if ($list["type"]==1){
            return $this->error("评论类型错误",[],400,"json");
        }
        $data=[
            "parent_id"=>$parent_id,
            "user_id"=>$user_id,
            "content"=>$content,
            "comments"=>0,
            "likes"=>0,
            "type"=>$list["type"],
            "reply_id"=>$reply_id,
        ];
        Db::startTrans();
        try {
            $res=Comment::create($data);
            if(!empty($reply_id)){
                Comment::where("id",$reply_id)
                    ->setInc("comments");
            }
            \app\common\model\dynamic\Dynamic::where("id",$parent_id)
                ->setInc("comments");
            Db::commit();
            return $this->success("评论成功",$res,200,"json");
        }catch (Exception $e){
            Db::rollback();
            return $this->error($e->getMessage(),[],400,"json");
        }

    }


    /**动态举报
     * @param Request $request
     * @return null
     */
    public function dynamicTip(Request $request)
    {
        $list=$request->post();
        if (empty($list["token"])) return $this->error("请登录后点赞",[],400,"json");
        $info=\app\common\library\Token::get($list["token"]);
        $token_bool=\app\common\library\Token::check($list["token"],$info["user_id"]);
        if (!$token_bool) return $this->error("token验证错误",[],400,"json");
        if (!is_numeric($list["id"]) || !is_numeric($list["user_id"])){
            return $this->error("id参数缺失",[],400,"json");
        }
        if (empty($list["content"])){
            return $this->error("举报理由不能为空",[],400,"json");
        }
        if (empty($list["images"])){
            return $this->error("举报图片不能为空",[],400,"json");
        }
        $data=[
            "parent_id"=>$list["id"],  //被举报动态id
            "user_id"=>$list["user_id"], //举报用户id
            "content"=>$list["content"],
            "images"=>$list["images"],
            "type"=>1,
            "solveswitch"=>0,
            "hiddswitch"=>0,
        ];
        Db::startTrans();
        try {
            $res=Tip::create($data);
            Db::commit();
            return $this->success("举报成功,请等待处理",$res,200,"json");
        }catch (Exception $e){
            Db::rollback();
            return $this->error("举报失败,请稍后再试",[],400,"json");
//            return $this->error($e->getMessage(),[],400,"json");
        }


    }



    /**点赞
     * @param Request $request($parent_id,$user_id,$like_type)
     * @return void|null
     */
    public function incLikes(Request $request)
    {
        $list=$request->post();
        if (empty($list["token"])) return $this->error("请登录后点赞",[],400,"json");
        $info=\app\common\library\Token::get($list["token"]);
        $token_bool=\app\common\library\Token::check($list["token"],$info["user_id"]);
        if (!$token_bool) return $this->error("token验证错误",[],400,"json");
        $parent_id=$list["id"];//点赞目标的id
        $user_id=$list["user_id"];//点赞的用户id
        $like_type=$list["like_type"];//点赞类型
        if(empty($parent_id) || empty($user_id)){
            return $this->error("数据参数缺失",[],400,"json");
        }
        $likes=Like::incLikes($user_id,$parent_id,$like_type);
        if ($likes==-1){
            return $this->error("点赞失败",[],400,"json");
        }
        if(($likes["like_times"])%2==0){
            return $this->success("点赞成功",[],200,"json");
        }else {
            return $this->success("取消点赞成功",[],200,"json");
        }
    }


}