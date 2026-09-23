<?php

require_once __DIR__ . '/../library/DrSlump/Protobuf.php';

error_reporting(E_ALL);

use \DrSlump\Protobuf;

Protobuf::autoload();

include_once __DIR__ . '/protos/simple.php';
include_once __DIR__ . '/protos/complex.php';
include_once __DIR__ . '/protos/repeated.php';
include_once __DIR__ . '/protos/addressbook.php';
include_once __DIR__ . '/protos/extension.php';

// Include some hamcrest matchers manually since they are not included by default
// TODO: Fix spec4php to include them
include_once 'Hamcrest/Core/IsNot.php';
include_once 'Hamcrest/Core/AllOf.php';


describe "Binary Codec"

    before
        $codec = new Protobuf\Codec\Binary();
        Protobuf::setDefaultCodec($codec);

        $W->bin_simple = file_get_contents(__DIR__ . '/protos/simple.bin');
        $W->bin_book = file_get_contents(__DIR__ . '/protos/addressbook.bin');
        $W->bin_repeated_string = file_get_contents(__DIR__ . '/protos/repeated-string.bin');
        $W->bin_repeated_int32 = file_get_contents(__DIR__ . '/protos/repeated-int32.bin');
        $W->bin_repeated_nested = file_get_contents(__DIR__ . '/protos/repeated-nested.bin');
        $W->bin_ext = file_get_contents(__DIR__ . '/protos/extension.bin');
    end;

    describe "serialize"

        it "a simple message comparing types with protoc"

            $max = pow(2, 54)-1;
            $min = -$max;

            $fields = array(
                'double' => array(1, 0.1, 1.0, -1, -0.1, -100000, 123456789.12345, -123456789.12345),
                'float'  => array(1, 0.1, 1.0, -1, -0.1, -100000, 12345.123, -12345.123),
                'int64'  => array(0, 1, -1, 123456789123456789, -123456789123456789, $min),
                'uint64' => array(0, 1, 1000, 123456789123456789, PHP_INT_MAX, $max),
                'int32'  => array(0, 1, -1, 123456789, -123456789),
                'fixed64'  => array(0, 1, 1000, 123456789123456789),
                'fixed32'  => array(0, 1, 1000, 123456789),
                'bool'  => array(0, 1),
                'string'  => array("", "foo"),
                'bytes'  => array("", "foo"),
                'uint32'  => array(0, 1, 1000, 123456789),
                'sfixed32'  => array(0, 1, -1, 123456789, -123456789),
                'sfixed64'  => array(0, 1, -1, 123456789123456789, -123456789123456789),
                'sint32'  => array(0, 1, -1, 123456789, -123456789),
                'sint64' => array(0, 1, -1, 123456789123456789, -123456789123456789, $min, $max),
            );


            foreach ($fields as $field=>$values) {
                foreach ($values as $value) {
                    $simple = new Tests\Simple();
                    $simple->$field = $value;
                    $bin = Protobuf::encode($simple);

                    if (is_string($value)) $value = '"' . $value . '"';

                    exec("echo '$field: $value' | protoc --encode=tests.Simple -Itests tests/protos/simple.proto", $out);

                    $out = implode("\n", $out);

                    $printValue = var_export($value, true);
                    bin2hex($bin) should eq (bin2hex($out)) as "Encoding $field with value $printValue";
                }
            }

            foreach ($fields as $field=>$values) {
                foreach ($values as $value) {
                    $cmdValue = is_string($value)
                              ? '"' . $value . '"'
                              : $value;

                    exec("echo '$field: $cmdValue' | protoc --encode=tests.Simple -Itests tests/protos/simple.proto", $out);
                    $out = implode("\n", $out);

                    $simple = Protobuf::decode('\tests\Simple', $out);

                    // Hack the comparison for float precision
                    if (is_float($simple->$field)) {
                        $precision = strlen($value) - strpos($value, '.');
                        $simple->$field = round($simple->$field, $precision);
                    }

                    $printValue = var_export($value, true);
                    $simple->$field should eq $value as "Decoding $field with value $printValue";
                }
            }
        end.

        it. "an enum comparing with protoc".

            $complex = new Tests\Complex();

            exec("echo 'enum: FOO' | protoc --encode=tests.Complex -Itests tests/protos/complex.proto", $protocbin);
            $protocbin = implode("\n", $protocbin);

            // Check encoding an enum
            $complex->enum = Tests\Complex\Enum::FOO;
            $encbin = Protobuf::encode($complex);

            bin2hex($encbin) should eq (bin2hex($protocbin)) as "Encoding Enum field";

            // Check decoding an enum
            $complex = Protobuf::decode('\tests\Complex', $protocbin);
            $complex->enum should eq (Tests\Complex\Enum::FOO) as "Decoding Enum field";

        end.

        it. "a nested message comparing with protoc"

            exec("echo 'nested { foo: \"FOO\" }' | protoc --encode=tests.Complex -Itests tests/protos/complex.proto", $protocbin);
            $protocbin = implode("\n", $protocbin);

            // Encoding
            $complex = new Tests\Complex();
            $complex->nested = new Tests\Complex\Nested();
            $complex->nested->foo = 'FOO';
            $encbin = Protobuf::encode($complex);

            bin2hex($encbin) should eq (bin2hex($protocbin)) as "Encoding nested message";

            // Decoding
            $complex = Protobuf::decode('\tests\Complex', $protocbin);
            $complex->nested->foo should eq 'FOO' as "Decoding nested message";
        end.

        it. "a message with repeated fields"

            $repeated = new Tests\Repeated();
            $repeated->addString('one');
            $repeated->addString('two');
            $repeated->addString('three');
            $bin = Protobuf::encode($repeated);
            $bin should be $W->bin_repeated_string;

            $repeated = new Tests\Repeated();
            $repeated->addInt(1);
            $repeated->addInt(2);
            $repeated->addInt(3);
            $bin = Protobuf::encode($repeated);
            $bin should be $W->bin_repeated_int32;


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
            $bin = Protobuf::encode($repeated);
            $bin should eq $W->bin_repeated_nested;
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

            $bin = Protobuf::encode($book);
            $bin should eq $W->bin_book but not be false;

        end.

        it 'a message with extended fields'
            $ext = new Tests\ExtA();
            $ext->first = 'FIRST';
            $ext['tests\ExtB\second'] = 'SECOND';
            $bin = Protobuf::encode($ext);
            $bin should eq $W->bin_ext but not be false;
        end

    end;

    describe "unserialize"

        it "a simple message"
            $simple = Protobuf::decode('Tests\Simple', $W->bin_simple);
            $simple should be instanceof 'Tests\Simple';
            $simple->string should be 'foo';
            $simple->int32 should be -123456789;
        end.

        it "a message with repeated fields"

            $repeated = Protobuf::decode('Tests\Repeated', $W->bin_repeated_string);
            $repeated should be instanceof 'Tests\Repeated';
            $repeated->getString() should eq array('one', 'two', 'three');

            $repeated = Protobuf::decode('Tests\Repeated', $W->bin_repeated_int32);
            $repeated should be instanceof 'Tests\Repeated';
            $repeated->getInt() should eq array(1,2,3);

            $repeated = Protobuf::decode('Tests\Repeated', $W->bin_repeated_nested);
            $repeated should be instanceof 'Tests\Repeated';
            foreach ($repeated->getNested() as $i=>$nested) {
                $nested->getId() should eq ($i+1);
            }
        end.

        it "a complex message"
            $complex = Protobuf::decode('Tests\AddressBook', $W->bin_book);
            count($complex->person) should eq 2;
            $complex->getPerson(0)->name should eq 'John Doe';
            $complex->getPerson(1)->name should eq 'Iván Montes';
            $complex->getPerson(0)->getPhone(1)->number should eq '55512321312';
        end.

        it 'a message with extended fields'
            $ext = Protobuf::decode('Tests\ExtA', $W->bin_ext);
            $ext->first should eq 'FIRST';
            $ext['tests\ExtB\second'] should eq 'SECOND';
        end

    end;

    describe "multi codec"

        before
           $W->jsonCodec = new Protobuf\Codec\Json();
        end

        it "a simple message"

            $simple = Protobuf::decode('Tests\Simple', $W->bin_simple);
            $json = $W->jsonCodec->encode($simple);
            $simple = $W->jsonCodec->decode(new \Tests\Simple, $json);
            $bin = Protobuf::encode($simple);
            $bin should be $W->bin_simple;

        end.

        it "a message with repeated fields"
            $repeated = Protobuf::decode('Tests\Repeated', $W->bin_repeated_nested);
            $json = $W->jsonCodec->encode($repeated);
            $repeated = $W->jsonCodec->decode(new \Tests\Repeated, $json);
            $bin = Protobuf::encode($repeated);
            $bin should be $W->bin_repeated_nested;
        end.

    end;
end;
