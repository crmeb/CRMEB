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
declare(strict_types=1);

namespace app\services\ai;

use app\dao\ai\AiModelDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\ai\AiInterface;
use crmeb\services\ai\AiProtocol;
use crmeb\services\ai\EmbeddingAiInterface;
use crmeb\utils\Http;
use crmeb\utils\SsrfGuard;
/**
 * AI 模型管理服务（系统内置 AI 能力）
 *
 * 职责：模型登记的增删改查 + 把模型记录解析为协议适配器可用的配置，
 * 协议实现本身在 crmeb\services\ai\protocol 下，不感知数据表。
 */
class AiModelServices extends BaseService
{
    /**
     * 密钥占位符：更新时回传该值表示保持原密钥不变
     */
    const SECRET_KEEP = '__KEEP__';

    /**
     * 系统内置默认协议：一号通 AI
     *
     * 凭证来自「一号通登录/设置」写入的全局系统配置，无需在 eb_ai_model 登记模型记录，
     * 因此未显式指定模型的对话接口统一以此协议兜底。
     */
    const DEFAULT_PROTOCOL = 'yihaotong';

    /**
     * @var AiModelDao
     */
    protected $dao;

    public function __construct(AiModelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 模型列表（分页）
     * @param array $where 查询条件（status/protocol/keyword）
     * @param int $page 页码
     * @param int $limit 每页条数
     * @return array ['list' => array, 'count' => int]
     */
    public function getList(array $where = [], int $page = 1, int $limit = 20): array
    {
        $count = $this->dao->count($where);
        $list = $this->dao->selectList($where, '*', $page, $limit, 'sort desc,id asc', [], true)->toArray();
        foreach ($list as &$item) {
            $item = $this->formatModel($item);
        }
        unset($item);
        // 内置一号通 AI 的配置状态随列表下发，供页面提示「未配置，请一键配置」
        return ['list' => $list, 'count' => $count, 'yihaotong_configured' => $this->isYihaotongConfigured() ? 1 : 0];
    }

    /**
     * 模型详情
     * @param int $id 模型ID
     * @return array
     */
    public function getDetail(int $id): array
    {
        return $this->formatModel($this->dao->findById($id), true);
    }

    /**
     * 新增模型
     * @param array $data 表单数据
     * @return int 模型ID
     */
    public function save(array $data): int
    {
        $payload = $this->buildData($data);
        $payload['is_default'] = (int) ($payload['is_default'] ?? 0);
        if ($payload['is_default'] === 1 && (int) ($payload['status'] ?? 1) === 1) {
            $payload['is_default'] = 0;
            $id = (int) $this->dao->save($payload)->id;
            $this->setDefault($id);
            return $id;
        }
        return (int) $this->dao->save($payload)->id;
    }

    /**
     * 修改模型
     * @param int $id 模型ID
     * @param array $data 表单数据
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $old = $this->dao->findById($id);
        if (!$old) {
            throw new AdminException('AI 模型不存在');
        }

        $payload = $this->buildData($data, $id);
        // 密钥留空或回传占位符时保持原值，避免编辑表单必须重填密钥
        if (($payload['api_key'] ?? '') === '' || $payload['api_key'] === self::SECRET_KEEP) {
            $payload['api_key'] = (string) ($old['api_key'] ?? '');
        }

        $this->dao->update($id, $payload);
        if ((int) ($payload['is_default'] ?? 0) === 1) {
            $this->setDefault($id);
        }
        return true;
    }

    /**
     * 删除模型
     * @param int $id 模型ID
     * @return bool
     */
    public function delete(int $id): bool
    {
        if (!$this->dao->findById($id)) {
            throw new AdminException('AI 模型不存在');
        }
        $this->dao->delete($id);
        return true;
    }

    /**
     * 修改模型状态
     * @param int $id 模型ID
     * @param int $status 状态（0=停用 1=启用）
     * @return bool
     */
    public function setStatus(int $id, int $status): bool
    {
        if (!$this->dao->findById($id)) {
            throw new AdminException('AI 模型不存在');
        }
        $this->dao->update($id, $status === 0 ? ['status' => 0, 'is_default' => 0] : ['status' => 1]);
        return true;
    }

    /**
     * 设为系统默认模型
     * @param int $id 模型ID
     * @return bool
     */
    public function setDefault(int $id): bool
    {
        // id = 0 表示内置一号通模型：清空全部登记默认，一号通即成为系统默认
        if ($id === 0) {
            $this->dao->clearDefaultExcept(0);
            return true;
        }
        $model = $this->dao->findById($id);
        if (!$model) {
            throw new AdminException('AI 模型不存在');
        }
        if ((int) $model['status'] !== 1) {
            throw new AdminException('请先启用该模型再设为默认');
        }
        $this->dao->clearDefaultExcept($id);
        $this->dao->update($id, ['is_default' => 1]);
        return true;
    }

    /**
     * 批量排序
     * @param array $sortData [['id' => 1, 'sort' => 10], ...]
     * @return bool
     */
    public function setSort(array $sortData): bool
    {
        foreach ($sortData as $item) {
            if (isset($item['id']) && isset($item['sort'])) {
                $this->dao->update((int) $item['id'], ['sort' => (int) $item['sort']]);
            }
        }
        return true;
    }

    /**
     * 模型连通性测试：按表单配置构造协议适配器发起一次真实请求（不落库）
     *
     * 按模型能力分流：仅勾选「支持文本向量化」的纯向量模型没有对话端点，
     * 改用 /embeddings 端点真实测试，其余模型走对话接口。
     * @param array $data 表单数据（与保存表单一致，编辑时需携带 id 以继承原密钥）
     * @return array [ok=>1|0, cost_ms=>int, message=>string]
     */
    public function testModel(array $data): array
    {
        $id = (int) ($data['id'] ?? 0);
        $old = $id > 0 ? $this->dao->findById($id) : null;
        if ($old) {
            $data = array_merge($old, $data);
            // 编辑场景密钥回传占位符或留空时，使用原密钥真实测试
            if (in_array(trim((string) ($data['api_key'] ?? '')), ['', self::SECRET_KEEP], true)) {
                $data['api_key'] = (string) $old['api_key'];
            }
        }
        // 名称仅用于展示，测试场景不参与查重
        $data['name'] = trim((string) ($data['name'] ?? '')) ?: ((string) ($data['model'] ?? ''));
        $payload = $this->buildData($data, $id);

        $driver = AiProtocol::make($payload['protocol'], $this->buildConfig($payload));
        // 纯向量模型：仅勾选向量化、未勾选工具调用/视觉（向量模型调 /chat/completions 会被厂商直接拒绝）
        $embeddingOnly = (int) ($payload['supports_embedding'] ?? 0) === 1
            && (int) ($payload['supports_tools'] ?? 0) !== 1
            && (int) ($payload['supports_vision'] ?? 0) !== 1;
        $start = microtime(true);
        try {
            if ($embeddingOnly) {
                if (!$driver instanceof EmbeddingAiInterface) {
                    throw new \RuntimeException('该模型协议不支持文本向量化，请检查模型能力配置');
                }
                $driver->embeddings(['连通性测试']);
            } else {
                $result = $driver->chat('连通性测试，请直接回复：ok', ['stream' => 0]);
            }
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            // 向量模型因未勾选向量化能力走了对话端点被厂商拒绝时，给出可操作的勾选建议
            if (!$embeddingOnly
                && (int) ($payload['supports_embedding'] ?? 0) !== 1
                && str_contains(strtolower((string) ($payload['model'] ?? '')), 'embedding')) {
                $message .= '；检测到模型 ID 含有「embedding」，请先在高级配置勾选「支持文本向量化」再测试';
            }
            return [
                'ok'      => 0,
                'cost_ms' => (int) round((microtime(true) - $start) * 1000),
                'message' => '连通性测试失败：' . $message,
            ];
        }
        $costMs = (int) round((microtime(true) - $start) * 1000);
        if ($embeddingOnly) {
            return [
                'ok'      => 1,
                'cost_ms' => $costMs,
                'message' => '连通性测试成功，向量接口正常响应（耗时 ' . $costMs . 'ms）',
            ];
        }
        $content = trim((string) ($result['content'] ?? ''));
        return [
            'ok'      => 1,
            'cost_ms' => $costMs,
            'message' => $content !== ''
                ? '连通性测试成功，模型已正常响应（耗时 ' . $costMs . 'ms）'
                : '连通性测试通过，但模型未返回内容，请检查模型配置',
        ];
    }

    /**
     * 拉取厂商可用模型列表（OpenAI 兼容 /models 端点，不落库）
     *
     * 供新增/编辑弹窗「获取模型」按钮调用：按表单填写的接口地址与密钥请求 /models，
     * 返回模型标识列表，前端拿到后把「模型 ID」切换为下拉选择。
     * @param array $data 表单数据（base_url/api_key/full_url，编辑时需携带 id 以继承原密钥）
     * @return array 模型标识一维数组（去重升序）
     */
    public function fetchRemoteModels(array $data): array
    {
        $id = (int) ($data['id'] ?? 0);
        $old = $id > 0 ? $this->dao->findById($id) : null;
        if ($old) {
            $data = array_merge($old, $data);
            // 编辑场景密钥回传占位符或留空时，使用原密钥真实拉取
            if (in_array(trim((string) ($data['api_key'] ?? '')), ['', self::SECRET_KEEP], true)) {
                $data['api_key'] = (string) $old['api_key'];
            }
        }

        $baseUrl = trim((string) ($data['base_url'] ?? ''));
        if (!preg_match('#^https?://#i', $baseUrl)) {
            throw new AdminException('请先填写以 http:// 或 https:// 开头的接口地址');
        }
        // 完整 URL 模式没有「网关根地址」概念，无法推导 /models 端点
        if ((int) ($data['full_url'] ?? 0) === 1) {
            throw new AdminException('完整 URL 模式无法自动获取模型列表，请手动输入模型 ID');
        }
        $apiKey = trim((string) ($data['api_key'] ?? ''));
        if ($apiKey === '') {
            throw new AdminException('请先填写 API 密钥');
        }
        // 出站 SSRF 防护：与模型保存同口径（「允许内网 AI 服务」开关打开时放行内网自建模型）
        try {
            SsrfGuard::assertSafeUrl($baseUrl, $this->privateEndpointAllowed() ? SsrfGuard::MODE_SELFCALL : SsrfGuard::MODE_STRICT);
        } catch (\InvalidArgumentException $e) {
            throw new AdminException('接口地址不安全：' . $e->getMessage());
        }

        // 地址可能被填成对话端点（…/chat/completions），先剥掉再拼 /models
        if (str_ends_with($baseUrl, '/chat/completions')) {
            $baseUrl = substr($baseUrl, 0, -strlen('/chat/completions'));
        }
        $url = rtrim($baseUrl, '/') . '/models';

        // 原生 curl 而非 Http::getRequest：4xx（密钥错误等）响应体里有厂商的明确错误信息，需要取出来提示
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey, 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new AdminException('获取模型列表失败：' . ($curlError ?: '网络异常，请稍后重试'));
        }
        $result = json_decode((string) $body, true);
        if (!is_array($result)) {
            throw new AdminException($httpCode > 0 ? '获取模型列表失败：HTTP ' . $httpCode : '获取模型列表失败：响应解析失败');
        }
        if ($httpCode >= 400 || isset($result['error'])) {
            throw new AdminException('获取模型列表失败：' . $this->remoteModelsErrorMessage($result, $httpCode));
        }
        $models = [];
        foreach ((array) ($result['data'] ?? []) as $item) {
            $modelId = trim((string) ($item['id'] ?? ''));
            if ($modelId !== '') {
                $models[] = $modelId;
            }
        }
        $models = array_values(array_unique($models));
        sort($models);
        return $models;
    }

