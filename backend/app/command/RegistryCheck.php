<?php
namespace app\command;

use crmeb\trade\mode\TradeModeHandlerInterface;
use crmeb\trade\mode\TradeModeRegistry;
use crmeb\trade\order\aftersale\AfterSaleRuleRegistry;
use crmeb\trade\order\type\OrderTypeAdapterInterface;
use crmeb\trade\order\type\OrderTypeRegistry;
use crmeb\trade\product\ProductTypeHandlerInterface;
use crmeb\trade\product\ProductTypeRegistry;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 扩展点注册中心一致性校验命令（T3 内核纯净性配套 / P5 联动）
 *
 * 框架启动期（AppService）各模块 App.php 完成注册后，对四大注册中心做运行时一致性校验：
 * - ProductTypeRegistry（产品类型）/ TradeModeRegistry（交易模式）
 * - OrderTypeRegistry（订单适配器）/ AfterSaleRuleRegistry（售后规则）
 *
 * 校验项：
 * 1. 注册类合法性：所有注册的 Handler/Adapter 类存在且实现对应契约接口（register 期未校验的中心兜底）
 * 2. 兜底完整性：normal 产品类型 / normal 交易模式 / normal_order 适配器必须就位
 * 3. 产品类型 → 订单适配器对齐：getOrderSourceType() 声明的源表类型必须已注册
 * 4. 产品类型 → 售后规则覆盖：每个产品类型必须有 product_type 维度规则（未注册默认拒绝售后，属静默行为变化）
 * 5. 产品类型 → 交易模式兼容有效：getCompatibility() 声明的模式必须已注册（warning 级）
 *
 * 用法：php think registry:check
 * error 级问题退出码 1，可接入 CI / 发布流程拦截（与 event:check 同模式）
 */
class RegistryCheck extends Command
{
    protected function configure()
    {
        $this->setName('registry:check')
            ->setDescription('扩展点注册中心一致性校验（产品类型/交易模式/订单适配器/售后规则）');
    }

