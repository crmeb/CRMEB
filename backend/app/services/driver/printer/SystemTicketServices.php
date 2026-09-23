<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\services\driver\printer;

use app\dao\driver\printer\SystemTicketDao;
use app\dao\driver\printer\PrinterChannelDao;
use app\dao\driver\printer\PrinterTaskDao;
use app\services\pay\PayServices;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\utils\FormBuilder as Form;
use crmeb\services\printer\Printer;
use think\facade\Log;

/**
 * 系统打印机服务
 * Class SystemTicketServices
 * @package app\services\driver\printer
 */
class SystemTicketServices extends BaseService
{
    /**
     * @var SystemTicketDao
     */
    protected $dao;
    /** @var PrinterChannelDao 打印设备查询 */
    protected $channelDao;
    /** @var PrinterDriverServices 驱动配置查询 */
    protected $driverServices;
    /** @var PrinterTaskDao 设备提交进度 */
    protected $taskDao;

    /**
     * 初始化打印配置和进度访问层
     * @param SystemTicketDao $dao 小票配置
     * @param PrinterChannelDao $channelDao 设备配置
     * @param PrinterDriverServices $driverServices 驱动配置
     * @param PrinterTaskDao $taskDao 提交进度
     */
    public function __construct(
        SystemTicketDao $dao,
        PrinterChannelDao $channelDao,
        PrinterDriverServices $driverServices,
        PrinterTaskDao $taskDao
    )
    {
        $this->dao = $dao;
        $this->channelDao = $channelDao;
        $this->driverServices = $driverServices;
        $this->taskDao = $taskDao;
    }

