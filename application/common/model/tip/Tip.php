<?php

namespace app\common\model\tip;

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
    

    







}
