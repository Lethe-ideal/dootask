<?php

namespace App\Http\Controllers\Api;


use App\Models\File;
use App\Models\FileContent;
use App\Models\User;
use App\Module\Base;
use Request;

/**
 * @apiDefine file
 *
 * 文件
 */
class FileController extends AbstractController
{
    /**
     * 获取文件列表
     *
     * @apiParam {Number} [pid]         父级ID
     */
    public function lists()
    {
        $user = User::auth();
        //
        $pid = intval(Request::input('pid'));
        //
        $list = File::whereUserid($user->userid)->wherePid($pid)->take(500)->get();
        $array = $list->toArray();
        //
        while ($pid > 0) {
            $file = File::whereUserid($user->userid)->whereId($pid)->first();
            if ($file) {
                $array[] = $file->toArray();
                $pid = $file->pid;
            }
        }
        return Base::retSuccess('success', $array);
    }

    /**
     * 添加、修改文件(夹)
     *
     * @apiParam {String} name          项目名称
     * @apiParam {String} type          文件类型
     * @apiParam {Number} [id]          文件ID（赋值修改文件名称）
     * @apiParam {Number} [pid]         父级ID
     */
    public function add()
    {
        $user = User::auth();
        // 文件名称
        $name = trim(Request::input('name'));
        $type = trim(Request::input('type'));
        $id = intval(Request::input('id'));
        $pid = intval(Request::input('pid'));
        if (mb_strlen($name) < 2) {
            return Base::retError('文件名称不可以少于2个字');
        } elseif (mb_strlen($name) > 32) {
            return Base::retError('文件名称最多只能设置32个字');
        }
        //
        if ($id > 0) {
            // 修改
            $file = File::whereUserid($user->userid)->whereId($id)->first();
            if (empty($file)) {
                return Base::retError('文件不存在或已被删除');
            }
            $file->name = $name;
            $file->save();
            return Base::retSuccess('修改成功', $file);
        } else {
            // 添加
            if (!in_array($type, [
                'folder',
                'document',
                'mind',
                'sheet',
                'flow',
            ])) {
                return Base::retError('类型错误');
            }
            //
            if ($pid > 0) {
                if (!File::whereUserid($user->userid)->whereId($pid)->exists()) {
                    return Base::retError('参数错误');
                }
            }
            if (File::whereUserid($user->userid)->wherePid($pid)->count() >= 300) {
                return Base::retError('每个文件夹里最多只能创建300个文件或文件夹');
            }
            // 开始创建
            $file = File::createInstance([
                'pid' => $pid,
                'name' => $name,
                'type' => $type,
                'userid' => $user->userid,
            ]);
            $file->save();
            //
            $data = File::find($file->id);
            return Base::retSuccess('添加成功', $data);
        }
    }

    /**
     * 复制文件(夹)
     *
     * @apiParam {Number} id            文件ID
     */
    public function copy()
    {
        $user = User::auth();
        //
        $id = intval(Request::input('id'));
        //
        $row = File::whereUserid($user->userid)->whereId($id)->first();
        if (empty($row)) {
            return Base::retError('文件不存在或已被删除');
        }
        if ($row->type == 'folder') {
            return Base::retError('不支持复制文件夹');
        }
        $num = File::whereCid($row->id)->count() + 1;
        $name = $row->name . " ({$num})";
        // 开始复制
        $file = File::createInstance([
            'cid' => $row->id,
            'pid' => $row->pid,
            'name' => $name,
            'type' => $row->type,
            'userid' => $user->userid,
        ]);
        $file->save();
        //
        $data = File::find($file->id);
        return Base::retSuccess('复制成功', $data);
    }

    /**
     * 移动文件(夹)
     *
     * @apiParam {Number} id            文件ID
     * @apiParam {Number} pid           移动到的文件夹ID
     */
    public function move()
    {
        $user = User::auth();
        //
        $id = intval(Request::input('id'));
        $pid = intval(Request::input('pid'));
        //
        $file = File::whereUserid($user->userid)->whereId($id)->first();
        if (empty($file)) {
            return Base::retError('文件不存在或已被删除');
        }
        //
        if ($pid > 0) {
            if (!File::whereUserid($user->userid)->whereId($pid)->exists()) {
                return Base::retError('参数错误');
            }
            $arr = [];
            $tid = $pid;
            while ($tid > 0) {
                $arr[] = $tid;
                $tid = intval(File::whereId($tid)->value('pid'));
            }
            if (in_array($id, $arr)) {
                return Base::retError('位置错误');
            }
        }
        //
        $file->pid = $pid;
        $file->save();
        return Base::retSuccess('操作成功', $file);
    }

    /**
     * 删除文件(夹)
     *
     * @apiParam {Number} id            文件ID
     */
    public function remove()
    {
        $user = User::auth();
        //
        $id = intval(Request::input('id'));
        //
        $file = File::whereUserid($user->userid)->whereId($id)->first();
        if (empty($file)) {
            return Base::retError('文件不存在或已被删除');
        }
        $file->deleteFile();
        return Base::retSuccess('删除成功', $file);
    }

    /**
     * 文件内容
     *
     * @apiParam {Number} id            文件ID
     */
    public function content()
    {
        $user = User::auth();
        //
        $id = intval(Request::input('id'));
        //
        $file = File::whereUserid($user->userid)->whereId($id)->first();
        if (empty($file)) {
            return Base::retError('文件不存在或已被删除');
        }
        //
        $content = FileContent::whereFid($file->id)->orderByDesc('id')->first();
        if (empty($content)) {
            $content = FileContent::createInstance([
                'fid' => $file->id,
                'content' => '{}',
                'userid' => $user->userid,
            ]);
            $content->save();
        }
        //
        $content->content = $content->formatContent($file->type, $content->content);
        return Base::retSuccess('success', $content);
    }

    /**
     * 保存文件内容
     *
     * @apiParam {Number} id            文件ID
     * @apiParam {Object} [D]               Request Payload 提交
     * - content: 内容
     */
    public function content__save()
    {
        $user = User::auth();
        //
        $id = Base::getPostInt('id');
        $content = Base::getPostValue('content');
        //
        $file = File::whereUserid($user->userid)->whereId($id)->first();
        if (empty($file)) {
            return Base::retError('文件不存在或已被删除');
        }
        //
        $text = '';
        if ($file->type == 'document') {
            $data = Base::json2array($content);
            $isRep = false;
            preg_match_all("/<img\s*src=\"data:image\/(png|jpg|jpeg);base64,(.*?)\"/s", $data['content'], $matchs);
            foreach ($matchs[2] as $key => $text) {
                $p = "uploads/files/document/" . $id . "/";
                Base::makeDir(public_path($p));
                $p.= md5($text) . "." . $matchs[1][$key];
                $r = file_put_contents(public_path($p), base64_decode($text));
                if ($r) {
                    $data['content'] = str_replace($matchs[0][$key], '<img src="' . Base::fillUrl($p) . '"', $data['content']);
                    $isRep = true;
                }
            }
            $text = strip_tags($data['content']);
            if ($isRep == true) {
                $content = Base::array2json($data);
            }
        }
        //
        $content = FileContent::createInstance([
            'fid' => $file->id,
            'content' => $content,
            'text' => $text,
            'size' => strlen($content) * 8,
            'userid' => $user->userid,
        ]);
        $content->save();
        //
        $file->size = $content->size;
        $file->save();
        //
        $content->content = $content->formatContent($file->type, $content->content);
        return Base::retSuccess('保存成功', $content);
    }
}