    /**
     * 从拉取模型列表的错误响应中提取可读消息
     * @param array $result 已解析的 JSON 响应
     * @param int $httpCode HTTP 状态码
     * @return string
     */
    protected function remoteModelsErrorMessage(array $result, int $httpCode): string
    {
        $error = $result['error'] ?? null;
        $message = is_array($error) ? (string) ($error['message'] ?? '') : (string) $error;
        if ($message === '') {
            $message = (string) ($result['message'] ?? ($result['msg'] ?? ''));
        }
        if ($message === '') {
            $message = 'HTTP ' . ($httpCode > 0 ? $httpCode : '请求异常');
        }
        return mb_substr(trim($message), 0, 300);
    }

    /**
     * 可用模型清单（下拉选择用，不含密钥）
     * @param bool $onlyEnabled 仅返回启用中的模型
     * @return array
     */
    public function getAvailableModels(bool $onlyEnabled = true): array
    {
        $list = $onlyEnabled ? $this->dao->getAllEnabled() : $this->dao->selectList([], '*', 0, 0, 'sort desc,id asc')->toArray();
        $result = [];
        foreach ($list as $item) {
            $result[] = [
                'id'            => (int) $item['id'],
                'name'          => (string) $item['name'],
                'protocol'      => (string) $item['protocol'],
                'protocol_name' => AiProtocol::name((string) $item['protocol']),
                'provider'      => (string) $item['provider'],
                'model'         => (string) $item['model'],
                'supports_tools' => (int) $item['supports_tools'],
                'supports_vision' => (int) $item['supports_vision'],
                'supports_embedding' => (int) ($item['supports_embedding'] ?? 0),
                'is_default'    => (int) $item['is_default'],
                'status'        => (int) $item['status'],
            ];
        }
        return $result;
    }

