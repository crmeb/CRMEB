<?php

require_once __DIR__ . '/../library/DrSlump/Protobuf.php';

use \DrSlump\Protobuf;

Protobuf::autoload();

include_once __DIR__ . '/protos/simple.php';
include_once __DIR__ . '/protos/repeated.php';
include_once __DIR__ . '/protos/addressbook.php';

include_once __DIR__ . '/protos/Annotated/Simple.php';
include_once __DIR__ . '/protos/Annotated/Repeated.php';
include_once __DIR__ . '/protos/Annotated/Addressbook.php';


describe "JSON Codec"

    before
        Protobuf::setDefaultCodec(new ProtoBuf\Codec\Json);
    end

    describe "serialize"

        it "a simple message"
            $simple = new Tests\Simple();
            $simple->string = 'FOO';
            $simple->int32 = 1000;
            $json = Protobuf::encode($simple);
            $json. should. eq. '{"int32":1000,"string":"FOO"}';
        end.

         it. "a message with repeated fields"

             $repeated = new \Tests\Repeated();
             $repeated->addString('one');
             $repeated->addString('two');
             $repeated->addString('three');
             $bin = Protobuf::encode($repeated);
             $bin should be '{"string":["one","two","three"]}';

             $repeated = new Tests\Repeated();
             $repeated->addInt(1);
             $repeated->addInt(2);
             $repeated->addInt(3);
             $bin = Protobuf::encode($repeated);
             $bin should be '{"int":[1,2,3]}';

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
             $json = Protobuf::encode($repeated);
             $json should eq '{"nested":[{"id":1},{"id":2},{"id":3}]}';
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

            $json = Protobuf::encode($book);

            $expected = '{
                "person":[
                    {
                        "name":"John Doe",
                        "id":2051,
                        "email":"john.doe@gmail.com",
                        "phone":[
                            {"number":"1231231212","type":1},
                            {"number":"55512321312","type":0}
                        ]
                    },
                    {
                        "name":"Iv\u00e1n Montes",
                        "id":23,
                        "email":"drslump@pollinimini.net",
                        "phone":[{"number":"3493123123","type":2}]
                    }
                ]
            }';

            $expected = preg_replace('/\n\s*/', '', $expected);

            $json should be $expected;
        end.

        it "an annotated simple message"
            $simple = new tests\Annotated\Simple();
            $simple->foo = 'FOO';
            $simple->bar = 1000;
            $json = Protobuf::encode($simple);
            $json. should. eq. '{"foo":"FOO","bar":1000}';
        end.

        it "an annotated message with repeated fields"
             $repeated = new \Tests\Annotated\Repeated();
             $repeated->string = array('one', 'two', 'three');
             $bin = Protobuf::encode($repeated);
             $bin should be '{"string":["one","two","three"]}';

             $repeated = new Tests\Annotated\Repeated();
             $repeated->int = array(1,2,3);
             $bin = Protobuf::encode($repeated);
             $bin should be '{"int":[1,2,3]}';

             $repeated = new Tests\Annotated\Repeated();
             $repeated->nested = array();
             $nested = new Tests\Annotated\RepeatedNested();
             $nested->id = 1;
             $repeated->nested[] = $nested;
             $nested = new Tests\Annotated\RepeatedNested();
             $nested->id = 2;
             $repeated->nested[] = $nested;
             $nested = new Tests\Annotated\RepeatedNested();
             $nested->id = 3;
             $repeated->nested[] = $nested;
             $json = Protobuf::encode($repeated);
             $json should eq '{"nested":[{"id":1},{"id":2},{"id":3}]}';
        end.
    end;

    describe "unserialize"

        it "should unserialize a simple message"
            $json = '{"string":"FOO","int32":1000}';
            $simple = Protobuf::decode('Tests\Simple', $json);
            $simple should be instanceof 'Tests\Simple';
            $simple->string should equal 'FOO';
            $simple->int32 should equal 1000;
        end.

        it "a message with repeated fields"

            $json = '{"string":["one","two","three"]}';
            $repeated = Protobuf::decode('Tests\Repeated', $json);
            $repeated->getString() should eq array('one', 'two', 'three');

            $json = '{"int":[1,2,3]}';
            $repeated = Protobuf::decode('Tests\Repeated', $json);
            $repeated should be instanceof 'Tests\Repeated';
            $repeated->getInt() should eq array(1,2,3);

            $json = '{"nested":[{"id":1},{"id":2},{"id":3}]}';
            $repeated = Protobuf::decode('Tests\Repeated', $json);
            $repeated should be instanceof 'Tests\Repeated';
            foreach ($repeated->getNested() as $i=>$nested) {
                $nested->getId() should eq ($i+1);
            }
        end.

        it "a complex message"
            $json = '{
                "person":[
                    {
                        "name":"John Doe",
                        "id":2051,
                        "email":"john.doe@gmail.com",
                        "phone":[
                            {"number":"1231231212","type":1},
                            {"number":"55512321312","type":0}
                        ]
                    },
                    {
                        "name":"Iv\u00e1n Montes",
                        "id":23,
                        "email":"drslump@pollinimini.net",
                        "phone":[{"number":"3493123123","type":2}]
                    }
                ]
            }';

            $json = preg_replace('/\n\s*/', '', $json);

            $complex = Protobuf::decode('Tests\AddressBook', $json);
            count($complex->person) should eq 2;
            $complex->getPerson(0)->name should eq 'John Doe';
            $complex->getPerson(1)->name should eq 'Iván Montes';
            $complex->getPerson(0)->getPhone(1)->number should eq '55512321312';
        end.

    end;
end;
