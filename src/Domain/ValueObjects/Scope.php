<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

enum Scope: string
{
    case Basic           = 'instagram_business_basic';
    case ContentPublish  = 'instagram_business_content_publish';
    case ManageMessages  = 'instagram_business_manage_messages';
    case ManageComments  = 'instagram_business_manage_comments';

    /**
     * Build a Scope collection from an array of string values.
     *
     * @param  string[]  $values
     * @return self[]
     */
    public static function fromArray(array $values): array
    {
        return array_map(fn (string $v) => self::from($v), $values);
    }

    /**
     * Serialize a collection of Scope cases to a comma-separated string.
     *
     * @param  self[]  $scopes
     */
    public static function toString(array $scopes): string
    {
        return implode(',', array_map(fn (self $s) => $s->value, $scopes));
    }
}
