<?php

namespace App\Support;

use Illuminate\Support\Str;

class Helpers
{
    public static function dropdownOptions(string $modelClass, string $labelColumn = 'name', int $limit = 10, ?Closure $queryModifier = null): array
    {
        $query = $modelClass::query()->orderBy($labelColumn)->take($limit);

        if ($queryModifier) {
            $queryModifier($query);
        }

        return $query->pluck($labelColumn, 'id')->toArray();
    }
}
