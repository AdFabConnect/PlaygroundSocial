<?php
namespace PlaygroundSocial\Cron;

class Cron
{

    /*
    For use :

    $this->log('Hello World', CronController::ERROR);
    $this->log('Hello World', CronController::WARN);
    $this->log('Hello World', CronController::DEBUG);
    $this->log('Hello World', CronController::SUCCESS);

    public function log($message, $level)
    { 
        CronController::log($message, $level);
    }
    
    */
    
    const ERROR = "error";
    const WARN = "warning";
    const DEBUG = "debug";
    const SUCCESS = "success";

    public static function log($message, $level)
    {  
        $obj = new self();
        $obj->$level($message);
    }   

    public function success($message)
    {
        // echo "\033[32m".$message."\033[37m"."\n";
    }

    public function debug($message)
    {
        // echo "\033[34m".$message."\033[37m"."\n";
    }

    public function warning($message)
    {
        // echo "\033[33m".$message."\033[37m"."\n";
    }

    public function error($message)
    {
        // echo "\033[31m".$message."\033[37m"."\n";
    }
   
}