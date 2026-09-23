<?php

require_once __DIR__ . '/../library/DrSlump/Protobuf.php';

use DrSlump\Protobuf;

describe 'Protobuf'

    //before
        Protobuf::autoload();
    //end

    it 'should autoload classes'
        new Protobuf\Codec\Binary();
    end.

    describe 'codecs registry'
        it 'should get a default codec if none set'
            $codec = Protobuf::getCodec();
            $codec should be an instance of '\DrSlump\Protobuf\CodecInterface';
        end.

        it 'should return the passed codec instance'
            $passed = new Protobuf\Codec\Binary();
            $getted = Protobuf::getCodec($passed);
            $getted should be $passed
        end.

        it. 'should register a new codec'
            $setted = new Protobuf\Codec\Binary();
            Protobuf::registerCodec('test', $setted);
            $getted = Protobuf::getCodec('test');
            $getted should be $setted
        end.

        it 'should register a new default codec'
            $setted = new Protobuf\Codec\Binary();
            Protobuf::setDefaultCodec($setted);
            Protobuf::getCodec() should be $setted
        end.

        # throws DrSlump\Protobuf\Exception
        it. 'should unregister a codec'
            $setted = new Protobuf\Codec\Binary();
            Protobuf::registerCodec('test', $setted);
            $result = Protobuf::unregisterCodec('test');
            $result should be true;
            // If not set throws an exception
            Protobuf::getCodec('test');
        end.

        it. 'should unregister the default codec'
            $result = Protobuf::unregisterCodec('default');
            $result should be true;
            // Ensure a new default is given
            $getted = Protobuf::getCodec();
            $getted should be instanceof 'DrSlump\Protobuf\Codec\Binary'
        end.
    end;
end;




