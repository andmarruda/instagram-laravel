<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects\Concerns;

trait HasCommaString
{
    /**
     * Serialize a collection of enum cases to a comma-separated string of their values.
     *
     * @param  self[]  $items
     */
    public static function toString(array $items): string
    {
        return implode(',', array_map(fn (self $i) => $i->value, $items));
    }
}
