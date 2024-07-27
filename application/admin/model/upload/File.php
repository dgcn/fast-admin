<?php

namespace app\admin\model\upload;

use think\Model;


class File extends Model
{

    

    

    // 表名
    protected $name = 'upload_file';
    
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
