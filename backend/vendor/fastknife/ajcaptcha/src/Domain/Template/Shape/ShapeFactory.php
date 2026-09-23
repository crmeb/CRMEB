<?php
declare(strict_types=1);

namespace Fastknife\Domain\Template\Shape;

class ShapeFactory
{
    public static function create(string $type): ShapeDrawerInterface
    {
        switch ($type) {
            case 'red_heart': // 新命名
                return new RedHeartShapeDrawer();
            case 'diamond':
                return new DiamondShapeDrawer();
            case 'spade':
                return new SpadeShapeDrawer();
            case 'club':
                return new ClubShapeDrawer();
            case 'jigsaw':
            default:
                return new JigsawShapeDrawer();
        }
    }
}
