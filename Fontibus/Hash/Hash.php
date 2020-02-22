<?php
namespace Fontibus\Hash;

use Exception;

class Hash extends UnsafeHash {

    const HASH_ALGO = 'sha256';

    /**
     * Hashes message using Bcrypt
     *
     * @param string $message - plaintext message
     * @return string
     */
    public static function bcrypt(string $message): string {
        return password_hash($message, PASSWORD_BCRYPT);
    }

    /**
     * Encrypts then MACs a message
     *
     * @param string $message - plaintext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encode - set to TRUE to return a base64-encoded string
     * @return string (raw binary)
     */
    public static function encrypt($message, $key, $encode = false) {
        list($encKey, $authKey) = self::splitKeys($key);
        $ciphertext = parent::encrypt($message, $encKey);
        $mac = hash_hmac(self::HASH_ALGO, $ciphertext, $authKey, true);
        if ($encode)
            return base64_encode($mac.$ciphertext);

        return $mac.$ciphertext;
    }

    /**
     * Decrypts a message (after verifying integrity)
     *
     * @param string $message - ciphertext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encoded - are we expecting an encoded string?
     * @return string (raw binary)
     * @throws Exception
     */
    public static function decrypt($message, $key, $encoded = false) {
        list($encKey, $authKey) = self::splitKeys($key);
        if ($encoded) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new Exception('Encryption failure', 500);
            }
        }

        $hs = mb_strlen(hash(self::HASH_ALGO, '', true), '8bit');
        $mac = mb_substr($message, 0, $hs, '8bit');
        $ciphertext = mb_substr($message, $hs, null, '8bit');
        $calculated = hash_hmac(
            self::HASH_ALGO,
            $ciphertext,
            $authKey,
            true
        );

        if (!self::hashEquals($mac, $calculated))
            throw new Exception('Encryption failure', 500);

        return parent::decrypt($ciphertext, $encKey);
    }

    /**
     * Splits a key into two separate keys; one for encryption
     * and the other for authenticaiton
     *
     * @param string $masterKey (raw binary)
     * @return array (two raw binary strings)
     */
    protected static function splitKeys($masterKey) {
        return [
            hash_hmac(self::HASH_ALGO, 'ENCRYPTION', $masterKey, true),
            hash_hmac(self::HASH_ALGO, 'AUTHENTICATION', $masterKey, true)
        ];
    }

    /**
     * Compare two strings without leaking timing information
     *
     * @param string $a
     * @param string $b
     * @ref https://paragonie.com/b/WS1DLx6BnpsdaVQW
     * @return boolean
     */
    protected static function hashEquals($a, $b) {
        if (function_exists('hash_equals'))
            return hash_equals($a, $b);

        $nonce = openssl_random_pseudo_bytes(32);
        return hash_hmac(self::HASH_ALGO, $a, $nonce) === hash_hmac(self::HASH_ALGO, $b, $nonce);
    }

}