    public function ticketList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->ticketList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        $count = $this->dao->ticketCount($where);
        return compact('list', 'count');
    }

    public function ticketForm($id)
    {
        $info = $this->dao->get($id) ?? [];
        if ($info) $info = $info->toArray();
        $field[] = Form::input('print_name', '打印机名称', $info['print_name'] ?? '')->required('请输入打印机名称')->placeholder('打印机名称');
        $field[] = Form::radio('type', '平台选择', $info['type'] ?? 1)
            ->options([['label' => '易联云', 'value' => 1], ['label' => '飞鹅云', 'value' => 2]])
            ->appendControl(1, [
                    Form::input('yly_user_id', '用户ID：', $info['yly_user_id'] ?? '')->required('请输入用户ID')->placeholder('易联云开发者ID'),
                    Form::input('yly_app_id', '应用ID：', $info['yly_app_id'] ?? '')->required('请输入应用ID')->placeholder('易联应用ID'),
                    Form::input('yly_app_secret', '应用密钥：', $info['yly_app_secret'] ?? '')->required('请输入应用密钥')->placeholder('易联应用密钥'),
                    Form::input('yly_sn', '终端号：', $info['yly_sn'] ?? '')->required('请输入终端号')->placeholder('易联云打印机终端号，打印机型号：易联云打印机 K4无线版'),
                ]
            )->appendControl(2, [
                    Form::input('fey_user', '飞鹅云USER：', $info['fey_user'] ?? '')->required('请输入飞鹅云USER')->placeholder('飞鹅云后台注册账号'),
                    Form::input('fey_ukey', '飞鹅云UYEK：', $info['fey_ukey'] ?? '')->required('请输入飞鹅云UYEK')->placeholder('飞鹅云后台注册账号后生成的UKEY 【备注：这不是填打印机的KEY】'),
                    Form::input('fey_sn', '飞鹅云SN：', $info['fey_sn'] ?? '')->required('请输入飞鹅云SN')->placeholder('打印机标签上的编号，必须要在管理后台里添加打印机或调用API接口'),
                ]
            );
        $field[] = Form::number('times', '打印联数', $info['times'] ?? 1)->min(1)->required('请输入打印联数')->placeholder('打印机单次打印张数');
        $field[] = Form::radio('print_type', '打印时机', $info['print_type'] ?? 1)->options([['label' => '支付后打印', 'value' => 1], ['label' => '下单后打印', 'value' => 2]]);
        $field[] = Form::radio('status', '打印开关', $info['status'] ?? 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return create_form('小票打印', $field, $this->url('/system/ticket/save/' . $id), 'POST');
    }

    public function ticketSave($id, $data)
    {
        if ($id) {
            $this->dao->update($id, $data);
        } else {
            $data['add_time'] = time();
            $this->dao->save($data);
        }
        return true;
    }

    public function ticketSetStatus($id, $status)
    {
        $this->dao->update($id, ['status' => $status]);
        return true;
    }

    public function ticketDel($id)
    {
        $this->dao->update($id, ['is_del' => 1]);
        return true;
    }

    public function ticketContent($id)
    {
        $print_content = $this->dao->value(['id' => $id], 'print_content');
        return $print_content ? json_decode($print_content, true) : [];
    }

    public function ticketContentSave($id, $data)
    {
        $print_content = json_encode($data);
        $this->dao->update($id, ['print_content' => $print_content]);
        return true;
    }

    /**
     * 提交小票：自动任务按订单与时机去重，手动打印使用独立任务
     * @param array $order 模板订单字段
     * @param array $product 商品快照
     * @param int|bool $print_type 1支付后、2下单后、true手动打印全部启用设备
     * @param string|null $requestId 可选的稳定调度标识
     * @return int 已提交的设备数
     */
    public function startPrint($order, $product, $print_type = 1, ?string $requestId = null): int
    {
        $orderNo = (string)($order['order_id'] ?? '');
        if ($orderNo === '' || !in_array($print_type, [1, 2, true], true)) {
            throw new \RuntimeException('小票订单号或打印时机无效');
        }
        $scene = $print_type === true ? 'manual' : ($print_type === 1 ? 'paid' : 'created');
        $requestId ??= $scene === 'manual' ? bin2hex(random_bytes(16)) : $orderNo . ':' . $scene;
        if ($requestId === '') throw new \RuntimeException('小票调度标识不能为空');
        $where = ['status' => 1, 'is_del' => 0];
        if ($print_type !== true) $where['print_type'] = $print_type;
        $list = $this->channelDao->selectList($where, 'id,driver,printer_id,times,print_content', 0, 0, 'sort desc,id asc')->toArray();
        if (!$list) return 0;

        // 同一驱动的凭据只读取一次，未启用的插件不发送请求。
        $drivers = [];
        foreach (array_unique(array_column($list, 'driver')) as $name) {
            $drivers[$name] = $this->driverServices->getEnabledDriverByKey($name);
        }
        $list = array_values(array_filter($list, fn ($item) => !empty($drivers[$item['driver']])));
        if (!$list) return 0;
        $tasks = [];
        $now = time();
        foreach ($list as &$item) {
            $item['task_key'] = hash('sha256', $orderNo . "\0" . $scene . "\0" . $requestId . "\0" . $item['id']);
            $tasks[] = ['task_key' => $item['task_key'], 'order_no' => $orderNo, 'channel_id' => $item['id'],
                'scene' => $scene, 'status' => 'pending', 'add_time' => $now, 'update_time' => $now];
        }
        unset($item);
        $this->taskDao->prepareTasks($tasks);
        $states = $this->taskDao->getTaskStates(array_column($tasks, 'task_key'), $now);
        $order['pay_type_name'] ??= PayServices::getPayType()[$order['pay_type'] ?? ''] ?? ($order['pay_type'] ?? '暂无');
        $submittedCount = 0;
        $failedChannels = [];
        foreach ($list as $item) {
            $key = $item['task_key'];
            if (($states[$key] ?? '') === 'submitted') {
                $submittedCount++;
                continue;
            }
            $token = bin2hex(random_bytes(16));
            if (!$this->taskDao->claimTask($key, $token, time())) {
                $failedChannels[] = $item['id'];
                continue;
            }
            $printer = null;
            $submitted = false;
            try {
                $name = $item['driver'];
                if (!in_array($name, ['YiLianYun', 'FeiEYun'], true)) throw new \RuntimeException('不支持的打印驱动');
                $printerId = trim((string)($item['printer_id'] ?? ''));
                if ($printerId === '') throw new \RuntimeException('打印机设备ID为空');
                $copies = $item['times'] ?? null;
                if (!(is_int($copies) || (is_string($copies) && ctype_digit($copies)))
                    || (int)$copies < 1 || (int)$copies > 99) throw new \RuntimeException('打印联数无效');
                $times = (int)$copies;
                $values = array_column($drivers[$name]['config_fields'] ?? [], 'value', 'field');
                $printContent = $item['print_content'] ?? null;
                if (is_string($printContent)) {
                    $printContent = $printContent === '' ? null : json_decode($printContent, true, 512, JSON_THROW_ON_ERROR);
                }
                if ($printContent !== null && !is_array($printContent)) throw new \RuntimeException('打印模板无效');
                $printContent = PrinterChannelServices::normalizePrintContent($printContent ?: []);
                // 手动打印是当前订单重印，不是下单时打印。
                $templateType = $print_type === true ? 1 : $print_type;
                if ($name === 'YiLianYun') {
                    $configData = ['partner' => $values['yly_user_id'] ?? '', 'clientId' => $values['yly_app_id'] ?? '',
                        'apiKey' => $values['yly_app_secret'] ?? '', 'terminal' => $printerId];
                    $content = $this->ylyContent($printContent, $order, $product, $times, $templateType);
                } else {
                    $configData = ['feyUser' => $values['fey_user'] ?? '', 'feyUkey' => $values['fey_ukey'] ?? '', 'feySn' => $printerId];
                    $content = $this->feyContent($printContent, $order, $product, $templateType);
                }
                $printer = $this->createPrinter($name, $configData);
                $printer->setJobId($key);
                $result = $printer->setPrinterContent($content, $times)->startPrinter();
                if (!is_array($result)) throw new \RuntimeException('云打印提交失败');
                $submitted = true;
                $providerJobId = (string)($name === 'YiLianYun' ? ($result['body']['id'] ?? '') : ($result['data'] ?? ''));
                // 每台设备接单后立即落库，不能等整批结束，否则下一台失败会重打上一台。
                $this->taskDao->finishTask($key, $token, 'submitted', '', $providerJobId);
                $submittedCount++;
            } catch (\Throwable $e) {
                $uncertain = $submitted || ($printer && $printer->isSubmissionUncertain());
                $error = $uncertain ? '提交结果待确认，已暂停自动重发' : ($printer ? ($printer->getError() ?: '设备提交失败，等待重试') : '设备提交失败，等待重试');
                try {
                    $this->taskDao->finishTask($key, $token, $uncertain ? 'uncertain' : 'failed', $error);
                } catch (\Throwable $persistError) {
                    // 保留租约，过期后转为待确认，禁止因保存失败而重复提交。
                    Log::error('[Printer] 小票进度保存失败: task=' . $key);
                }
                Log::error('[Printer] 设备提交失败: channel_id=' . $item['id'] . '; task=' . $key . '; ' . $error);
                $failedChannels[] = $item['id'];
            }
        }
        if ($failedChannels) {
            throw new \RuntimeException('小票提交未完成，失败或待确认设备ID: ' . implode(',', $failedChannels));
        }
        return $submittedCount;
    }

    /**
     * 创建打印驱动，便于隔离云端边界进行回归验证
     * @param string $name 驱动名
     * @param array $config 配置
     * @return Printer
     */
    protected function createPrinter(string $name, array $config): Printer
    {
        return new Printer($name, $config);
    }

    /**
     * 格式化中台地址快照，不输出邮编或未知结构字段
     * @param mixed $address 地址字符串或结构化快照
     * @return string
     */
    public static function formatAddress($address): string
    {
        if (is_string($address)) return $address;
        if (!is_array($address)) return '';
        $parts = [];
        foreach (['province', 'city', 'district', 'detail'] as $field) {
            if (is_scalar($address[$field] ?? null) && trim((string)$address[$field]) !== '') $parts[] = trim((string)$address[$field]);
        }
        return implode(' ', $parts);
    }

    /**
     * 解析二维码地址，绝对地址不追加站点前缀
     * @param string $url 二维码地址
     * @return string
     */
    public static function resolveCodeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '//')) return '';
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme !== null && $scheme !== false) {
            return in_array(strtolower($scheme), ['http', 'https'], true) && parse_url($url, PHP_URL_HOST) ? $url : '';
        }
        $siteUrl = rtrim((string)sys_config('site_url'), '/');
        return in_array(strtolower((string)parse_url($siteUrl, PHP_URL_SCHEME)), ['http', 'https'], true)
            && parse_url($siteUrl, PHP_URL_HOST) ? $siteUrl . '/' . ltrim($url, '/') : '';
    }

    /**
     * 从订单快照或统一配送配置读取名称
     * @param array $order 模板订单字段
     * @return string
     */
    protected function shippingName(array $order): string
    {
        return (string)($order['shipping_name'] ?? config('printer.shipping_types', [])[$order['shipping_type'] ?? 0] ?? '暂无');
    }

    /**
     * 动态读取支付方式名称，允许模块传入已付积分等专属文案
     * @param array $order 模板订单字段
     * @param int $printType 打印时机
     * @return string
     */
    protected function paymentName(array $order, int $printType): string
    {
        if ($printType !== 1) return '暂无';
        return (string)($order['pay_type_name'] ?? PayServices::getPayType()[$order['pay_type'] ?? ''] ?? (($order['pay_type'] ?? '') ?: '暂无'));
    }

    /**
     * 渲染易联云小票
     * @param array $printContent 模板设置
     * @param array $orderInfo 订单字段
     * @param array $product 商品快照
     * @param int $times 打印联数
     * @param int $print_type 打印时机
     * @return string
     */
    public function ylyContent($printContent, $orderInfo, $product, $times, $print_type)
    {
        $printContent = PrinterChannelServices::normalizePrintContent($printContent);
        $orderInfo['user_address'] = self::formatAddress($orderInfo['user_address'] ?? '');
        $unit = $orderInfo['amount_unit'] ?? '元';
        $goodsStr = '<table><tr><td>名称</td><td>单价</td><td>数量</td><td>金额</td></tr>';
        foreach ($product as $item) {
            $goodsStr .= '<tr><td><FH2><FW2>----------------</FW2></FH2></td></tr>';
            $goodsStr .= '<tr>';
            $price = $item['sum_price'];
            $num = $item['cart_num'];
            $prices = bcmul((string)$item['cart_num'], (string)$item['sum_price'], 2);
            $goodsStr .= "<td>{$item['productInfo']['store_name']} | {$item['productInfo']['attrInfo']['suk']}</td><td>{$price}</td><td>{$num}</td><td>{$prices}</td>";
            $goodsStr .= '</tr>';
            if (in_array(1, $printContent['goods'])) {
                $goodsStr .= '<tr>';
                $goodsStr .= "<td>规格编码：{$item['productInfo']['attrInfo']['bar_code']}</td>";
                $goodsStr .= '</tr>';
            }
            unset($price, $num, $prices);
        }
        $goodsStr .= '</table>';
        $total_price = bcadd($orderInfo['total_price'], $orderInfo['pay_postage'], 2);
        $addTime = date('Y-m-d H:i:s', $orderInfo['add_time']);
        $payTime = $print_type === 1 && ($orderInfo['pay_time'] ?? 0) > 0 ? date('Y-m-d H:i:s', $orderInfo['pay_time']) : '';
        $printTime = date('Y-m-d H:i:s', time());

        $content = '';
        $content .= '<MN>' . $times . '</MN>';
        if ($printContent['header']) {
            $content .= '<FS2><center>' . sys_config('site_name') . '</center></FS2>';
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if ($printContent['delivery']) {
            $content .= '配送方式：' . $this->shippingName($orderInfo) . ' \r';
            $content .= '客户姓名: ' . $orderInfo['real_name'] . ' \r';
            $content .= '客户电话: ' . $orderInfo['user_phone'] . ' \r';
            if ($orderInfo['shipping_type'] != 2 && $orderInfo['user_address'] !== '') $content .= '收货地址: ' . $orderInfo['user_address'] . ' \r';
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if ($printContent['buyer_remarks']) {
            $content .= '买家备注: ' . $orderInfo['mark'] . ' \r';
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if (in_array(0, $printContent['goods'])) {
            $content .= '*************商品***************';
            $content .= '      \r';
            $content .= $goodsStr;
            $content .= '********************************\r';
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
            $content .= '<RA>合计：' . $total_price . $unit . '</RA>';
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if ($printContent['preferential'] || $printContent['freight']) {
            if ($printContent['freight']) {
                $content .= '<RA>邮费：' . $orderInfo['pay_postage'] . $unit . '</RA>';
            }
            if ($printContent['preferential']) {
                $discount_price = bcsub(bcadd($orderInfo['total_price'], $orderInfo['pay_postage'], 2), bcadd($orderInfo['deduction_price'], $orderInfo['pay_price'], 2), 2);
                $content .= '<RA>优惠：-' . $discount_price . $unit . '</RA>';
                $content .= '<RA>抵扣：-' . $orderInfo['deduction_price'] . $unit . '</RA>';
            }
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if (in_array(0, $printContent['pay'])) {
            $content .= '<RA>支付方式：' . $this->paymentName($orderInfo, $print_type) . '</RA>';
        }
        if (in_array(1, $printContent['pay'])) {
            $content .= '<RA>实际支付：' . $orderInfo['pay_price'] . $unit . '</RA>';
        }
        if (count($printContent['pay'])) {
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if (in_array(0, $printContent['order'])) {
            $content .= '订单编号：' . $orderInfo['order_id'] . '\r';
        }
        if (in_array(1, $printContent['order'])) {
            $content .= '下单时间：' . $addTime . '\r';
        }
        if (in_array(2, $printContent['order']) && $payTime !== '') {
            $content .= '支付时间：' . $payTime . '\r';
        }
        if (in_array(3, $printContent['order'])) {
            $content .= '打印时间：' . $printTime . '\r';
        }
        if (count($printContent['order'])) {
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        $codeUrl = self::resolveCodeUrl($printContent['code_url']);
        if ($printContent['code'] && $codeUrl !== '') {
            $content .= '<QR>' . $codeUrl . '</QR>';
            $content .= '      \r';
        }
        if ($printContent['show_notice']) {
            $content .= '<center>' . $printContent['notice_content'] . '</center>';
            $content .= '      \r';
        }
        return $content;
    }

    /**
     * 渲染飞鹅云小票
     * @param array $printContent 模板设置
     * @param array $orderInfo 订单字段
     * @param array $product 商品快照
     * @param int $print_type 打印时机
     * @return string
     */
    public function feyContent($printContent, $orderInfo, $product, $print_type)
    {
        $printContent = PrinterChannelServices::normalizePrintContent($printContent);
        $orderInfo['user_address'] = self::formatAddress($orderInfo['user_address'] ?? '');
        $unit = $orderInfo['amount_unit'] ?? '元';
        $printTime = date('Y-m-d H:i:s', time());
        $addTime = date('Y-m-d H:i:s', $orderInfo['add_time']);
        $payTime = $print_type === 1 && ($orderInfo['pay_time'] ?? 0) > 0 ? date('Y-m-d H:i:s', $orderInfo['pay_time']) : '';
        $content = '';
        if ($printContent['header']) {
            $content .= '<CB>' . sys_config('site_name') . '</CB><BR>';
            $content .= '--------------------------------<BR>';
        }
        if ($printContent['delivery']) {
            $content .= '配送方式：' . $this->shippingName($orderInfo) . '<BR>';
            $content .= '客户姓名: ' . $orderInfo['real_name'] . '<BR>';
            $content .= '客户电话: ' . $orderInfo['user_phone'] . '<BR>';
            if ($orderInfo['shipping_type'] != 2 && $orderInfo['user_address'] !== '') $content .= '收货地址：' . $orderInfo['user_address'] . '<BR>';
            $content .= '--------------------------------<BR>';
        }
        if ($printContent['buyer_remarks']) {
            $content .= '买家备注：' . $orderInfo['mark'] . '<BR>';
            $content .= '--------------------------------<BR>';
        }
        if (in_array(0, $printContent['goods'])) {
            $content .= '<BR>';
            $content .= '**************商品**************<BR>';
            $content .= '<BR>';
            $content .= '名称           单价  数量 金额<BR>';
            foreach ($product as $item) {
                $content .= '--------------------------------<BR>';
                $name = $item['productInfo']['store_name'] . " | " . $item['productInfo']['attrInfo']['suk'];
                $price = $item['sum_price'];
                $num = $item['cart_num'];
                $prices = bcmul((string)$item['cart_num'], (string)$item['sum_price'], 2);
                $kw3 = '';
                $kw1 = '';
                $kw2 = '';
                $kw4 = '';
                $str = $name;
                $blankNum = 14;//名称控制为14个字节
                $lan = mb_strlen($str, 'utf-8');
                $m = 0;
                $j = 1;
                $blankNum++;
                $result = array();
                if (strlen($price) < 6) {
                    $k1 = 6 - strlen($price);
                    for ($q = 0; $q < $k1; $q++) {
                        $kw1 .= ' ';
                    }
                    $price = $price . $kw1;
                }
                if (strlen($num) < 3) {
                    $k2 = 3 - strlen($num);
                    for ($q = 0; $q < $k2; $q++) {
                        $kw2 .= ' ';
                    }
                    $num = $num . $kw2;
                }
                if (strlen($prices) < 6) {
                    $k3 = 6 - strlen($prices);
                    for ($q = 0; $q < $k3; $q++) {
                        $kw4 .= ' ';
                    }
                    $prices = $prices . $kw4;
                }
                for ($i = 0; $i < $lan; $i++) {
                    $new = mb_substr($str, $m, $j, 'utf-8');
                    $j++;
                    if (mb_strwidth($new, 'utf-8') < $blankNum) {
                        if ($m + $j > $lan) {
                            $m = $m + $j;
                            $tail = $new;
                            $lenght = iconv("UTF-8", "GBK//IGNORE", $new);
                            $k = 14 - strlen($lenght);
                            for ($q = 0; $q < $k; $q++) {
                                $kw3 .= ' ';
                            }
                            if ($m == $j) {
                                $tail .= $kw3 . ' ' . $price . ' ' . $num . ' ' . $prices;
                            } else {
                                $tail .= $kw3 . '<BR>';
                            }
                            break;
                        } else {
                            $next_new = mb_substr($str, $m, $j, 'utf-8');
                            if (mb_strwidth($next_new, 'utf-8') < $blankNum) {
                                continue;
                            } else {
                                $m = $i + 1;
                                $result[] = $new;
                                $j = 1;
                            }
                        }
                    }
                }
                $head = '';
                foreach ($result as $key => $value) {
                    if ($key < 1) {
                        $v_lenght = iconv("UTF-8", "GBK//IGNORE", $value);
                        $v_lenght = strlen($v_lenght);
                        if ($v_lenght == 13) $value = $value . " ";
                        $head .= $value . ' ' . $price . ' ' . $num . ' ' . $prices;
                    } else {
                        $head .= $value . '<BR>';
                    }
                }
                $content .= $head . $tail;
                if (in_array(1, $printContent['goods'])) {
                    $content .= '规格编码：' . $item['productInfo']['attrInfo']['bar_code'] . '<BR>';
                }
                unset($price);
            }
            $content .= '<BR>';
            $content .= '********************************<BR>';
            $content .= '<BR>';
            $content .= '--------------------------------<BR>';
            $total_price = bcadd($orderInfo['total_price'], $orderInfo['pay_postage'], 2);
            $content .= '<RIGHT>合计：' . number_format($total_price, 2) . $unit . '</RIGHT>';
            $content .= '--------------------------------<BR>';
        }
        if ($printContent['preferential'] || $printContent['freight']) {
            if ($printContent['freight']) {
                $content .= '<RIGHT>邮费：' . number_format($orderInfo['pay_postage'], 2) . $unit . '</RIGHT><BR>';
            }
            if ($printContent['preferential']) {
                $discount_price = bcsub(bcadd($orderInfo['total_price'], $orderInfo['pay_postage'], 2), bcadd($orderInfo['deduction_price'], $orderInfo['pay_price'], 2), 2);
                $content .= '<RIGHT>优惠：-' . number_format($discount_price, 2) . $unit . '</RIGHT><BR>';
                $content .= '<RIGHT>抵扣：-' . number_format($orderInfo['deduction_price'], 2) . $unit . '</RIGHT>';
            }
            $content .= '--------------------------------<BR>';
        }
        if (in_array(0, $printContent['pay'])) {
            $content .= '<RIGHT>支付方式：' . $this->paymentName($orderInfo, $print_type) . '</RIGHT><BR>';
        }
        if (in_array(1, $printContent['pay'])) {
            $content .= '<RIGHT>实际支付：' . number_format($orderInfo['pay_price'], 2) . $unit . '</RIGHT>';
        }
        if (count($printContent['pay'])) {
            $content .= '--------------------------------<BR>';
        }
        if (in_array(0, $printContent['order'])) {
            $content .= '订单编号：' . $orderInfo['order_id'] . '<BR>';
        }
        if (in_array(1, $printContent['order'])) {
            $content .= '下单时间: ' . $addTime . '<BR>';
        }
        if (in_array(2, $printContent['order']) && $payTime !== '') {
            $content .= '付款时间: ' . $payTime . '<BR>';
        }
        if (in_array(3, $printContent['order'])) {
            $content .= '打印时间: ' . $printTime . '<BR>';
        }
        if (count($printContent['order'])) {
            $content .= '--------------------------------<BR>';
            $content .= '<BR>';
        }
        $codeUrl = self::resolveCodeUrl($printContent['code_url']);
        if ($printContent['code'] && $codeUrl !== '') {
            $content .= '<QR>' . $codeUrl . '</QR>';
        }
        if ($printContent['show_notice']) {
            $content .= '<C>' . $printContent['notice_content'] . '</C>';
        }
        return $content;
    }
}
