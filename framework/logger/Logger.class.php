<?php
namespace Framework\Logger;

use \DateTime;
use \Exception;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Logger{
    private $level;
    private $file;
    private $currentDateTime;
    
    const DEBUG = 0x01;
    const INFO = 0x02;
    const WARN = 0x04;
    const ERROR = 0x08;
    
    public function __construct(){
        $this->setCurrentDateTime(new DateTime());
        
        $configVars = parse_ini_file(ROOT_PATH . '/framework/config/log.ini');
        $fileName = str_replace('%level%', $configVars['level'], str_replace('%date%', $this->getCurrentDateTime()->format($configVars['date_format']), ROOT_PATH . $configVars['log_path'] . $configVars['file_name']));
        
        $this->setLevel($configVars['level']);
        $this->setFile($fileName);
    }
    
    public function debug($stringToLog){
        if($this->getLevel() & Logger::DEBUG){
            $finalStringtoLog = '[DEBUG] ' . $this->getCurrentDateTime()->format('Y-m-d H:i:s') . ': ' . $stringToLog . "\n";
            $this->log($finalStringtoLog);
        }
    }
    
    public function info($stringToLog){
        if($this->getLevel() & Logger::INFO){
            $finalStringtoLog = '[INFO] ' . $this->getCurrentDateTime()->format('Y-m-d H:i:s') . ': ' . $stringToLog . "\n";
            $this->log($finalStringtoLog);
        }
    }
    
    public function warn($stringToLog){
        if($this->getLevel() & Logger::WARN){
            $finalStringtoLog = '[WARN] ' . $this->getCurrentDateTime()->format('Y-m-d H:i:s') . ': ' . $stringToLog . "\n";
            $this->log($finalStringtoLog);
        }
    }
    
    public function error($stringToLog){
        if($this->getLevel() & Logger::ERROR){
            $finalStringtoLog = '[ERROR] ' . $this->getCurrentDateTime()->format('Y-m-d H:i:s') . ': ' . $stringToLog . "\n";
            $this->log($finalStringtoLog);
        }
    }
    
    public function getLevel() {
        return $this->level;
    }

    public function setLevel($level) {
        switch($level){
            case 'debug':
                $this->level = Logger::ERROR |+ Logger::WARN |+ Logger::INFO |+ Logger::DEBUG;
                break;
            case 'info':
                $this->level = Logger::ERROR |+ Logger::WARN |+ Logger::INFO;
                break;
            case 'warn':
                $this->level = Logger::ERROR |+ Logger::WARN;
                break;
            case 'error':
                $this->level = Logger::ERROR;
                break;
        }
    }
    
    public function getFile() {
        return $this->file;
    }

    public function setFile($file) {
        $this->file = $file;
    }
    public function getCurrentDateTime() {
        return $this->currentDateTime;
    }

    public function setCurrentDateTime($currentDateTime) {
        $this->currentDateTime = $currentDateTime;
    }

    private function log($stringToLog){
        try{
            $content = (file_exists($this->getfile())) ? file_get_contents($this->getFile()) : '';
            file_put_contents($this->getFile(), $content . $stringToLog);
        } 
        catch (Exception $ex) {

        }
    }
}

