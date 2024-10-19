<?php

namespace MagicObject\Util;

use MagicObject\Exceptions\InvalidInputFormatException;

/**
 * Class PicoPasswordUtil
 *
 * A utility class for handling password management tasks, including validation, hashing, and enforcing security policies.
 *
 * This class provides methods to validate passwords based on customizable rules, including minimum length and complexity requirements.
 * It also offers functionality to hash passwords using various cryptographic algorithms to ensure secure storage.
 *
 * Passwords can be validated against a regular expression that enforces specific character types (uppercase, lowercase, numbers, and special characters).
 * Users can configure the minimum length and the hashing algorithm used to generate password hashes.
 *
 * Example usage:
 * ```
 * $passwordUtil = new PicoPasswordUtil();
 * $passwordUtil->validate('YourSecureP@ssw0rd!');
 * $hashedPassword = $passwordUtil->getHash('YourSecureP@ssw0rd!');
 * ```
 * 
 * @author Kamshory
 * @package MagicObject\Util
 * @link https://github.com/Planetbiru/MagicObject
 */
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
     * Regular expression format for password validation.
     *
     * This regex is used to enforce complexity rules for passwords,
     * including the presence of uppercase letters, lowercase letters,
     * numbers, and special characters.
     *
     * @var string
     */
    private $regex = '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{%d,}$/';

    /**
     * Minimum length of the password.
     *
     * This sets the minimum number of characters required for a valid password.
     *
     * @var int
     */
    private $minLength = 8;

    /**
     * Hash algorithm to be used for password hashing.
     *
     * This determines which hashing algorithm will be applied when generating
     * password hashes.
     *
     * @var string
     */
    private $hashAlgorithm = self::ALG_SHA1;

    /**
     * Constructor to initialize password utility settings.
     *
     * @param string|null $hashAlgorithm Optional hashing algorithm to use.
     * @param int $minLength Minimum length of the password. Default is 8.
     * @param string|null $regex Optional regex pattern for password validation.
     */
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
     * Get the regular expression string used for password validation.
     *
     * The method replaces the placeholder '%d' with the minimum length
     * defined for the password.
     *
     * @return string The regex string with the minimum length.
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
     * Validate the given password against the set rules.
     *
     * This method checks if the password meets the length requirement
     * and matches the defined complexity rules. Throws an exception
     * if the password is invalid.
     *
     * @param string $password Password to be validated.
     * @return bool True if the password is valid.
     * @throws InvalidInputFormatException If the password is invalid.
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
     * Get the hash of the given plain text password.
     *
     * This method hashes the password using the specified hashing algorithm.
     * Optionally validates the password before hashing.
     *
     * @param string $password Plain text password to be hashed.
     * @param bool $binary Optional. If true, returns the binary representation of the hash.
     * @param bool $validate Optional. If true, validates the password before hashing.
     * @return string The resulting hashed password.
     * @throws InvalidInputFormatException If the password is invalid and validation is enabled.
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
     * Get the current regular expression format used for password validation.
     *
     * @return string The current regex format.
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Set a new regular expression format for password validation.
     *
     * @param string $regex New regular expression format.
     * @return self Returns the current instance for method chaining.
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;

        return $this;
    }

    /**
     * Get the minimum length required for passwords.
     *
     * @return int The minimum length for passwords.
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * Set a new minimum length requirement for passwords.
     *
     * @param int $minLength New minimum length for passwords.
     * @return self Returns the current instance for method chaining.
     */
    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;

        return $this;
    }

    /**
     * Get the currently set hash algorithm.
     *
     * @return string The hashing algorithm in use.
     */
    public function getHashAlgorithm()
    {
        return $this->hashAlgorithm;
    }

    /**
     * Set a new hash algorithm to be used for password hashing.
     *
     * @param string $hashAlgorithm New hashing algorithm.
     * @return self Returns the current instance for method chaining.
     */
    public function setHashAlgorithm($hashAlgorithm)
    {
        $this->hashAlgorithm = $hashAlgorithm;

        return $this;
    }
}