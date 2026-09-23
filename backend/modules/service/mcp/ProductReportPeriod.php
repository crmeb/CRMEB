<?php
declare(strict_types=1);

namespace modules\service\mcp;

/** 商品报表工具的日期范围入口。 */
final class ProductReportPeriod
{
    /**
     * @param array $args 工具参数，可传 start_date/end_date（YYYY-MM-DD）
     * @return array{start_date:string,end_date:string,start_time:int,end_time:int}
     * @throws McpException 日期错误或范围过大
     */
    public static function fromArgs(array $args): array
    {
        return ReportPeriod::fromArgs($args, '商品统计');
    }
}
