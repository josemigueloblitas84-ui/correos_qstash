<?php

namespace App\Services\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(
        string $description,
        array $properties = [],
        ?Model $subject = null,
        ?Authenticatable $causer = null,
        string $logName = 'default',
        ?string $event = null
    ): void {
        $logger = activity($logName);

        $causer ??= auth()->user();

        if ($causer !== null) {
            $logger->causedBy($causer);
        }

        if ($subject !== null) {
            $logger->performedOn($subject);
        }

        $requestData = [];

        if (app()->bound('request')) {
            $request = request();
            $requestData = array_filter([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);
        }

        $payload = $properties;

        if ($requestData !== []) {
            $payload['request'] = $requestData;
        }

        if ($payload !== []) {
            $logger->withProperties($payload);
        }

        if ($event !== null) {
            $logger->event($event);
        }

        $logger->log($description);
    }
}
