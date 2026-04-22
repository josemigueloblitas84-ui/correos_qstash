<?php

use Illuminate\Contracts\Encryption\DecryptException;

if (! function_exists('encrypt_id')) {
    function encrypt_id(int|string $id): string
    {
        return encrypt((string) $id);
    }
}

if (! function_exists('decrypt_id')) {
    function decrypt_id(?string $encryptedId): int
    {
        try {
            if (!is_string($encryptedId) || $encryptedId === '') {
                abort(404);
            }

            return (int) decrypt($encryptedId);
        } catch (DecryptException $exception) {
            abort(404);
        }
    }
}
