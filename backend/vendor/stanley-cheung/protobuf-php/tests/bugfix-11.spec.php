<?php

require_once __DIR__ . '/../library/DrSlump/Protobuf.php';

error_reporting(E_ALL);

use \DrSlump\Protobuf;

Protobuf::autoload();

include_once __DIR__ . '/protos/addressbook.php';

describe "Bugfix #11"

    before
        $codec = new Protobuf\Codec\Binary();
        Protobuf::setDefaultCodec($codec);
    end

    it "should serialize nested message"

        $p = new tests\Person();

        $p->setName('Foo');
        $p->setId(2048);
        $p->setEmail('foo@bar.com');

        $phoneNumber = new tests\Person\PhoneNumber;
        $phoneNumber->setNumber('+8888888888');
        $p->setPhone($phoneNumber);

        $data = $p->serialize();

        $data should be a string
    end

end;
