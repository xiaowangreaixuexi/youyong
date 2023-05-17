<?php

namespace app\api\controller;


use app\common\controller\Api;
use think\Exception;
use think\Request;

/**评论api接口
 * class Comment
 */
class Comment extends Api{
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


    /**获取评论回复
     * @param Request $request
     * @return null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getReply(Request $request)
    {
        $list=$request->post();
        if (!is_numeric($list["id"]))
            return $this->error("评论id错误",[],400,"json");
        if (!is_numeric($list["type"]) || $list["type"]!=3)
            return $this->error("评论类型错误",[],400,"json");
        $reply_list=\app\common\model\Comment\Comment::where("parent_id",$list["id"])
            ->where("type",3)
            ->select()
            ->toArray();
        $reply_list=\app\common\model\Comment\Comment::getReplyNickname($reply_list);
        return $this->success("回复获取成功",$reply_list,200,"json");
    }



}