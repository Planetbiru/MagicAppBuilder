<?php

namespace MagicObject\Util;

use MagicObject\Exceptions\InvalidInputFormatException;

class PicoPasswordUtil{
    const ALG_MD2          = "md2";
    const ALG_MD4          = "md4";
    const ALG_MD5          = "md5";
    const ALG_SHA1         = "sha1";
    const ALG_SHA224       = "sha224";
    const ALG_SHA256       = "sha256";
    const ALG_SHA384       = "sha384";
    const ALG_SHA512_224   = "sha512/224";
    const ALG_SHA512_256   = "sha512/256";
    const ALG_SHA512       = "sha512";
    const ALG_SHA3_224     = "sha3-224";
    const ALG_SHA3_256     = "sha3-256";
    const ALG_SHA3_384     = "sha3-384";
    const ALG_SHA3_512     = "sha3-512";
    const ALG_RIPEMD128    = "ripemd128";
    const ALG_RIPEMD160    = "ripemd160";
    const ALG_RIPEMD256    = "ripemd256";
    const ALG_RIPEMD320    = "ripemd320";
    const ALG_WHIRLPOOL    = "whirlpool";
    const ALG_TIGER128_3   = "tiger128,3";
    const ALG_TIGER160_3   = "tiger160,3";
    const ALG_TIGER192_3   = "tiger192,3";
    const ALG_TIGER128_4   = "tiger128,4";
    const ALG_TIGER160_4   = "tiger160,4";
    const ALG_TIGER192_4   = "tiger192,4";
    const ALG_SNEFRU       = "snefru";
    const ALG_SNEFRU256    = "snefru256";
    const ALG_GOST         = "gost";
    const ALG_GOST_CRYPTO  = "gost-crypto";
    const ALG_ADLER32      = "adler32";
    const ALG_CRC32        = "crc32";
    const ALG_CRC32B       = "crc32b";
    const ALG_CRC32C       = "crc32c";
    const ALG_FNV132       = "fnv132";
    const ALG_FNV1A32      = "fnv1a32";
    const ALG_FNV164       = "fnv164";
    const ALG_FNV1A64      = "fnv1a64";
    const ALG_JOAAT        = "joaat";
    const ALG_HAVAL128_3   = "haval128,3";
    const ALG_HAVAL160_3   = "haval160,3";
    const ALG_HAVAL192_3   = "haval192,3";
    const ALG_HAVAL224_3   = "haval224,3";
    const ALG_HAVAL256_3   = "haval256,3";
    const ALG_HAVAL128_4   = "haval128,4";
    const ALG_HAVAL160_4   = "haval160,4";
    const ALG_HAVAL192_4   = "haval192,4";
    const ALG_HAVAL224_4   = "haval224,4";
    const ALG_HAVAL256_4   = "haval256,4";
    const ALG_HAVAL128_5   = "haval128,5";
    const ALG_HAVAL160_5   = "haval160,5";
    const ALG_HAVAL192_5   = "haval192,5";
    const ALG_HAVAL224_5   = "haval224,5";
    const ALG_HAVAL256_5   = "haval256,5";

    /**
     * Regular expression format
     *
     * @var string
     */
    private $regex = '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{%d,}$/';

    /**
     * Minimum length of the password
     *
     * @var integer
     */
    private $minLength = 8;

    private $hashAlgorithm = self::ALG_SHA1;

    public function __construct($hashAlgorithm = null, $minLength = 8, $regex = null)
    {
        if(isset($hashAlgorithm))
        {
            $this->hashAlgorithm = $hashAlgorithm;
        }
        if($minLength > 0)
        {
            $this->minLength = $minLength;
        }
        if(isset($regex))
        {
            $this->regex = $regex;
        }
    }

    /**
     * Get regular expression
     *
     * @return string
     */
    private function getRegexString()
    {
        if(stripos($this->regex, '%d') !== false)
        {
            return str_replace('%d', $this->minLength, $this->regex);
        }
        return $this->regex;
    }

    /**
     * Validate password
     *
     * @param string $password Password to be validated
     * @return true
     * @throws InvalidInputFormatException
     */
    public function validate($password)
    {
        if(strlen($password) < $this->minLength)
        {
            throw new InvalidInputFormatException("Invalid password length");
        }
        if(!preg_match($this->getRegexString(), $password))
        {
            throw new InvalidInputFormatException("Invalid password format");
        }
        return true;
    }

    /**
     * Get password hash
     *
     * @param string $password Plain text
     * @param boolean $binary Flag that result is binary
     * @param boolean $validate Flag to validate password
     * @return string
     */
    public function getHash($password, $binary = false, $validate = true)
    {
        if($validate)
        {
            $this->validate($password);
        }
        return hash($this->hashAlgorithm, $password, $binary);
    }

    /**
     * Get regular expression format
     *
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Set regular expression format
     *
     * @param string  $regex  Regular expression format
     *
     * @return self
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;

        return $this;
    }

    /**
     * Get minimum length of the password
     *
     * @return integer
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * Set minimum length of the password
     *
     * @param integer $minLength Minimum length of the password
     *
     * @return self
     */
    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;

        return $this;
    }

    /**
     * Get the value of hashAlgorithm
     */
    public function getHashAlgorithm()
    {
        return $this->hashAlgorithm;
    }

    /**
     * Set the value of hashAlgorithm
     *
     * @return self
     */
    public function setHashAlgorithm($hashAlgorithm)
    {
        $this->hashAlgorithm = $hashAlgorithm;

        return $this;
    }
}