<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\Concerns\HasCommaString;

enum Scope: string
{
    use HasCommaString;
    case Basic           = 'instagram_business_basic';
    case ContentPublish  = 'instagram_business_content_publish';
    case ManageMessages  = 'instagram_business_manage_messages';
    case ManageComments  = 'instagram_business_manage_comments';

    /**
     * Build a Scope collection from an array of string values.
     *
     * @param  array<int, self|string|null>  $values
     * @return self[]
     */
    public static function fromArray(array $values): array
    {
        $scopes = [];

        foreach ($values as $value) {
            if ($value instanceof self) {
                $scopes[] = $value;

                continue;
            }

            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            $scopes[] = self::from($value);
        }

        return $scopes;
    }
}