    /**
     * 内置协议族清单（后台表单渲染用）
     * @return array
     */
    public function getProtocols(): array
    {
        return AiProtocol::options();
    }

    /**
     * 按模型 ID 创建协议适配器实例
     * @param int $modelId 模型ID，传 0 使用系统默认模型
     * @return AiInterface
     */
    public function getHandler(int $modelId = 0): AiInterface
    {
        $model = $modelId > 0 ? $this->dao->findById($modelId) : $this->dao->getDefaultModel();
        if (!$model) {
            throw new AdminException($modelId > 0
                ? 'AI 模型不存在，请在「AI Agent → 模型管理」中检查配置'
                : '未配置默认 AI 模型，请先在「AI Agent → 模型管理」中新增并启用一个模型');
        }
        if ((int) $model['status'] !== 1) {
            throw new AdminException('AI 模型[' . $model['name'] . ']已停用，请在「AI Agent → 模型管理」中启用');
        }
        return AiProtocol::make((string) $model['protocol'], $this->buildConfig($model));
    }

    /**
     * 对话接口用适配器解析：显式指定模型走登记模型，缺省走内置一号通 AI
     *
     * 与 getHandler 的差异：本方法在 model_id 为 0 时不依赖「系统默认模型」，
     * 直接使用一号通全局凭证，保证后台内置 AI 能力（如编辑器 AI 写作）开箱可用。
     * @param int $modelId 模型ID，传 0 使用内置一号通 AI
     * @return AiInterface
     */
    public function getChatHandler(int $modelId = 0): AiInterface
    {
        return $modelId > 0 ? $this->getHandler($modelId) : $this->getYihaotongHandler();
    }

