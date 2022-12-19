<?php
declare(strict_types=1);

namespace Fastknife\Domain\Vo;


class OffsetVo
{
    public $x;
    public $y;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

}
