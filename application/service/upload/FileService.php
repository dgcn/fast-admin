<?php

namespace app\service\upload;

use think\Db;
use think\Exception;

class FileService
{


    const UPLOAD_PREFIX = '__$$__';

    public function add($params)
    {
        if (empty($params['upload_classify_id'])) throw new Exception(__('Parameter %s can not be empty', 'File type'));
        if (empty($params['local_url'])) throw new Exception(__('Parameter %s can not be empty', 'File'));

        $count = Db::name('upload_classify')->where('id', $params['upload_classify_id'])->count();
        if (!$count) throw new Exception(__('The file classify does not exist'));
        $isExistName = true;
        if (empty($params['name'])) $isExistName = false;


        Db::startTrans();
        try {
            foreach (explode(',', $params['local_url']) as $key => $url) {
                $fileInfo = $this->getFileInfo([$url])[0];
                if (!$isExistName) $params['name'] = $fileInfo['name'];
                $params['local_url'] = $url;
                $params['file_info_json'] = json_encode($fileInfo, JSON_UNESCAPED_UNICODE);
                $params['createtime'] = time();
                $params['updatetime'] = time();
                Db::name('upload_file')->insert($params);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
    }

    public function edit($params)
    {
        $existCount = Db::name('upload_file')->where('id', $params['id'])->count();
        if ($existCount <= 0) throw new Exception(__('The file does not exist'));
        $updateData = [];
        if (!empty($params['name'])) $updateData['name'] = $params['name'];
        if (!empty($params['local_url'])) {
            $updateData['local_url'] = $params['local_url'];
            $fileInfo  = $this->getFileInfo([$params['local_url']])[0];
            if (empty($params['name'])) $updateData['name'] = $fileInfo['name'];
            $updateData['file_info_json'] = json_encode($fileInfo, JSON_UNESCAPED_UNICODE);
        }

        if (!empty($params['status'])) $updateData['status'] = $params['status'];
        if (!empty($params['upload_classify_id'])) {
            $exitClassifyCount = Db::name('upload_classify')->where('id', $params['upload_classify_id'])->count();
            if ($exitClassifyCount <= 0) throw new Exception(__('The file classify does not exist'));
            $updateData['upload_classify_id'] = $params['upload_classify_id'];
        }
        if (!empty($updateData['operator'])) $updateData['operator'] = $params['operator'];
        $updateData['updatetime'] = time();
        $res = Db::name('upload_file')->where('id', $params['id'])->update($updateData);
        if (false === $res) throw new Exception(__('The file edit failed'));
    }

    public function getFileInfo($urls)
    {
        $fileArr = [];
        foreach ($urls as $key => $url) {
            $fileArrInfo = explode('/', $url);
            $filenameArr = end($fileArrInfo);
            $filenameArr = explode(self::UPLOAD_PREFIX, $filenameArr);
            $fileInfo = explode('.', end($filenameArr));
            $tmp = [
                'md5' => $filenameArr[0],
                'full_name' => $filenameArr[1],
                'type' => end($fileInfo),
                'name' => $fileInfo[0],
                'full_url' => config('app_base_api') . $url,
                'local_url' => $url
            ];
            array_push($fileArr, $tmp);
        }
        return $fileArr;
    }

}