    /**
     * 向量模型适配器解析：显式指定模型走登记模型（须勾选向量化能力），缺省走一号通全局凭证
     *
     * 与 getChatHandler 同口径：model_id 为 0 时不依赖登记记录，
     * 直接使用一号通全局凭证 + 平台默认向量模型，保证向量化能力开箱可用。
     * @param int $modelId 向量模型ID，传 0 使用内置一号通向量模型
     * @return EmbeddingAiInterface
     */
    public function getEmbeddingHandler(int $modelId = 0): EmbeddingAiInterface
    {
        $handler = $modelId > 0 ? $this->getHandler($modelId) : $this->getYihaotongHandler();
        if (!$handler instanceof EmbeddingAiInterface) {
            throw new AdminException('该模型协议不支持文本向量化，请更换向量模型或在模型管理中勾选「支持文本向量化」');
        }
        if ($modelId > 0) {
            $model = $this->dao->findById($modelId);
            if ((int) ($model['supports_embedding'] ?? 0) !== 1) {
                throw new AdminException('模型[' . ($model['name'] ?? '') . ']未勾选「支持文本向量化」，请先在「AI Agent → 模型管理」中勾选');
            }
        }
        return $handler;
    }

    /**
     * 创建内置一号通 AI 适配器
     *
     * 一号通 appid / appsecret 由「一号通登录」写入 eb_system_config 全站共享，
     * 模型标识留空表示由一号通平台侧决定实际模型。
     * @return AiInterface
     */
    public function getYihaotongHandler(): AiInterface
    {
        return AiProtocol::make(self::DEFAULT_PROTOCOL, [
            'api_key'     => '',
            'base_url'    => '',
            'model'       => '',
            'temperature' => 0.7,
            'max_tokens'  => 2048,
        ]);
    }

