<?php

namespace MagicObject\Util;

use MagicObject\Exceptions\InvalidInputFormatException;

class PicoPasswordUtil{
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
    
    private $hashAlgorithm = 'sha512';
    
    public function __construct($hashAlgorithm, $minLength, $regex)
    {
        $this->hashAlgorithm = $hashAlgorithm;
        $this->minLength = $minLength;
        $this->regex = $regex;
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
            return sprintf($this->regex, $this->minLength);
        }
        return $this->regex;
    }
    
    /**
     * Validate password
     *
     * @param string $password
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
     * @param string $password
     * @param boolean $binary
     * @return string
     */
    public function getHash($password, $binary = false)
    {
        $this->validate($password);
        return hash($this->hashAlgorithm, $password, $binary);
    }

    /**
     * Get regular expression format
     *
     * @return  string
     */ 
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Set regular expression format
     *
     * @param  string  $regex  Regular expression format
     *
     * @return  self
     */ 
    public function setRegex($regex)
    {
        $this->regex = $regex;

        return $this;
    }

    /**
     * Get minimum length of the password
     *
     * @return  integer
     */ 
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * Set minimum length of the password
     *
     * @param  integer  $minLength  Minimum length of the password
     *
     * @return  self
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
     * @return  self
     */ 
    public function setHashAlgorithm($hashAlgorithm)
    {
        $this->hashAlgorithm = $hashAlgorithm;

        return $this;
    }
}