<?php
declare(strict_types=1);

namespace crmeb\trade\order\aftersale;

/** 可选契约：在售后单落库前校验产品专属的申请范围和金额。 */
interface AfterSaleApplicationValidatorInterface
{
    public function validateApplication(array $order, array $data, array $items): void;
}
