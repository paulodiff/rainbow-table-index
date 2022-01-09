<?php
namespace Paulodiff\RainbowTableIndex;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

// $h1 = Hash::make('test');
// $cr = Crypt::encryptString('test');
// $cr = Crypt::decryptString('test');

class RainbowTableIndexEncrypter
{

    public static function encrypt($s)
    {
        $enc_result = Crypt::encryptString($s);
        $encoded = base64_encode($enc_result);
        return $encoded;
    }

    public static function decrypt($s)
    {
        $decoded = base64_decode($s);
        $o = Crypt::decryptString($decoded);
        return $o;
    }

    public static function hash($s)
    {
        //$enc_result = Hash::make($s);
        //$encoded = base64_encode($enc_result);
        //return $encoded;
        return hash("md5", $s);
    }

    public static function hash_md5($s)
    {
        return hash("md5", $s);
    }

    /*
    public static function encrypt_sodium($s)
    {
        // Log::debug('Encrypter:encrypt ', [] );
        $k = self::getKey();
        $nonce = self::getNonce();
        $enc_result = sodium_crypto_secretbox( $s, $nonce, $k);
        $encoded = base64_encode( $enc_result );
        sodium_memzero($nonce);
        sodium_memzero($k);
        return $encoded;
    }

    public static function decrypt_sodium($s)
    {
        // Log::debug('Encrypter:decrypt ', [] );
        $k = self::getKey();
        $nonce = self::getNonce();
        $decoded = base64_decode($s);
        $o = sodium_crypto_secretbox_open($decoded, $nonce, $k);
        // $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        // $encrypted_result = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
        // $o = sodium_crypto_secretbox_open($encrypted_result, $nonce, $k);
        //$o = gzinflate($o);
        sodium_memzero($s);
        sodium_memzero($k);
        return $o;
    }

    public static function hash_sodium($s)
    {
        return sodium_bin2hex(sodium_crypto_generichash($s));
    }

  

    public static function short_hash($s)
    {
        return sodium_crypto_shorthash($s, self::getNonce());
    }

    protected static function getKey()
    {
        // $key = config('rainbowtable.key');
        // Log::debug('Encrypter:getKey ... from config', [$key] );
        return  sodium_base642bin(config('rainbowtableindex.key') , SODIUM_BASE64_VARIANT_ORIGINAL);
    }

    protected static function getNonce()
    {
        // $nonce = config('rainbowtable.nonce');
        // Log::debug('Encrypter:getNonce ... from config', [$nonce] );
        return  sodium_base642bin(config('rainbowtableindex.nonce') , SODIUM_BASE64_VARIANT_ORIGINAL);
    }
    */


}
