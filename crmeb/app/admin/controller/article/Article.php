<?php

namespace app\admin\controller\article;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemAttachment;
use crmeb\services\{
    UtilService as Util, JsonService as Json
};
use app\admin\model\article\{
    ArticleCategory as ArticleCategoryModel, Article as ArticleModel
};

/**
 * 图文管理
 * Class WechatNews
 * @package app\admin\controller\wechat
 */
class Article extends AuthController
{
    /**
     * TODO 显示后台管理员添加的图文
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $where = Util::getMore([
            ['title', ''],
            ['cid', $this->request->param('pid', '')],
        ], $this->request);
        $this->assign('where', $where);
        $where['merchant'] = 0;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        $tree = sort_list_tier(ArticleCategoryModel::getArticleCategoryList());
        $this->assign(compact('tree'));
        $this->assign(ArticleModel::getAll($where));
        return $this->fetch();
    }

    /**
     * TODO 文件添加和修改
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create()
    {
        $id = $this->request->param('id');
        $cid = $this->request->param('cid');
        $news = [];
        $all = [];
        $news['id'] = '';
        $news['image_input'] = '';
        $news['title'] = '';
        $news['author'] = '';
        $news['is_banner'] = '';
        $news['is_hot'] = '';
        $news['content'] = '';
        $news['synopsis'] = '';
        $news['url'] = '';
        $news['cid'] = [];
        $select = 0;
        if ($id) {
            $news = ArticleModel::where('n.id', $id)->alias('n')->field('n.*,c.content')->join('ArticleContent c', 'c.nid=n.id', 'left')->find();
            if (!$news) return $this->failed('数据不存在!');
            $news['cid'] = explode(',', $news['cid']);
            $news['content'] = htmlspecialchars_decode($news['content']);
        }
        if ($cid && in_array($cid, ArticleCategoryModel::getArticleCategoryInfo(0, 'id'))) {
            $all = ArticleCategoryModel::getArticleCategoryInfo($cid);
            $select = 1;
        }
        if (!$select) {
            $list = ArticleCategoryModel::getTierList();
            foreach ($list as $menu) {
                $all[$menu['id']] = $menu['html'] . $menu['title'];
            }
        }
        $this->assign('all', $all);
        $this->assign('news', $news);
        $this->assign('cid', $cid);
        $this->assign('select', $select);
        return $this->fetch();
    }

    /**
     * 上传图文图片
     * @return \think\response\Json
     */
    public function upload_image()
    {
        $res = Upload::instance()->setUploadPath('wechat/image/' . date('Ymd'))->image($_POST['file']);
        if (!is_array($res)) return Json::fail($res);
        SystemAttachment::attachmentAdd($res['name'], $res['size'], $res['type'], $res['dir'], $res['thumb_path'], 5, $res['image_type'], $res['time']);
        return Json::successful('上传成功!', ['url' => $res['dir']]);
    }

    /**
     * 添加和修改图文
     */
    public function add_new()
    {
        $data = Util::postMore([
            ['id', 0],
            ['cid', []],
            'title',
            'author',
            'image_input',
            'content',
            'synopsis',
            'share_title',
            'share_synopsis',
            ['visit', 0],
            ['sort', 0],
            'url',
            ['is_banner', 0],
            ['is_hot', 0],
            ['status', 1],]);
        $data['cid'] = implode(',', $data['cid']);
        $content = $data['content'];
        unset($data['content']);
        if ($data['id']) {
            $id = $data['id'];
            unset($data['id']);
            $res = false;
            ArticleModel::beginTrans();
            $res1 = ArticleModel::edit($data, $id, 'id');
            $res2 = ArticleModel::setContent($id, $content);
            if ($res1 && $res2) {
                $res = true;
            }
            ArticleModel::checkTrans($res);
            if ($res)
                return Json::successful('修改图文成功!', $id);
            else
                return Json::fail('修改图文失败，您并没有修改什么!', $id);
        } else {
            $data['add_time'] = time();
            $data['admin_id'] = $this->adminId;
            $res = false;
            ArticleModel::beginTrans();
            $res1 = ArticleModel::create($data);
            $res2 = false;
            if ($res1)
                $res2 = ArticleModel::setContent($res1->id, $content);
            if ($res1 && $res2) {
                $res = true;
            }
            ArticleModel::checkTrans($res);
            if ($res)
                return Json::successful('添加图文成功!', $res1->id);
            else
                return Json::successful('添加图文失败!', $res1->id);
        }
    }

    /**
     * 删除图文
     * @param $id
     * @return \think\response\Json
     */
    public function delete($id)
    {
        $res = ArticleModel::del($id);
        if (!$res)
            return Json::fail('删除失败,请稍候再试!');
        else
            return Json::successful('删除成功!');
    }

    public function merchantIndex()
    {
        $where = Util::getMore([
            ['title', '']
        ], $this->request);
        $this->assign('where', $where);
        $where['cid'] = input('cid');
        $where['merchant'] = 1;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        $this->assign(ArticleModel::getAll($where));
        return $this->fetch();
    }

    /**
     * 关联文章 id
     * @param int $id
     */
    public function relation($id = 0)
    {
        $this->assign('id', $id);
        return $this->fetch();
    }

    /**
     * 保存选择的产品
     * @param int $id
     */
    public function edit_article($id = 0)
    {
        if (!$id) return Json::fail('缺少参数');
        list($product_id) = Util::postMore([
            ['product_id', 0]
        ], $this->request, true);
        if (ArticleModel::edit(['product_id' => $product_id], ['id' => $id]))
            return Json::successful('保存成功');
        else
            return Json::fail('保存失败');
    }

    /**
     * 取消绑定的产品id
     * @param int $id
     */
    public function unrelation($id = 0)
    {
        if (!$id) return Json::fail('缺少参数');
        if (ArticleModel::edit(['product_id' => 0], $id))
            return Json::successful('取消关联成功！');
        else
            return Json::fail('取消失败');
    }
}