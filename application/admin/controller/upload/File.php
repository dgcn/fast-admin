<?php

namespace app\admin\controller\upload;

use app\common\controller\Backend;
use app\service\upload\ClassifyService;
use app\service\upload\FileService;
use think\Db;
use think\exception\PDOException;
/**
 * 文件上传
 *
 * @icon fa fa-circle-o
 */
class File extends Backend
{

    /**
     * File模型对象
     * @var \app\admin\model\upload\File
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\upload\File;

        $uploadClassify = new ClassifyService();
        $this->view->assign("upload_classify_list", $uploadClassify->list([])['list']);
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        foreach ($list as $k => &$v) {
            $v['upload_classify_name'] = Db::name('upload_classify')->where('id', $v['upload_classify_id'])->value('name');

            $v['file_info_json'] = json_decode($v['file_info_json'], JSON_UNESCAPED_UNICODE);
        }

        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        if (empty($params['upload_classify_id'])) $this->error(__('Parameter %s can not be empty', 'File type'));
        if (empty($params['local_url'])) $this->error(__('Parameter %s can not be empty', 'File'));

        $count = Db::name('upload_classify')->where('id', $params['upload_classify_id'])->count();
        if (!$count) $this->error(__('The file classify does not exist'));

        $userinfo = $this->auth->getUserInfo();
        $operator = $userinfo['username'];
        $params['operator'] = $operator;
        try {
            $fileService = new FileService();
            $fileService->add($params);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success();
    }

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $userinfo = $this->auth->getUserInfo();
        $operator = $userinfo['username'];
        $params['operator'] = $operator;
        try {
            $fileService = new FileService();
            $params['id'] = $row->id;
            $fileService->edit($params);
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
        $this->success();
    }

    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = $this->model->where($pk, 'in', $ids)->select();

        $count = 0;
        Db::startTrans();
        try {
            foreach ($list as $item) {
                $count += $item->delete();
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }
}
