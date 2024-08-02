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
            $createtime = $item['createtime'];
            $updatetime = $item['updatetime'];
            $item['file_info_json'] = json_decode($item['file_info_json'], JSON_UNESCAPED_UNICODE);
            $item['createtime'] = date('Y-m-d H:i:s', $createtime);
            $item['updatetime'] = date('Y-m-d H:i:s', $updatetime);
            $item['create_date'] = date('Y-m-d', $createtime);
            $item['update_date'] = date('Y-m-d', $updatetime);
        }

        $this->success(__('Operation successful'), array(
            'list'=> $list
        ));
    }

    public function getInfo(){
        $id = $this->request->get('id');
        if (empty($id)) $this->error(__('Parameter exception'));
        $info = db('upload_file')->where('id', $id)->find();
        if (!$info)  $this->error(__('Parameter exception'));
        if ($info['status'] != 1)  $this->error(__('Status exception'));
        $info['file_info_json'] = json_decode($info['file_info_json'], JSON_UNESCAPED_UNICODE);
        $info['upload_classify_name'] = Db::name('upload_classify')->where('id', $info['upload_classify_id'])->value('name');
        $info['file_size'] = filesize($info['local_url']);

        $createtime = $info['createtime'];
        $updatetime = $info['updatetime'];
        $info['createtime'] = date('Y-m-d H:i:s', $createtime);
        $info['updatetime'] = date('Y-m-d H:i:s', $updatetime);
        $info['create_date'] = date('Y-m-d', $createtime);
        $info['update_date'] = date('Y-m-d', $updatetime);
        $this->success(__('Operation successful'), $info);
    }

    public function collect()
    {
        $id = $this->request->get('id');
        $status = $this->request->get('status');
        if (empty($id) || empty($status)) $this->error(__('Parameter exception'));
        $info = db('upload_file')->where('id', $id)->find();
        if (!$info)  $this->error(__('Parameter exception'));

        if ($status == 1) {
            Db::name('upload_file')->where('id', $id)->setInc('collect_count');
        }

        if ($status == 2 && $info['collect_count'] > 0) {
            Db::name('upload_file')->where('id', $id)->setDec('collect_count');
        }

        $this->success(__('Operation successful'));
    }

    public function addRead()
    {
        $id = $this->request->get('id');
        if (empty($id)) $this->error(__('Parameter exception'));
        $info = db('upload_file')->where('id', $id)->find();
        if (!$info)  $this->error(__('Parameter exception'));

        Db::name('upload_file')->where('id', $id)->setInc('read_count');
        $this->success(__('Operation successful'));
    }
}
