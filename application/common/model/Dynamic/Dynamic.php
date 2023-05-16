<?php

namespace app\common\model\dynamic;

use think\Model;
use traits\model\SoftDelete;

class Dynamic extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'dynamic';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];
    

    







}
