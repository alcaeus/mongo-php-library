<?php

namespace MongoDB\PHPBSON;

use MongoDB\BSON\ObjectIdInterface;

use function hex2bin;

final class ObjectId implements ObjectIdInterface, Type
{
    public readonly string $id;

    final public function __construct(?string $id = null)
    {
        if ($id === null) {
            // TODO: Implement OID generation according to spec
            throw new \Exception('Not implemented');
        }

        if (strlen($id) !== 24 || hex2bin($id) === false) {
            throw new \Exception('Invalid ObjectId given');
        }

        $this->id = $id;
    }

    public function getTimestamp()
    {
        // TODO: Implement getTimestamp() method.
    }

    public function __toString()
    {
        return $this->id;
    }

    public function toCanonicalExtendedJSON(): string
    {
        return sprintf('{"$oid" : "%s"}', $this->id);
    }

    public function toRelaxedExtendedJSON(): string
    {
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
