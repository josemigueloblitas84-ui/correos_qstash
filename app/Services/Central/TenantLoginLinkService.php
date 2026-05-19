<?php

namespace App\Services\Central;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Agenda\InstitucionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantLoginLinkService
{
    public function __construct(private InstitucionService $institucionService)
    {
    }

    public function createLoginUrl(Tenant $tenant, User $centralUser, Request $request): ?string
    {
        $domain = $this->institucionService->primaryDomain($tenant);

        if (! $domain) {
            return null;
        }

        $token = Str::random(80);

        DB::table('tenant_login_tokens')->insert([
            'token_hash' => hash('sha256', $token),
            'tenant_id' => $tenant->id,
            'central_user_id' => $centralUser->id,
            'central_user_name' => $centralUser->name,
            'central_user_email' => $centralUser->email,
            'expires_at' => now()->addMinute(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->buildTenantUrl($domain, $request, '/central-login/' . $token);
    }

    private function buildTenantUrl(string $domain, Request $request, string $path): string
    {
        $host = parse_url('http://' . $domain, PHP_URL_HOST) ?: $domain;
        $domainPort = parse_url('http://' . $domain, PHP_URL_PORT);
        $requestPort = $request->getPort();
        $port = $domainPort ?: $requestPort;
        $scheme = $request->getScheme();

        $portSuffix = in_array((int) $port, [80, 443], true) ? '' : ':' . $port;

        return $scheme . '://' . $host . $portSuffix . $path;
    }
}
