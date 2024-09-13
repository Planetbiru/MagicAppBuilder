<?php

namespace MagicApp\Config;

use MagicObject\Exceptions\InvalidParameterException;
use MagicObject\SecretObject;

class ConfigEncrypter
{
    /**
     * Callback password
     *
     * @var callable
     */
    private $callbaskPassword;
    
    /**
     * Constructor
     *
     * @param callable $callbaskPassword Callback function
     */
    public function __construct($callbaskPassword)
    {
        if(isset($callbaskPassword) && is_callable($callbaskPassword))
        {
            $this->callbaskPassword = $callbaskPassword;
        }
        else
        {
            throw new InvalidParameterException("Callback function is required");
        }
    }
    /**
     * Encrypt configuration
     *
     * @param string $inputPath Input configuration path
     * @param string $outputPath Output configuration path
     * @return boolean
     */
    public function encryptConfig($inputPath, $outputPath)
    {
        if(file_exists($inputPath))
        {
            $config = new SecretObject();
            $config->loadYamlFile($inputPath);
            
            $database = new SecretDatabaseWriter($config->getDatabase(), $this->callbaskPassword);     
            $mailer = new SecretMailerWriter($config->getMailer(), $this->callbaskPassword);
            $session = new SecretSessionWriter($config->getSession(), $this->callbaskPassword);
            $redis = new SecretRedisWriter($config->getRedis(), $this->callbaskPassword);
            
            $config->setDatabase(new SecretObject($database->value()));
            $config->setMailer(new SecretObject($mailer->value()));
            $config->setSession(new SecretObject($session->value()));
            $config->setRedis(new SecretObject($redis->value()));
            file_put_contents($outputPath, $config->dumpYaml());
            return true;
        }
        return false; 
    }
    
    /**
     * Decrypt configuration
     *
     * @param string $inputPath Input configuration path
     * @param string $outputPath Output configuration path
     * @return boolean
     */
    public function decryptConfig($inputPath, $outputPath)
    {
        if(file_exists($inputPath))
        {
            $config = new SecretObject();
            $config->loadYamlFile($inputPath);
            
            $database = new SecretDatabaseReader($config->getDatabase(), $this->callbaskPassword);
            $mailer = new SecretMailerReader($config->getMailer(), $this->callbaskPassword);
            $session = new SecretSessionReader($config->getSession(), $this->callbaskPassword);
            $redis = new SecretRedisReader($config->getRedis(), $this->callbaskPassword);
            
            $config->setDatabase(new SecretObject($database->value()));
            $config->setMailer(new SecretObject($mailer->value()));
            $config->setSession(new SecretObject($session->value()));
            $config->setRedis(new SecretObject($redis->value()));
            
            file_put_contents($outputPath, $config->dumpYaml());
            return true;
        }
        return false; 
    }
}