<?php

namespace app\service\upload;

use think\Db;

class ClassifyService
{

    public function list($params)
    {
        $status = !empty($params['status']) ? $params['status'] : -1;
        $type =  !empty($params['type']) ? $params['type'] : '';
        $name =  !empty($params['name']) ? $params['name'] : '';
        $where = [];
        if ($status != -1) $where['status'] = $status;
        if (!empty($name)) $where['name'] = ['like', '%'.$name.'%'];
        if (!empty($type)) $where['type'] = ['like', '%'.$type.'%'];

        if (count($where) > 0) {
            $list = Db::name('upload_classify')->where($where)->select();
            $total = Db::name('upload_classify')->where($where)->count();

        }else {
            $list = Db::name('upload_classify')->select();
            $total = Db::name('upload_classify')->count();
        }
        return [
            'list' => $list,
            'total' => $total
        ];
    }

}
