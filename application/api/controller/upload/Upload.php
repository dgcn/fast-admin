<?php

namespace app\api\controller\upload;

use app\api\middleware\AuthMiddleware;
use app\common\controller\Api;
use think\Db;

/**
 * 示例接口
 */
class Upload extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = '*';
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
        if (!AuthMiddleware::checkApiKey($this->request)) $this->error(__('Api key invalid'));
    }


    public function list()
    {
        $params = $this->request->get();
        $uploadClassifyId = !empty($params['upload_classify_id']) ? $params['upload_classify_id'] : -1;
        $status = !empty($params['status']) ? $params['status'] : -1;
        $name =!empty( $params['name']) ? $params['name'] : '';
        $where = [];
        if ($status != -1) $where['status'] = $status;
        if ($uploadClassifyId!= -1) $where['upload_classify_id'] = $uploadClassifyId;
        if ($name) $where['name'] = ['like', '%'.$name.'%'];

        $list = Db::name('upload_file')->where($where)->select();

        foreach ($list as $key => &$item) {
            $item['file_info_json'] = json_decode($item['file_info_json'], JSON_UNESCAPED_UNICODE);
            $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            $item['updatetime'] = date('Y-m-d H:i:s', $item['updatetime']);
        }

        $this->success(__('Operation successful'), $list);
    }
}