    /**
     * 内置一号通 AI 是否已配置
     *
     * 与 Yihaotong 协议的凭证读取保持同一口径：全局系统配置
     * yihaotong_appid / yihaotong_appsecret 均非空视为已配置。
     * @return bool
     */
    public function isYihaotongConfigured(): bool
    {
        return sys_config('yihaotong_appid', '') !== ''
            && sys_config('yihaotong_appsecret', '') !== '';
    }

    /**
     * 模型记录 → 协议适配器配置
     * @param array $model 模型记录
     * @return array
     */
    public function buildConfig(array $model): array
    {
        return [
            'api_key'     => (string) ($model['api_key'] ?? ''),
            'base_url'    => (string) ($model['base_url'] ?? ''),
            'full_url'    => (int) ($model['full_url'] ?? 0),
            'model'       => (string) ($model['model'] ?? ''),
            'temperature' => (float) ($model['temperature'] ?? 0.7),
            'max_tokens'  => (int) ($model['max_tokens'] ?? 2048),
        ];
    }

    /**
     * 表单校验并归一化为可入库字段
     * @param array $data 表单数据
     * @param int $exceptId 编辑时排除自身ID
     * @return array
     */
    protected function buildData(array $data, int $exceptId = 0): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new AdminException('请填写模型显示名称');
        }
        if ($this->dao->countByName($name, $exceptId) > 0) {
            throw new AdminException('模型显示名称已存在');
        }

        $protocol = trim((string) ($data['protocol'] ?? ''));
        if (!AiProtocol::has($protocol)) {
            throw new AdminException('请选择正确的 AI 协议');
        }

        $model = trim((string) ($data['model'] ?? ''));
        if ($model === '') {
            throw new AdminException('请填写模型标识（如 deepseek-chat、qwen-plus）');
        }

        $baseUrl = trim((string) ($data['base_url'] ?? ''));
        $apiKey = trim((string) ($data['api_key'] ?? ''));
        if ($protocol !== 'yihaotong') {
            if ($baseUrl !== '') {
                if (!preg_match('#^https?://#i', $baseUrl)) {
                    throw new AdminException('接口地址必须以 http:// 或 https:// 开头');
                }
                // 出站 SSRF 防护：拒绝内网/元数据地址与危险端口（「允许内网 AI 服务」开关打开时放行内网自建模型）
                try {
                    SsrfGuard::assertSafeUrl($baseUrl, $this->privateEndpointAllowed() ? SsrfGuard::MODE_SELFCALL : SsrfGuard::MODE_STRICT);
                } catch (\InvalidArgumentException $e) {
                    throw new AdminException('接口地址不安全：' . $e->getMessage());
                }
            }
        } else {
            // 一号通复用系统全局凭证，不落地独立地址与密钥
            $baseUrl = '';
        }

        $temperature = (float) ($data['temperature'] ?? 0.7);
        if ($temperature < 0 || $temperature > 2) {
            throw new AdminException('采样温度取值范围为 0 ~ 2');
        }

        $status = (int) ($data['status'] ?? 1) === 0 ? 0 : 1;
        return [
            'name'           => $name,
            'protocol'       => $protocol,
            'provider'       => trim((string) ($data['provider'] ?? '')),
            'model'          => $model,
            'base_url'       => $baseUrl,
            'api_key'        => $apiKey,
            'full_url'       => (int) ($data['full_url'] ?? 0) === 1 ? 1 : 0,
            'supports_tools' => (int) ($data['supports_tools'] ?? 0) === 1 ? 1 : 0,
            'supports_vision' => (int) ($data['supports_vision'] ?? 0) === 1 ? 1 : 0,
            'supports_embedding' => (int) ($data['supports_embedding'] ?? 0) === 1 ? 1 : 0,
            'context_window' => max(0, (int) ($data['context_window'] ?? 0)),
            'max_tokens'     => max(0, (int) ($data['max_tokens'] ?? 2048)),
            'temperature'    => $temperature,
            // 停用的模型不能担任系统默认：强制取消默认标记，避免默认模型悬空不可用
            'is_default'     => $status === 1 && (int) ($data['is_default'] ?? 0) === 1 ? 1 : 0,
            'sort'           => (int) ($data['sort'] ?? 0),
            'status'         => $status,
        ];
    }

    /**
     * 是否允许出站地址指向内网（自建本地模型如 Ollama/vLLM 场景），
     * 由「AI 自动化任务 → 允许内网 AI 服务地址」系统配置控制，默认关闭
     * @return bool
     */
    protected function privateEndpointAllowed(): bool
    {
        return (int) sys_config('ai_allow_private_endpoint', 0) === 1;
    }

    /**
     * 输出格式化：脱敏密钥并补充协议名
     * @param array $item 模型记录
     * @param bool $detail 是否详情场景（详情仅给出是否已配置的布尔提示）
     * @return array
     */
    protected function formatModel(array $item, bool $detail = false): array
    {
        if (!$item) {
            return [];
        }
        $apiKey = (string) ($item['api_key'] ?? '');
        $item['protocol_name'] = AiProtocol::name((string) $item['protocol']);
        $item['has_api_key'] = $apiKey !== '' ? 1 : 0;
        $item['api_key'] = $detail ? ($apiKey !== '' ? self::SECRET_KEEP : '') : $this->maskSecret($apiKey);
        $item['temperature'] = (float) $item['temperature'];
        $item['max_tokens'] = (int) $item['max_tokens'];
        $item['context_window'] = (int) $item['context_window'];
        $item['full_url'] = (int) ($item['full_url'] ?? 0);
        $item['supports_tools'] = (int) $item['supports_tools'];
        $item['supports_vision'] = (int) $item['supports_vision'];
        $item['supports_embedding'] = (int) ($item['supports_embedding'] ?? 0);
        $item['is_default'] = (int) $item['is_default'];
        $item['status'] = (int) $item['status'];
        $item['sort'] = (int) $item['sort'];
        return $item;
    }

    /**
     * 密钥脱敏展示
     * @param string $secret 原始密钥
     * @return string
     */
    protected function maskSecret(string $secret): string
    {
        if ($secret === '') {
            return '';
        }
        $len = strlen($secret);
        if ($len <= 8) {
            return str_repeat('*', $len);
        }
        return substr($secret, 0, 4) . str_repeat('*', min(12, $len - 8)) . substr($secret, -4);
    }
}
