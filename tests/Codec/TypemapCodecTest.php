<?php

namespace MongoDB\Tests\Codec;

use Generator;
use MongoDB\BSON\Binary;
use MongoDB\BSON\Document;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\Unserializable;
use MongoDB\Codec\TypemapCodec;
use MongoDB\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

class TypemapCodecTest extends TestCase
{
    #[DataProvider('providePClassTests')]
    public function testPClassMustBeInstantiableAndPersistable(string $json): void
    {
        $document = Document::fromJSON($json);

        $phpDocument = $this->createCodec()->decode($document);

        $this->assertInstanceOf(stdClass::class, $phpDocument);
        $this->assertSame('yes', $phpDocument->foo);
        $this->assertInstanceOf(Binary::class, $phpDocument->__pclass);
    }

    public static function providePClassTests(): Generator
    {
        // Create base64-encoded class names for __pclass field's binary data
        $bMyAbstractDocument = base64_encode(AbstractPersistable::class);
        $bMyDocument = base64_encode(UnserializableClass::class);
        $bUnserializable = base64_encode(Unserializable::class);
        $bPersistable = base64_encode(Persistable::class);

        yield AbstractPersistable::class => ['{ "foo": "yes", "__pclass": { "$binary": "' . $bMyAbstractDocument . '", "$type": "80" } }'];
        yield UnserializableClass::class => ['{ "foo": "yes", "__pclass": { "$binary": "' . $bMyDocument . '", "$type": "80" } }'];
        yield Unserializable::class => ['{ "foo": "yes", "__pclass": { "$binary": "' . $bUnserializable . '", "$type": "80" } }'];
        yield Persistable::class => ['{ "foo": "yes", "__pclass": { "$binary": "' . $bPersistable . '", "$type": "44" } }'];
    }

    private function createCodec(): TypemapCodec
    {
        return new TypemapCodec();
    }
}

abstract class AbstractPersistable implements Persistable
{
}

class UnserializableClass implements Unserializable
{
    public bool $unserialized = false;
    public array $data;

    public function bsonUnserialize(array $data): void
    {
        $this->unserialized = true;
        $this->data = $data;
    }
}

