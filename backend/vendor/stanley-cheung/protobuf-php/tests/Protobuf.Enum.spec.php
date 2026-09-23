<?php

require_once __DIR__ . '/../library/DrSlump/Protobuf.php';

use \DrSlump\Protobuf;

Protobuf::autoload();

include_once __DIR__ . '/protos/complex.php';


describe "Enums support"

    before
        Protobuf::setDefaultCodec(new ProtoBuf\Codec\Json);
    end

    it "obtain all possible enum values"
        $enum = Tests\Complex\Enum::getInstance();
        $enum->toArray() should be an array;
        count($enum->toArray()) should be 3;

        $arr = $enum->toArray();
        foreach ($enum as $k=>$v) {
            $v should eq ($arr[$k]);
        }
    end

    it "access with property names and with array offsets"
        $enum = Tests\Complex\Enum::getInstance();

        $enum->FOO should eq $enum['FOO'];
    end

    it "obtain enum value name from the integer value"
        $enum = Tests\Complex\Enum::getInstance();

        $enum[ $enum->FOO ] should eq 'FOO';
    end

end;
