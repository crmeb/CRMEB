<?php

require_once __DIR__ . '/../library/DrSlump/Protobuf.php';

use \DrSlump\Protobuf;

Protobuf::autoload();

include_once __DIR__ . '/protos/simple.php';
include_once __DIR__ . '/protos/repeated.php';
include_once __DIR__ . '/protos/addressbook.php';

describe "XML Codec"

    before
        Protobuf::setDefaultCodec(new ProtoBuf\Codec\Xml);
    end

    describe "serialize"

        it "a simple message"
            $simple = new Tests\Simple();
            $simple->string = 'FOO';
            $simple->int32 = 1000;
            $xml = Protobuf::encode($simple);
            $sxe = simplexml_load_string($xml);
            $sxe->string should eq "FOO";
            $sxe->int32 should eq 1000;
        end.

         it. "a message with repeated fields"

             $repeated = new \Tests\Repeated();
             $repeated->addString('one');
             $repeated->addString('two');
             $repeated->addString('three');
             $xml = Protobuf::encode($repeated);
             $xml = simplexml_load_string($xml);
             $xml->string[0] should eq 'one';
             $xml->string[1] should eq 'two';
             $xml->string[2] should eq 'three';

             $repeated = new Tests\Repeated();
             $repeated->addInt(1);
             $repeated->addInt(2);
             $repeated->addInt(3);
             $xml = Protobuf::encode($repeated);
             $xml = simplexml_load_string($xml);
             $xml->int[0] should eq 1;
             $xml->int[1] should eq 2;
             $xml->int[2] should eq 3;

             $repeated = new Tests\Repeated();
             $nested = new Tests\Repeated\Nested();
             $nested->setId(1);
             $repeated->addNested($nested);
             $nested = new Tests\Repeated\Nested();
             $nested->setId(2);
             $repeated->addNested($nested);
             $nested = new Tests\Repeated\Nested();
             $nested->setId(3);
             $repeated->addNested($nested);
             $xml = Protobuf::encode($repeated);
             $xml = simplexml_load_string($xml);
             $xml->nested[0]->id should eq 1;
             $xml->nested[1]->id should eq 2;
             $xml->nested[2]->id should eq 3;
         end.

        it. "a complex message"

            $book = new Tests\AddressBook();
            $person = new Tests\Person();
            $person->name = 'John Doe';
            $person->id = 2051;
            $person->email = 'john.doe@gmail.com';
            $phone = new Tests\Person\PhoneNumber;
            $phone->number = '1231231212';
            $phone->type = Tests\Person\PhoneType::HOME;
            $person->addPhone($phone);
            $phone = new Tests\Person\PhoneNumber;
            $phone->number = '55512321312';
            $phone->type = Tests\Person\PhoneType::MOBILE;
            $person->addPhone($phone);
            $book->addPerson($person);

            $person = new Tests\Person();
            $person->name = 'Iván Montes';
            $person->id = 23;
            $person->email = 'drslump@pollinimini.net';
            $phone = new Tests\Person\PhoneNumber;
            $phone->number = '3493123123';
            $phone->type = Tests\Person\PhoneType::WORK;
            $person->addPhone($phone);
            $book->addPerson($person);

            $xml = Protobuf::encode($book);
            $xml = simplexml_load_string($xml);

            $xml->person[0]->name should eq "John Doe";
            $xml->person[0]->phone[1]->number should eq "55512321312";
            $xml->person[1]->id should eq 23;
            $xml->person[1]->phone[0]->type should eq 2;
        end.
    end;

    describe "unserialize"

        it "should unserialize a simple message"
            $xml = new SimpleXmlElement('<root></root>');
            $xml->addChild('string', 'FOO');
            $xml->addChild('int32', 1000);

            $simple = Protobuf::decode('Tests\Simple', $xml);
            $simple should be instanceof 'Tests\Simple';
            $simple->string should equal 'FOO';
            $simple->int32 should equal 1000;
        end.

        it "a message with repeated fields"

            $xml = new SimpleXMLElement('<root></root>');
            $xml->addChild('string', 'one');
            $xml->addChild('string', 'two');
            $xml->addChild('string', 'three');

            $repeated = Protobuf::decode('Tests\Repeated', $xml);
            $repeated->getString() should eq array('one', 'two', 'three');

            $xml = new SimpleXMLElement('<root></root>');
            $xml->addChild('int', 1);
            $xml->addChild('int', 2);
            $xml->addChild('int', 3);

            $repeated = Protobuf::decode('Tests\Repeated', $xml);
            $repeated should be instanceof 'Tests\Repeated';
            $repeated->getInt() should eq array(1,2,3);

            $xml = new SimpleXMLElement('<root></root>');
            $xml->addChild('nested')->addChild('id', 1);
            $xml->addChild('nested')->addChild('id', 2);
            $xml->addChild('nested')->addChild('id', 3);

            $repeated = Protobuf::decode('Tests\Repeated', $xml);
            $repeated should be instanceof 'Tests\Repeated';
            foreach ($repeated->getNested() as $i=>$nested) {
                $nested->getId() should eq ($i+1);
            }
        end.

        it "a complex message"

            $xml = new SimpleXMLElement('<root></root>');
            $p = $xml->addChild('person');
                $p->addChild('name', 'John Doe');
                $p->addChild('id', 2051);
                $p->addChild('email', 'john.doe@gmail.com');
                $p = $p->addChild('phone');
                    $p->addChild('number', '1231231212');
                    $p->addChild('type', 1);
            $p = $xml->addChild('person');
                $p->addChild('name', 'Iván Montes');
                $p->addChild('id', 23);
                $p->addChild('email', 'drslump@pollinimini.net');
                $p = $p->addChild('phone');
                    $p->addChild('number', '3493123123');
                    $p->addChild('type', 2);

            $complex = Protobuf::decode('Tests\AddressBook', $xml->asXML());
            count($complex->person) should eq 2;
            $complex->getPerson(0)->name should eq 'John Doe';
            $complex->getPerson(1)->name should eq 'Iván Montes';
            $complex->getPerson(1)->getPhone(0)->number should eq '3493123123';
        end.

    end;
end;
