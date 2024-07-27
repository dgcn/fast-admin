<?php

namespace app\admin\controller\upload;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

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

        $uploadClassify = Db::name('upload_classify')->select();
        $this->view->assign("upload_classify_list", $uploadClassify);
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

        if (empty($params['name'])) $this->error(__('Parameter %s can not be empty', 'name'));
        if (empty($params['upload_classify_id'])) $this->error(__('Parameter %s can not be empty', 'File type'));
        if (empty($params['local_url'])) $this->error(__('Parameter %s can not be empty', 'File'));

        $count = Db::name('upload_classify')->where('id', $params['upload_classify_id'])->count();
        if(!$count) $this->error(__('The file classify does not exist'));

        $userinfo = $this->auth->getUserInfo();
        $operator = $userinfo['username'];
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $params['full_url'] = config('app_base_api').$params['local_url'];
            $params['operator'] = $operator;
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
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
        if (empty($params['name'])) $this->error(__('Parameter %s can not be empty', 'name'));
        if (empty($params['upload_classify_id'])) $this->error(__('Parameter %s can not be empty', 'File type'));
        if (empty($params['local_url'])) $this->error(__('Parameter %s can not be empty', 'File'));

        $count = Db::name('upload_classify')->where('id', $params['upload_classify_id'])->count();
        if(!$count) $this->error(__('The file classify does not exist'));
        $params = $this->preExcludeFields($params);
        $userinfo = $this->auth->getUserInfo();
        $operator = $userinfo['username'];
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $params['operator'] = $operator;
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }
}
