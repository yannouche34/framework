<?php
namespace Framework\AbstractClass;

use Framework\Logger\Logger;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class AbstractClass{
    protected $logger;
    
    public function __construct(){
        $this->setLogger(new Logger());
    }
    public function getLogger() {
        return $this->logger;
    }

    public function setLogger($logger) {
        $this->logger = $logger;
    }
    
    public function toArray(){
        
        $properties = $this->getObjectProperties();
        $finalData = [];
        
        foreach($properties as $property){
            $value = $property->getValue();
            
            if(is_object($value)){
                $value = $value->toArray();
            }
            
            $finalData[$property->getName()] = $value;
        }
        
        return $finalData;
    }
    
    public function toXML(){
        $properties = $this->getObjectProperties();
        $finalData = '<' . get_class($this) . '>';
        
        foreach($properties as $property){
            $value = $property->getValue();
            
            if(is_object($value)){
                $value = $value->toString();
            }
            if(is_array($value)){
                $value = print_r($value, true);
            }
            
            $finalData .= '<' . $property->getName() . '>' . $value . '</' . $property->getName() . '>'; 

        }
        
        return $finalData;
    }
    
    public function toString(){
        $dataAsArray = $this->toArray();
        return print_r($dataAsArray, true);
    }
    
    private function getObjectProperties(){
        $object = new ReflectionClass($this);
        $properties = $object->getProperties();
        
        return $properties;
    }
}