    /**
     * 执行校验
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return int 0=通过（允许存在 warning），1=存在 error 级问题
     */
    protected function execute(Input $input, Output $output)
    {
        $errors = [];
        $warnings = [];

        /** @var ProductTypeRegistry $productRegistry */
        $productRegistry = $this->app->make(ProductTypeRegistry::class);
        /** @var TradeModeRegistry $tradeRegistry */
        $tradeRegistry = $this->app->make(TradeModeRegistry::class);

        $productTypes = $productRegistry->all();
        $tradeModes   = $tradeRegistry->all();
        $sourceTypes  = OrderTypeRegistry::getRegisteredSourceTypes();

        $output->writeln('扩展点注册中心一致性校验');
        $output->writeln('产品类型：' . count($productTypes) . ' / 交易模式：' . count($tradeModes)
            . ' / 订单适配器：' . count($sourceTypes));
        $output->writeln(str_repeat('-', 72));

        // ── 1. 注册类合法性：类存在 + 实现契约接口（反射读注册表，不实例化、零副作用）──
        $this->checkClassLegality('ProductTypeRegistry', $this->readProtectedMap(ProductTypeRegistry::class, 'handlers'), ProductTypeHandlerInterface::class, $errors);
        $this->checkClassLegality('TradeModeRegistry', $this->readProtectedMap(TradeModeRegistry::class, 'handlers'), TradeModeHandlerInterface::class, $errors);
        $this->checkClassLegality('OrderTypeRegistry', $this->readProtectedMap(OrderTypeRegistry::class, 'adapters'), OrderTypeAdapterInterface::class, $errors);
        // AfterSaleRuleRegistry::register 期已做 is_subclass_of 校验，无需重复

        // ── 2. 兜底完整性 ──
        if (!in_array('normal', $productTypes, true)) {
            $errors[] = 'ProductTypeRegistry 缺少兜底产品类型 normal（Registry::get 未注册类型将无兜底，运行期抛 RuntimeException）';
        }
        if (!in_array('normal', $tradeModes, true)) {
            $errors[] = 'TradeModeRegistry 缺少普通购买交易模式 normal';
        }
        if (!in_array('normal_order', $sourceTypes, true)) {
            $errors[] = 'OrderTypeRegistry 缺少兜底订单适配器 normal_order';
        }

        // ── 3~5. 产品类型三维对齐：订单适配器 / 售后规则 / 交易模式兼容 ──
        foreach ($productTypes as $type) {
            try {
                $handler = $productRegistry->get($type);

                // 订单适配器对齐：缺失时中台解析订单状态抛「未注册的源表类型」
                $sourceType = $handler->getOrderSourceType();
                if (!in_array($sourceType, $sourceTypes, true)) {
                    $errors[] = "产品类型 [{$type}] 声明源表类型 [{$sourceType}] 未在 OrderTypeRegistry 注册"
                        . "（请检查 modules/product/{$type}/App.php 是否漏调 OrderTypeRegistry::register）";
                }

                // 售后规则覆盖：未注册 product_type 规则的订单默认拒绝售后，属静默行为变化
                if (AfterSaleRuleRegistry::getProvider('product_type', $type) === null) {
                    $errors[] = "产品类型 [{$type}] 未注册 product_type 维度售后规则"
                        . "（AfterSaleRuleRegistry::registerProductType，缺失将默认拒绝售后）";
                }

                // 交易模式兼容有效：声明了未注册的模式，运行期下单会走兜底异常
                // getCompatibility() 返回关联数组，模式列表在 supported_trade_modes 键下
                foreach (($handler->getCompatibility()['supported_trade_modes'] ?? []) as $mode) {
                    if (!in_array($mode, $tradeModes, true)) {
                        // 插值前做标量防护，避免非标量声明值触发 Array to string conversion
                        $modeLabel = is_scalar($mode) ? var_export($mode, true) : gettype($mode);
                        $warnings[] = "产品类型 [{$type}] 声明兼容交易模式 [{$modeLabel}]，但 TradeModeRegistry 未注册该模式";
                    }
                }
            } catch (\Throwable $e) {
                $errors[] = "产品类型 [{$type}] Handler 无法实例化或声明方法异常：" . $e->getMessage();
            }
        }

        // 输出结果
        if ($errors) {
            $output->error('error 级问题 ' . count($errors) . ' 个：');
            foreach ($errors as $msg) {
                $output->writeln('  ❌ ' . $msg);
            }
        } else {
            $output->writeln('✅ error 级问题：0');
        }

        if ($warnings) {
            $output->warning('warning 级问题 ' . count($warnings) . ' 个：');
            foreach ($warnings as $msg) {
                $output->writeln('  ⚠️ ' . $msg);
            }
        } else {
            $output->writeln('✅ warning 级问题：0');
        }

        if ($errors) {
            $output->error('校验未通过：注册中心存在非法/缺失注册，请修正模块 App.php 注册逻辑');
            return 1;
        }

        $output->writeln('✅ 校验通过' . ($warnings ? '（存在兼容声明警告，请评估）' : ''));
        return 0;
    }

    /**
     * 校验注册表内所有类名合法（类存在 + 实现契约接口）
     * @param string $registryName 注册中心名称（用于报错提示）
     * @param array $map 注册表 key => 类名
     * @param string $contract 契约接口完整类名
     * @param array $errors 引用收集 error 消息
     * @return void
     */
    protected function checkClassLegality(string $registryName, array $map, string $contract, array &$errors): void
    {
        foreach ($map as $key => $class) {
            if (!class_exists($class)) {
                $errors[] = "{$registryName} 注册项 [{$key}] 类不存在：{$class}";
            } elseif (!is_subclass_of($class, $contract)) {
                $errors[] = "{$registryName} 注册项 [{$key}] 未实现契约接口：{$class} 应实现 {$contract}";
            }
        }
    }

    /**
     * 反射读取注册中心的 protected 静态/实例映射表（只读，不触发实例化）
     * @param string $class 注册中心类名
     * @param string $prop 属性名（handlers / adapters）
     * @return array key => 类名映射表
     */
    protected function readProtectedMap(string $class, string $prop): array
    {
        try {
            $ref = new \ReflectionProperty($class, $prop);
            $ref->setAccessible(true);
            $value = $ref->isStatic() ? $ref->getValue() : $ref->getValue($this->app->make($class));
            return is_array($value) ? $value : [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
