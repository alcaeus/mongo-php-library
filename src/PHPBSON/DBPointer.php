<?php

namespace MongoDB\PHPBSON;

use function sprintf;

final class DBPointer implements Type
{
    public function __construct(
        public string $ref,
        // TODO: Store ObjectId instance?
        public string $oid,
    ) {}

    public function toCanonicalExtendedJSON(): string
    {
        return sprintf('{"$dbPointer": {"$ref": "%s", "$id": {"$oid": "%s"}}', $this->ref, $this->oid);
    }

    public function toRelaxedExtendedJSON(): string
    {
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
