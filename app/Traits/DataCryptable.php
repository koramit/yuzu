<?php

namespace App\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

trait DataCryptable
{
    /**
     * Use Illuminate\Contracts\Encryption\Encrypter to encrypt data.
     *
     * @param  string  $value
     * @return string|null
     */
    public function encryptField($value)
    {
        return $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Use Illuminate\Contracts\Encryption\Encrypter to decrypt data.
     *
     * @param  string  $value
     * @return string|null
     */
    public function decryptField($value)
    {
        if (is_null($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            //
        }
    }

    /**
     * Use hmac with sha256 algorithm to hash data then get small portion by substr.
     *
     * @param  string  $value
     * @return string|null
     */
    public function miniHash($value)
    {
        return substr(
                hash_hmac('sha256', $value, config('app.key')),
                config('app.mini_hash_start_at'),
                config('app.mini_hash_length')
            );
    }

    public static function findByEncryptedKey($value)
    {
        $keyName = (new static)->encryptedKey;
        $cahceKey = "{$keyName}-{$value}";
        if (Cache::has($cahceKey)) {
            return Cache::get("{$keyName}-{$value}");
        }

        $records = static::select('id', $keyName)->where('mini_hash', (new static)->miniHash($value))->get();

        foreach ($records as $record) {
            if ($record->$keyName == $value) {
                $record = static::find($record->id);
                Cache::put($cahceKey, $record, 300);

                return $record;
            }
        }

        return null;
    }

    public function updateCache()
    {
        $key = $this->encryptedKey;
        Cache::put("{$key}-{$this->$key}", $this, now()->addDays(15));
    }
}
