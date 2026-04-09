<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class HfsNavigation
{
    public static function forUser(?Authenticatable $user): Collection
    {
        return collect(config('hfs.navigation'))
            ->filter(function (array $item) use ($user): bool {
                $permission = $item['permission'] ?? null;

                if (! $permission) {
                    return true;
                }

                if (! $user || ! method_exists($user, 'can')) {
                    return false;
                }

                if (is_array($permission)) {
                    foreach ($permission as $entry) {
                        if ($user->can($entry)) {
                            return true;
                        }
                    }

                    return false;
                }

                return $user->can($permission);
            })
            ->map(function (array $item): array {
                $item['active'] = request()->routeIs($item['route']);

                return $item;
            })
            ->values();
    }
}
