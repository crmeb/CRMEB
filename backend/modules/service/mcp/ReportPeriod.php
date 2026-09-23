<?php
declare(strict_types=1);

namespace modules\service\mcp;

/** MCP 商品和优惠券统计共用的日期范围校验。 */
final class ReportPeriod
{
    private const MAX_DAYS = 31;

    /**
     * @param array $args 可传 start_date/end_date（YYYY-MM-DD）
     * @param string $subject 报表业务名称
     * @return array{start_date:string,end_date:string,start_time:int,end_time:int}
     * @throws McpException 日期错误或范围过大
     */
    public static function fromArgs(array $args, string $subject): array
    {
        $startInput = $args['start_date'] ?? null;
        $endInput = $args['end_date'] ?? null;
        if ($startInput === null && $endInput === null) {
            $end = new \DateTimeImmutable('today');
            $start = $end->modify('-6 days');
        } else {
            if (!is_string($startInput) || !is_string($endInput)) {
                throw new McpException('请同时提供 start_date 和 end_date，格式为 YYYY-MM-DD');
            }
            $start = self::parseDate($startInput);
            $end = self::parseDate($endInput);
        }

        if ($start > $end) {
            throw new McpException('start_date 不能晚于 end_date');
        }
        if ($start->diff($end)->days >= self::MAX_DAYS) {
            throw new McpException($subject . '时间范围最多 31 天');
        }

        return [
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'start_time' => $start->getTimestamp(),
            'end_time' => $end->setTime(23, 59, 59)->getTimestamp(),
        ];
    }

    /**
     * @param string $value 日期字符串
     * @return \DateTimeImmutable
     * @throws McpException 日期无效
     */
    private static function parseDate(string $value): \DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new McpException('日期格式错误，请使用有效的 YYYY-MM-DD 日期');
        }
        return $date;
    }
}
