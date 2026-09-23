<?php

require_once __DIR__ . '/../library/DrSlump/Protobuf.php';

use \DrSlump\Protobuf;

Protobuf::autoload();

include_once __DIR__ . '/protos/simple.php';
include_once __DIR__ . '/protos/repeated.php';
include_once __DIR__ . '/protos/addressbook.php';

describe "TextFormat Codec"

    before
        Protobuf::setDefaultCodec(new ProtoBuf\Codec\TextFormat);
    end

    describe "serialize"

         it "should serialize a simple message"
             $simple = new Tests\Simple();
             $simple->string = 'FOO';
             $simple->int32 = 1000;
             $txt = Protobuf::encode($simple);
             $txt . should. be. "int32: 1000\nstring: \"FOO\"\n";
         end.

         it. "a message with repeated fields"

             $repeated = new \Tests\Repeated();
             $repeated->addString('one');
             $repeated->addString('two');
             $repeated->addString('three');
             $txt = Protobuf::encode($repeated);
             $txt should be "string: \"one\"\nstring: \"two\"\nstring: \"three\"\n";

             $repeated = new Tests\Repeated();
             $repeated->addInt(1);
             $repeated->addInt(2);
             $repeated->addInt(3);
             $txt = Protobuf::encode($repeated);
             $txt should be "int: 1\nint: 2\nint: 3\n";

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
             $txt = Protobuf::encode($repeated);
             $txt should eq "nested {\n  id: 1\n}\nnested {\n  id: 2\n}\nnested {\n  id: 3\n}\n";
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

            $txt = Protobuf::encode($book);
            $txt = str_replace(' ', '', $txt);
            $txt = trim($txt);

            $expected = '
                person {
                    name: "John Doe"
                    id: 2051
                    email: "john.doe@gmail.com"
                    phone {
                        number: "1231231212"
                        type: 1
                    }
                    phone {
                        number: "55512321312"
                        type: 0
                    }
                }
                person {
                    name: "Iv\u00e1n Montes"
                    id: 23
                    email: "drslump@pollinimini.net"
                    phone {
                        number: "3493123123"
                        type: 2
                    }
                }
            ';

            $expected = str_replace(' ', '', $expected);
            $expected = trim($expected);

            $txt should be $expected;
         end.
    end;

    describe "unserialize"

         # throws \BadMethodCallException
         it "TextFormat does not implement decoding"
             $txt = "foo: \"FOO\"\nbar: \"BAR\"\n";
             $simple = Protobuf::decode('Tests\Simple', $txt);
         end.
    end;
end;
