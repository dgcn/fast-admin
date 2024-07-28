<?php

namespace app\api\controller\upload;
use app\service\upload\ClassifyService;
use app\common\controller\Api;
use app\api\middleware\AuthMiddleware;


class Classify extends Api
{
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
        $uploadClassService = new ClassifyService();
        $list = $uploadClassService->list($params);
        $this->success(__('Operation successful'), $list);
    }

}
