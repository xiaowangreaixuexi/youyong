<?php
namespace app\api\controller;

use app\admin\command\Api;
use think\Request;

/**暂时没用到的接口
 *
 */
class Reply extends Api{

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

    public function getReplyCount(Request $request)
    {
        $list=$request->post();

    }
}