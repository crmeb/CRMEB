<?php
/**
 * Project: 快速复制 淘宝、天猫、1688、京东 商品到CRMEB系统
 * Author: 有一片天 <810806442@qq.com>  微信：szktor
 * Date: 2019-04-25
 */

namespace app\admin\controller\store;

use crmeb\services\HttpService;
use crmeb\services\UploadService;
use think\exception\PDOException;
use app\admin\controller\AuthController;
use app\admin\model\system\SystemConfig;
use crmeb\traits\CurdControllerTrait;
use crmeb\services\JsonService;
use crmeb\services\UtilService;
use app\admin\model\store\StoreCategory as CategoryModel;
use app\admin\model\store\StoreProduct as ProductModel;
use app\admin\model\system\SystemAttachment;
use app\admin\model\system\SystemAttachmentCategory;

/**
 * 产品管理
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class CopyTaobao extends AuthController
{

    use CurdControllerTrait;

    protected $bindModel = ProductModel::class;
    //错误信息
    protected $errorInfo = true;
    //产品默认字段
    protected $productInfo = [
        'cate_id' => '',
        'store_name' => '',
        'store_info' => '',
        'unit_name' => '件',
        'price' => 0,
        'keyword' => '',
        'ficti' => 0,
        'ot_price' => 0,
        'give_integral' => 0,
        'postage' => 0,
        'cost' => 0,
        'image' => '',
        'slider_image' => '',
        'add_time' => 0,
        'stock' => 0,
        'description' => '',
        'soure_link' => ''
    ];
    //抓取网站主域名
    protected $grabName = [
        'taobao',
        '1688',
        'tmall',
        'jd'
    ];
    //远程下载附件图片分类名称
    protected $AttachmentCategoryName = '远程下载';

    /**
     * 显示资源
     * @return html
     */
    public function index()
    {
        $list = CategoryModel::getTierList(null, 1);
        $menus = [];
        foreach ($list as $menu) {
            $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name'], 'disabled' => $menu['pid'] == 0];//,'disabled'=>$menu['pid']== 0];
        }
        $this->assign('menus', $menus);
        $this->assign('is_layui', 1);
        return $this->fetch();
    }

    /*
     * 设置错误信息
     * @param string $msg 错误信息
     * */
    public function setErrorInfo($msg = '')
    {
        $this->errorInfo = $msg;
        return false;
    }

    /*
     * 设置字符串字符集
     * @param string $str 需要设置字符集的字符串
     * @return string
     * */
    public function Utf8String($str)
    {
        $encode = mb_detect_encoding($str, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
        if (strtoupper($encode) != 'UTF-8') $str = mb_convert_encoding($str, 'utf-8', $encode);
        return $str;
    }

    /**
     * 获取资源,并解析出对应的商品参数
     * @return json
     */
    public function get_request_contents()
    {
        list($link) = UtilService::postMore([
            ['link', '']
        ], $this->request, true);
        $url = $this->checkurl($link);
        if ($url === false) return JsonService::fail($this->errorInfo);
        $this->errorInfo = true;
        $html = $this->curl_Get($url, 60);
        if (!$html) return JsonService::fail('商品HTML信息获取失败');
        $html = $this->Utf8String($html);
        preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
        //商品标题
        $this->productInfo['store_name'] = isset($title['1']) ? str_replace(['-淘宝网', '-tmall.com天猫', ' - 阿里巴巴', ' ', '-', '【图片价格品牌报价】京东', '京东', '【行情报价价格评测】'], '', trim($title['1'])) : '';
        $this->productInfo['store_info'] = $this->productInfo['store_name'];
        try {
            //获取url信息
            $pathinfo = pathinfo($url);
            if (!isset($pathinfo['dirname'])) return JsonService::fail('解析URL失败');
            //提取域名
            $parse_url = parse_url($pathinfo['dirname']);
            if (!isset($parse_url['host'])) return JsonService::fail('获取域名失败');
            //获取第一次.出现的位置
            $strLeng = strpos($parse_url['host'], '.') + 1;
            //截取域名中的真实域名不带.com后的
            $funsuffix = substr($parse_url['host'], $strLeng, strrpos($parse_url['host'], '.') - $strLeng);
            if (!in_array($funsuffix, $this->grabName)) return JsonService::fail('您输入的地址不在复制范围内！');
            //设拼接设置产品函数
            $funName = "setProductInfo" . ucfirst($funsuffix);
            //执行方法
            if (method_exists($this, $funName))
                $this->$funName($html);
            else
                return JsonService::fail('设置产品函数不存在');
            if (!$this->productInfo['slider_image']) return JsonService::fail('未能获取到商品信息，请确保商品信息有效！');
            return JsonService::successful($this->productInfo);
        } catch (\Exception $e) {
            return JsonService::fail('系统错误', ['line' => $e->getLine(), 'meass' => $e->getMessage()]);
        }
    }

    /*
     * 淘宝设置产品
     * @param string $html 网页内容
     * */
    public function setProductInfoTaobao($html)
    {
        //获取轮播图
        $images = $this->getTaobaoImg($html);
        $images = array_merge($images);
        $this->productInfo['slider_image'] = isset($images['gaoqing']) ? $images['gaoqing'] : (array)$images;
        //获取产品详情请求链接
        $link = $this->getTaobaoDesc($html);
        //获取请求内容
        $desc_json = HttpService::getRequest($link);
        //转换字符集
        $desc_json = $this->Utf8String($desc_json);
        //截取掉多余字符
        $this->productInfo['test'] = $desc_json;
        $desc_json = str_replace('var desc=\'', '', $desc_json);
        $desc_json = str_replace(["\n", "\t", "\r"], '', $desc_json);
        $content = substr($desc_json, 0, -2);
        $this->productInfo['description'] = $content;
        //获取详情图
        $description_images = $this->decodedesc($this->productInfo['description']);
        $this->productInfo['description_images'] = is_array($description_images) ? $description_images : [];
        $this->productInfo['image'] = is_array($this->productInfo['slider_image']) && isset($this->productInfo['slider_image'][0]) ? $this->productInfo['slider_image'][0] : '';
    }

    /*
     * 天猫设置产品
     * @param string $html 网页内容
     * */
    public function setProductInfoTmall($html)
    {
        //获取轮播图
        $images = $this->getTianMaoImg($html);
        $images = array_merge($images);
        $this->productInfo['slider_image'] = $images;
        $this->productInfo['image'] = is_array($this->productInfo['slider_image']) && isset($this->productInfo['slider_image'][0]) ? $this->productInfo['slider_image'][0] : '';
        //获取产品详情请求链接
        $link = $this->getTianMaoDesc($html);
        //获取请求内容
        $desc_json = HttpService::getRequest($link);
        //转换字符集
        $desc_json = $this->Utf8String($desc_json);
        //截取掉多余字符
        $desc_json = str_replace('var desc=\'', '', $desc_json);
        $desc_json = str_replace(["\n", "\t", "\r"], '', $desc_json);
        $content = substr($desc_json, 0, -2);
        $this->productInfo['description'] = $content;
        //获取详情图
        $description_images = $this->decodedesc($this->productInfo['description']);
        $this->productInfo['description_images'] = is_array($description_images) ? $description_images : [];
    }

    /*
     * 1688设置产品
     * @param string $html 网页内容
     * */
    public function setProductInfo1688($html)
    {
        //获取轮播图
        $images = $this->get1688Img($html);
        if (isset($images['gaoqing'])) {
            $images['gaoqing'] = array_merge($images['gaoqing']);
            $this->productInfo['slider_image'] = $images['gaoqing'];
        } else
            $this->productInfo['slider_image'] = $images;
        $this->productInfo['image'] = is_array($this->productInfo['slider_image']) && isset($this->productInfo['slider_image'][0]) ? $this->productInfo['slider_image'][0] : '';
        //获取产品详情请求链接
        $link = $this->get1688Desc($html);
        //获取请求内容
        $desc_json = HttpService::getRequest($link);
        //转换字符集
        $desc_json = $this->Utf8String($desc_json);
        $this->productInfo['test'] = $desc_json;
        //截取掉多余字符
        $desc_json = str_replace('var offer_details=', '', $desc_json);
        $desc_json = str_replace(["\n", "\t", "\r"], '', $desc_json);
        $desc_json = substr($desc_json, 0, -1);
        $descArray = json_decode($desc_json, true);
        if (!isset($descArray['content'])) $descArray['content'] = '';
        $this->productInfo['description'] = $descArray['content'];
        //获取详情图
        $description_images = $this->decodedesc($this->productInfo['description']);
        $this->productInfo['description_images'] = is_array($description_images) ? $description_images : [];
    }

    /*
     * JD设置产品
     * @param string $html 网页内容
     * */
    public function setProductInfoJd($html)
    {
        //获取产品详情请求链接
        $desc_url = $this->getJdDesc($html);
        //获取请求内容
        $desc_json = HttpService::getRequest($desc_url);
        //转换字符集
        $desc_json = $this->Utf8String($desc_json);
        //截取掉多余字符
        if (substr($desc_json, 0, 8) == 'showdesc') $desc_json = str_replace('showdesc', '', $desc_json);
        $desc_json = str_replace('data-lazyload=', 'src=', $desc_json);
        $descArray = json_decode($desc_json, true);
        if (!$descArray) $descArray = ['content' => ''];
        //获取轮播图
        $images = $this->getJdImg($html);
        $images = array_merge($images);
        $this->productInfo['slider_image'] = $images;
        $this->productInfo['image'] = is_array($this->productInfo['slider_image']) ? $this->productInfo['slider_image'][0] : '';
        $this->productInfo['description'] = $descArray['content'];
        //获取详情图
        $description_images = $this->decodedesc($descArray['content']);
        $this->productInfo['description_images'] = is_array($description_images) ? $description_images : [];
    }

    /*
    * 检查淘宝，天猫，1688的商品链接
    * @return string
    */
    public function checkurl($link)
    {
        $link = strtolower($link);
        if (!$link) return $this->setErrorInfo('请输入链接地址');
        if (substr($link, 0, 4) != 'http') return $this->setErrorInfo('链接地址必须以http开头');
        $arrLine = explode('?', $link);
        if (!count($arrLine)) return $this->setErrorInfo('链接地址有误(ERR:1001)');
        if (!isset($arrLine[1])) {
            if (strpos($link, '1688') !== false && strpos($link, 'offer') !== false) return trim($arrLine[0]);
            else if (strpos($link, 'item.jd') !== false) return trim($arrLine[0]);
            else return $this->setErrorInfo('链接地址有误(ERR:1002)');
        }
        if (strpos($link, '1688') !== false && strpos($link, 'offer') !== false) return trim($arrLine[0]);
        if (strpos($link, 'item.jd') !== false) return trim($arrLine[0]);
        $arrLineValue = explode('&', $arrLine[1]);
        if (!is_array($arrLineValue)) return $this->setErrorInfo('链接地址有误(ERR:1003)');
        if (!strpos(trim($arrLine[0]), 'item.htm')) $this->setErrorInfo('链接地址有误(ERR:1004)');
        //链接参数
        $lastStr = '';
        foreach ($arrLineValue as $k => $v) {
            if (substr(strtolower($v), 0, 3) == 'id=') {
                $lastStr = trim($v);
                break;
            }
        }
        if (!$lastStr) return $this->setErrorInfo('链接地址有误(ERR:1005)');
        return trim($arrLine[0]) . '?' . $lastStr;
    }

    /*
     * 保存图片保存产品信息
     * */
    public function save_product()
    {
        $data = UtilService::postMore([
            ['cate_id', ''],
            ['store_name', ''],
            ['store_info', ''],
            ['keyword', ''],
            ['unit_name', ''],
            ['image', ''],
            ['slider_image', []],
            ['price', ''],
            ['ot_price', ''],
            ['give_integral', ''],
            ['postage', ''],
            ['sales', ''],
            ['ficti', ''],
            ['stock', ''],
            ['cost', ''],
            ['description_images', []],
            ['description', ''],
            ['is_show', 0],
            ['soure_link', ''],
        ]);
        if (!$data['cate_id']) return JsonService::fail('请选择分类！');
        if (!$data['store_name']) return JsonService::fail('请填写产品名称');
        if (!$data['unit_name']) return JsonService::fail('请填写产品单位');
        if (!$data['image']) return JsonService::fail('商品主图暂无，无法保存商品，您可选择其他链接进行复制产品');
        if ($data['price'] == '' || $data['price'] < 0) return JsonService::fail('请输入产品售价');
        if ($data['ot_price'] == '' || $data['ot_price'] < 0) return JsonService::fail('请输入产品市场价');
        if ($data['stock'] == '' || $data['stock'] < 0) return JsonService::fail('请输入库存');
        //查询附件分类
        $AttachmentCategory = SystemAttachmentCategory::where('name' , $this->AttachmentCategoryName)->find();
        //不存在则创建
        if (!$AttachmentCategory) $AttachmentCategory = SystemAttachmentCategory::create(['pid' => '0', 'name' => $this->AttachmentCategoryName, 'enname' => '']);
        //生成附件目录
        try{
            if (make_path('attach', 3) == '') return JsonService::fail('无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DS . 'uploads' . DS. 'attach' . DS);

        }catch (\Exception $e){
            return JsonService::fail($e->getMessage().'或无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DS . 'uploads' . DS. 'attach' . DS);
        }
        ini_set("max_execution_time", 600);
        //开始图片下载处理
        ProductModel::beginTrans();
        try {
            //放入主图
            $images = [
                ['w' => 305, 'h' => 305, 'line' => $data['image'], 'valuename' => 'image']
            ];
            //放入轮播图
            foreach ($data['slider_image'] as $item) {
                $value = ['w' => 640, 'h' => 640, 'line' => $item, 'valuename' => 'slider_image', 'isTwoArray' => true];
                array_push($images, $value);
            }
            //执行下载
            $res = $this->uploadImage($images, false, 0, $AttachmentCategory['id']);
            if (!is_array($res)) return JsonService::fail($this->errorInfo ? $this->errorInfo : '保存图片失败');
            if (isset($res['image'])) $data['image'] = $res['image'];
            if (isset($res['slider_image'])) $data['slider_image'] = $res['slider_image'];
            $data['slider_image'] = count($data['slider_image']) ? json_encode($data['slider_image']) : '';
            //替换并下载详情里面的图片默认下载全部图片
            $data['description'] = preg_replace('#<style>.*?</style>#is', '', $data['description']);
            $data['description'] = $this->uploadImage($data['description_images'], $data['description'], 1, $AttachmentCategory['id']);
            unset($data['description_images']);
            $data['add_time'] = time();
            $cate_id = explode(',', $data['cate_id']);
            //产品存在
            if ($productInfo = ProductModel::where('soure_link', $data['soure_link'])->find()) {
                $productInfo->description = $data['description'];
                $productInfo->slider_image = $data['slider_image'];
                $productInfo->image = $data['image'];
                $productInfo->store_name = $data['store_name'];
                $productInfo->save();
                ProductModel::commitTrans();
                return JsonService::successful('商品存在，信息已被更新成功');
            } else {
                //不存在时新增
                if ($res = ProductModel::create($data)) {
                    foreach ($cate_id as $cid) {
                        \think\facade\Db::name('store_product_cate')->insert(['product_id' => $res['id'], 'cate_id' => $cid, 'add_time' => time()]);
                    }
                    ProductModel::commitTrans();
                    return JsonService::successful('生成产品成功');
                } else {
                    ProductModel::rollbackTrans();
                    return JsonService::fail('生成产品失败');
                }
            }
        } catch (PDOException $e) {
            ProductModel::rollbackTrans();
            return JsonService::fail('插入数据库错误', ['line' => $e->getLine(), 'messag' => $e->getMessage()]);
        } catch (\Exception $e) {
            ProductModel::rollbackTrans();
            return JsonService::fail('系统错误', ['line' => $e->getLine(), 'messag' => $e->getMessage()]);
        }
    }

    /*
     * 上传图片处理
     * @param array $image 图片路径
     * @param int $uploadType 上传方式 0=远程下载
     * */
    public function uploadImage(array $images = [], $html = '', $uploadType = 0, $AttachmentCategoryId = 0)
    {
        $uploadImage = [];
        $siteUrl = SystemConfig::getConfigValue('site_url');
        switch ($uploadType) {
            case 0:
                foreach ($images as $item) {
                    //下载图片文件
                    if ($item['w'] && $item['h'])
                        $uploadValue = $this->downloadImage($item['line'], '', 0, 30, $item['w'], $item['h']);
                    else
                        $uploadValue = $this->downloadImage($item['line']);
                    //下载成功更新数据库
                    if (is_array($uploadValue)) {
                        //TODO 拼接图片地址
                        if ($uploadValue['image_type'] == 1) $imagePath = $siteUrl . $uploadValue['path'];
                        else $imagePath = $uploadValue['path'];
                        //写入数据库
                        if (!$uploadValue['is_exists'] && $AttachmentCategoryId) SystemAttachment::attachmentAdd($uploadValue['name'], $uploadValue['size'], $uploadValue['mime'], $imagePath, $imagePath, $AttachmentCategoryId, $uploadValue['image_type'], time(), 1);
                        //组装数组
                        if (isset($item['isTwoArray']) && $item['isTwoArray'])
                            $uploadImage[$item['valuename']][] = $imagePath;
                        else
                            $uploadImage[$item['valuename']] = $imagePath;
                    }
                }
                break;
            case 1:
                preg_match_all('#<img.*?src="([^"]*)"[^>]*>#i', $html, $match);
                if (isset($match[1])) {
                    foreach ($match[1] as $item) {
                        if (is_int(strpos($item, 'http')))
                            $arcurl = $item;
                        else
                            $arcurl = 'http://' . ltrim($item, '\//');
                        $uploadValue = $this->downloadImage($arcurl);
                        //下载成功更新数据库
                        if (is_array($uploadValue)) {
                            //TODO 拼接图片地址
                            if ($uploadValue['image_type'] == 1) $imagePath = $siteUrl . $uploadValue['path'];
                            else $imagePath = $uploadValue['path'];
                            //写入数据库
                            if (!$uploadValue['is_exists'] && $AttachmentCategoryId) SystemAttachment::attachmentAdd($uploadValue['name'], $uploadValue['size'], $uploadValue['mime'], $imagePath, $imagePath, $AttachmentCategoryId, $uploadValue['image_type'], time(), 1);
                            //替换图片
                            $html = str_replace($item, $imagePath, $html);
                        } else {
                            //替换掉没有下载下来的图片
                            $html = preg_replace('#<img.*?src="' . $item . '"*>#i', '', $html);
                        }
                    }
                }
                return $html;
                break;
            default:
                return $this->setErrorInfo('上传方式错误');
                break;
        }
        return $uploadImage;
    }

    //提取商品描述中的所有图片
    public function decodedesc($desc = '')
    {
        $desc = trim($desc);
        if (!$desc) return '';
        preg_match_all('/<img[^>]*?src="([^"]*?)"[^>]*?>/i', $desc, $match);
        if (!isset($match[1]) || count($match[1]) <= 0) {
            preg_match_all('/:url(([^"]*?));/i', $desc, $match);
            if (!isset($match[1]) || count($match[1]) <= 0) return $desc;
        } else {
            preg_match_all('/:url(([^"]*?));/i', $desc, $newmatch);
            if (isset($newmatch[1]) && count($newmatch[1]) > 0) $match[1] = array_merge($match[1], $newmatch[1]);
        }
        $match[1] = array_unique($match[1]); //去掉重复
        foreach ($match[1] as $k => &$v) {
            $_tmp_img = str_replace([')', '(', ';'], '', $v);
            $_tmp_img = strpos($_tmp_img, 'http') ? $_tmp_img : 'http:' . $_tmp_img;
            if (strpos($v, '?')) {
                $_tarr = explode('?', $v);
                $_tmp_img = trim($_tarr[0]);
            }
            $_urls = str_replace(['\'', '"'], '', $_tmp_img);
            if ($this->_img_exists($_urls)) $v = $_urls;
        }
        return $match[1];
    }

    //获取京东商品组图
    public function getJdImg($html = '')
    {
        //获取图片服务器网址
        preg_match('/<img(.*?)id="spec-img"(.*?)data-origin=\"(.*?)\"[^>]*>/', $html, $img);
        if (!isset($img[3])) return '';
        $info = parse_url(trim($img[3]));
        if (!$info['host']) return '';
        if (!$info['path']) return '';
        $_tmparr = explode('/', trim($info['path']));
        $url = 'http://' . $info['host'] . '/' . $_tmparr[1] . '/' . str_replace(['jfs', ' '], '', trim($_tmparr[2]));
        preg_match('/imageList:(.*?)"],/is', $html, $img);
        if (!isset($img[1])) {
            return '';
        }
        $_arr = explode(',', $img[1]);
        foreach ($_arr as $k => &$v) {
            $_str = $url . str_replace(['"', '[', ']', ' '], '', trim($v));
            if (strpos($_str, '?')) {
                $_tarr = explode('?', $_str);
                $_str = trim($_tarr[0]);
            }
            if ($this->_img_exists($_str)) {
                $v = $_str;
            } else {
                unset($_arr[$k]);
            }
        }
        return array_unique($_arr);
    }

    //获取京东商品描述
    public function getJdDesc($html = '')
    {
        preg_match('/,(.*?)desc:([^<>]*)\',/i', $html, $descarr);
        if (!isset($descarr[1]) && !isset($descarr[2])) return '';
        $tmpArr = explode(',', $descarr[2]);
        if (count($tmpArr) > 0) {
            $descarr[2] = trim($tmpArr[0]);
        }
        $replace_arr = ['\'', '\',', ' ', ',', '/*', '*/'];
        if (isset($descarr[2])) {
            $d_url = str_replace($replace_arr, '', $descarr[2]);
            return $this->formatDescUrl(strpos($d_url, 'http') ? $d_url : 'http:' . $d_url);
        }
        $d_url = str_replace($replace_arr, '', $descarr[1]);
        $d_url = $this->formatDescUrl($d_url);
        $d_url = rtrim(rtrim($d_url, "?"), "&");
        return substr($d_url, 0, 4) == 'http' ? $d_url : 'http:' . $d_url;
    }

    //处理下京东商品描述网址
    public function formatDescUrl($url = '')
    {
        if (!$url) return '';
        $url = substr($url, 0, 4) == 'http' ? $url : 'http:' . $url;
        if (!strpos($url, '&')) {
            $_arr = explode('?', $url);
            if (!is_array($_arr) || count($_arr) <= 0) return $url;
            return trim($_arr[0]);
        } else {
            $_arr = explode('&', $url);
        }
        if (!is_array($_arr) || count($_arr) <= 0) return $url;
        unset($_arr[count($_arr) - 1]);
        $new_url = '';
        foreach ($_arr as $k => $v) {
            $new_url .= $v . '&';
        }
        return !$new_url ? $url : $new_url;
    }

    //获取1688商品组图
    public function get1688Img($html = '')
    {
        preg_match('/<ul class=\"nav nav-tabs fd-clr\">(.*?)<\/ul>/is', $html, $img);
        if (!isset($img[0])) {
            return '';
        }
        preg_match_all('/preview":"(.*?)\"\}\'>/is', $img[0], $arrb);
        if (!isset($arrb[1]) || count($arrb[1]) <= 0) {
            return '';
        }
        $thumb = [];
        $gaoqing = [];
        $res = ['thumb' => '', 'gaoqing' => ''];  //缩略图片和高清图片
        foreach ($arrb[1] as $k => $v) {
            $_str = str_replace(['","original":"'], '*', $v);
            $_arr = explode('*', $_str);
            if (is_array($_arr) && isset($_arr[0]) && isset($_arr[1])) {
                if (strpos($_arr[0], '?')) {
                    $_tarr = explode('?', $_arr[0]);
                    $_arr[0] = trim($_tarr[0]);
                }
                if (strpos($_arr[1], '?')) {
                    $_tarr = explode('?', $_arr[1]);
                    $_arr[1] = trim($_tarr[0]);
                }
                if ($this->_img_exists($_arr[0])) $thumb[] = trim($_arr[0]);
                if ($this->_img_exists($_arr[1])) $gaoqing[] = trim($_arr[1]);
            }
        }
        $res = ['thumb' => array_unique($thumb), 'gaoqing' => array_unique($gaoqing)];  //缩略图片和高清图片
        return $res;
    }

    //获取1688商品描述
    public function get1688Desc($html = '')
    {
        preg_match('/data-tfs-url="([^<>]*)data-enable="true"/', $html, $descarr);
        if (!isset($descarr[1])) return '';
        return str_replace(['"', ' '], '', $descarr[1]);
    }

    //获取天猫商品组图
    public function getTianMaoImg($html = '')
    {
        $pic_size = '430';
        preg_match('/<img[^>]*id="J_ImgBooth"[^r]*rc=\"([^"]*)\"[^>]*>/', $html, $img);
        if (isset($img[1])) {
            $_arr = explode('x', $img[1]);
            $filename = $_arr[count($_arr) - 1];
            $pic_size = intval(substr($filename, 0, 3));
        }
        preg_match('|<ul id="J_UlThumb" class="tb-thumb tm-clear">(.*)</ul>|isU', $html, $match);
        preg_match_all('/<img src="(.*?)" \//', $match[1], $images);
        if (!isset($images[1])) return '';
        foreach ($images[1] as $k => &$v) {
            $tmp_v = trim($v);
            $_arr = explode('x', $tmp_v);
            $_fname = $_arr[count($_arr) - 1];
            $_size = intval(substr($_fname, 0, 3));
            if (strpos($tmp_v, '://')) {
                $_arr = explode(':', $tmp_v);
                $r_url = trim($_arr[1]);
            } else {
                $r_url = $tmp_v;
            }
            $str = str_replace($_size, $pic_size, $r_url);
            if (strpos($str, '?')) {
                $_tarr = explode('?', $str);
                $str = trim($_tarr[0]);
            }
            $_i_url = strpos($str, 'http') ? $str : 'http:' . $str;
            if ($this->_img_exists($_i_url)) {
                $v = $_i_url;
            } else {
                unset($images[1][$k]);
            }
        }
        return array_unique($images[1]);
    }

    //获取天猫商品描述
    public function getTianMaoDesc($html = '')
    {
        preg_match('/descUrl":"([^<>]*)","httpsDescUrl":"/', $html, $descarr);
        if (!isset($descarr[1])) {
            preg_match('/httpsDescUrl":"([^<>]*)","fetchDcUrl/', $html, $descarr);
            if (!isset($descarr[1])) return '';
        }
        return strpos($descarr[1], 'http') ? $descarr[1] : 'http:' . $descarr[1];
    }

    //获取淘宝商品组图
    public function getTaobaoImg($html = '')
    {
        preg_match('/auctionImages([^<>]*)"]/', $html, $imgarr);
        if (!isset($imgarr[1])) return '';
        $arr = explode(',', $imgarr[1]);
        foreach ($arr as $k => &$v) {
            $str = trim($v);
            $str = str_replace(['"', ' ', '', ':['], '', $str);
            if (strpos($str, '?')) {
                $_tarr = explode('?', $str);
                $str = trim($_tarr[0]);
            }
            $_i_url = strpos($str, 'http') ? $str : 'http:' . $str;
            if ($this->_img_exists($_i_url)) {
                $v = $_i_url;
            } else {
                unset($arr[$k]);
            }
        }
        return array_unique($arr);
    }

    //获取淘宝商品描述
    public function getTaobaoDesc($html = '')
    {
        preg_match('/descUrl([^<>]*)counterApi/', $html, $descarr);
        if (!isset($descarr[1])) return '';
        $arr = explode(':', $descarr[1]);
        $url = [];
        foreach ($arr as $k => $v) {
            if (strpos($v, '//')) {
                $str = str_replace(['\'', ',', ' ', '?', ':'], '', $v);
                $url[] = trim($str);
            }
        }
        if ($url) {
            return strpos($url[0], 'http') ? $url[0] : 'http:' . $url[0];
        } else {
            return '';
        }
    }

    /**
     * GET 请求
     * @param string $url
     */
    public function curl_Get($url = '', $time_out = 25)
    {
        if (!$url) return '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查  
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('user-agent:' . $_SERVER['HTTP_USER_AGENT']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);
        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
            return false;
        }
        curl_close($ch);
        return mb_convert_encoding($response, 'utf-8', 'GB2312');
    }

    //检测远程文件是否存在
    public function _img_exists($url = '')
    {
        ini_set("max_execution_time", 0);
        $str = @file_get_contents($url, 0, null, 0, 1);
        if (strlen($str) <= 0) return false;
        if ($str)
            return true;
        else
            return false;
    }

    //TODO 下载图片
    public function downloadImage($url = '', $name = '', $type = 0, $timeout = 30, $w = 0, $h = 0)
    {
        if (!strlen(trim($url))) return '';
        if (!strlen(trim($name))) {
            //TODO 获取要下载的文件名称
            $downloadImageInfo = $this->getImageExtname($url);
            $name = $downloadImageInfo['file_name'];
            if (!strlen(trim($name))) return '';
        }
        //TODO 获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //TODO 跳过证书检查
            if (stripos($url, "https://") !== FALSE) curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  //TODO 从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('user-agent:' . $_SERVER['HTTP_USER_AGENT']));
            if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//TODO 是否采集301、302之后的页面
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            try {
                ob_start();
                readfile($url);
                $content = ob_get_contents();
                ob_end_clean();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        $size = strlen(trim($content));
        if (!$content || $size <= 2) return '图片流获取失败';
        $date_dir = date('Y') . DS . date('m') . DS . date('d');
        $imageInfo = UploadService::imageStream($name, $content, 'attach/' . $date_dir);
        if (!is_array($imageInfo)) return $imageInfo;
        $date['path'] = $imageInfo['dir'];
        $date['name'] = $imageInfo['name'];
        $date['size'] = $imageInfo['size'];
        $date['mime'] = $imageInfo['type'];
        $date['image_type'] = $imageInfo['image_type'];
        $date['is_exists'] = false;
        return $date;
    }

    //获取即将要下载的图片扩展名
    public function getImageExtname($url = '', $ex = 'jpg')
    {
        $_empty = ['file_name' => '', 'ext_name' => $ex];
        if (!$url) return $_empty;
        if (strpos($url, '?')) {
            $_tarr = explode('?', $url);
            $url = trim($_tarr[0]);
        }
        $arr = explode('.', $url);
        if (!is_array($arr) || count($arr) <= 1) return $_empty;
        $ext_name = trim($arr[count($arr) - 1]);
        $ext_name = !$ext_name ? $ex : $ext_name;
        return ['file_name' => md5($url) . '.' . $ext_name, 'ext_name' => $ext_name];
    }

    /*
      $filepath = 绝对路径，末尾有斜杠 /
      $name = 图片文件名
      $maxwidth 定义生成图片的最大宽度（单位：像素）
      $maxheight 生成图片的最大高度（单位：像素）
      $filetype 最终生成的图片类型（.jpg/.png/.gif）
    */
    public function resizeImage($filepath = '', $name = '', $maxwidth = 0, $maxheight = 0)
    {
        $pic_file = $filepath . $name; //图片文件
        $img_info = getimagesize($pic_file); //索引 2 是图像类型的标记：1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，
        if ($img_info[2] == 1) {
            $im = imagecreatefromgif($pic_file); //打开图片
            $filetype = '.gif';
        } elseif ($img_info[2] == 2) {
            $im = imagecreatefromjpeg($pic_file); //打开图片
            $filetype = '.jpg';
        } elseif ($img_info[2] == 3) {
            $im = imagecreatefrompng($pic_file); //打开图片
            $filetype = '.png';
        } else {
            return ['path' => $filepath, 'file' => $name, 'mime' => ''];
        }
        $file_name = md5('_tmp_' . microtime() . '_' . rand(0, 10)) . $filetype;
        $pic_width = imagesx($im);
        $pic_height = imagesy($im);
        $resizewidth_tag = false;
        $resizeheight_tag = false;
        if (($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight)) {
            if ($maxwidth && $pic_width > $maxwidth) {
                $widthratio = $maxwidth / $pic_width;
                $resizewidth_tag = true;
            }
            if ($maxheight && $pic_height > $maxheight) {
                $heightratio = $maxheight / $pic_height;
                $resizeheight_tag = true;
            }
            if ($resizewidth_tag && $resizeheight_tag) {
                if ($widthratio < $heightratio)
                    $ratio = $widthratio;
                else
                    $ratio = $heightratio;
            }
            if ($resizewidth_tag && !$resizeheight_tag)
                $ratio = $widthratio;
            if ($resizeheight_tag && !$resizewidth_tag)
                $ratio = $heightratio;
            $newwidth = $pic_width * $ratio;
            $newheight = $pic_height * $ratio;
            if (function_exists("imagecopyresampled")) {
                $newim = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
            } else {
                $newim = imagecreate($newwidth, $newheight);
                imagecopyresized($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
            }
            if ($filetype == '.png') {
                imagepng($newim, $filepath . $file_name);
            } else if ($filetype == '.gif') {
                imagegif($newim, $filepath . $file_name);
            } else {
                imagejpeg($newim, $filepath . $file_name);
            }
            imagedestroy($newim);
        } else {
            if ($filetype == '.png') {
                imagepng($im, $filepath . $file_name);
            } else if ($filetype == '.gif') {
                imagegif($im, $filepath . $file_name);
            } else {
                imagejpeg($im, $filepath . $file_name);
            }
            imagedestroy($im);
        }
        @unlink($pic_file);
        return ['path' => $filepath, 'file' => $file_name, 'mime' => $img_info['mime']];
    }
}
