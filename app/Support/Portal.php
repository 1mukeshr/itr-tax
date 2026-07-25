<?php

namespace App\Support;

use Illuminate\Http\Request;

class Portal
{
    public static function separationEnabled(): bool
    {
        if (! filter_var(config('itr.admin_portal_separate', false), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        return self::adminHosts() !== [] && filled(config('itr.admin_url'));
    }

    /** @return list<string> */
    public static function adminHosts(): array
    {
        $raw = (string) config('itr.admin_hosts', '');
        if (trim($raw) === '') {
            $adminUrl = (string) config('itr.admin_url', '');
            if ($adminUrl !== '') {
                $host = parse_url($adminUrl, PHP_URL_HOST);
                $port = parse_url($adminUrl, PHP_URL_PORT);
                if ($host) {
                    $raw = $port ? $host.':'.$port : $host;
                }
            }
        }

        return collect(preg_split('/[\s,;]+/', strtolower($raw)) ?: [])
            ->map(fn ($h) => trim($h))
            ->filter()
            ->values()
            ->all();
    }

    public static function requestHost(?Request $request = null): string
    {
        $request ??= request();

        return strtolower($request->getHttpHost());
    }

    public static function isAdminHost(?Request $request = null): bool
    {
        if (! self::separationEnabled()) {
            return false;
        }

        return in_array(self::requestHost($request), self::adminHosts(), true);
    }

    public static function isPublicHost(?Request $request = null): bool
    {
        if (! self::separationEnabled()) {
            return true;
        }

        return ! self::isAdminHost($request);
    }

    public static function adminBaseUrl(): string
    {
        return rtrim((string) config('itr.admin_url', config('app.url')), '/');
    }

    public static function publicBaseUrl(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    public static function adminPath(string $path = '/admin'): string
    {
        return self::adminBaseUrl().'/'.ltrim($path, '/');
    }

    public static function publicPath(string $path = '/'): string
    {
        return self::publicBaseUrl().'/'.ltrim($path, '/');
    }
}
