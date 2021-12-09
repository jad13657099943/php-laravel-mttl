<?php


namespace Modules\User\Http\Controllers\admin\api;


use Modules\Core\Http\Requests\Admin\Media\UploadRequest;

class UploadController
{

    /**
     * layEdit编辑器上传图片专用接口
     * @param UploadRequest $request
     * @return array
     */
    public function uploadForLayEdit(UploadRequest $request)
    {

        $logic = resolve(\Modules\Core\Http\Controllers\Admin\Api\Media\UploadController::class);
        $res = $logic->upload($request);
        if (isset($res['url'])) {
            return [

                'code' => 0,
                'msg' => '上传成功',
                'data' => [
                    'src' => $res['url'],
                    'title' => $res['basename'],
                    'other' => $res,
                ],
            ];
        } else {
            return [
                'code' => -1,
                'msg' => '上传失败',
            ];
        }
    }

}
