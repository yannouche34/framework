<?php
namespace Framework\AbstractClass;

use Framework\Types\Collection;

abstract class AbstractManager extends AbstractClass{
    protected $entities;
    
    public function __construct(){
        $this->entities = new Collection();
    }
    
    public function addEntity($attribute, $entityName){
        $this->entities->addItem($entityName, $attribute);
    }
    
    public function __call($name, $arguments) {
        $matches = [];
        
        if(preg_match('#get(\w+)ById#i', $name, $matches)){
            $attribute = lcfirst($matches[1]);
            
            if(property_exists($this, $attribute)){
                if(is_null($this->$attribute)){
                    $className = $this->entities->getItem($attribute)->getItem(0);
                    $this->$attribute = new $className();
                }
                
                $this->$attribute->read($arguments[0]);
                
                return $this->$attribute;
            }
        }
        
        if(preg_match('#^findAll(\w+)By(\w+)$#i', $name, $matches)){
            $attribute = lcfirst($matches[1]);
            
            if(property_exists($this, lcfirst($matches[1]))){
                if(is_null($this->$attribute)){
                    $className = $this->entities->getItem($attribute)->getItem(0);
                    $this->$attribute = new $className();
                }
                
                if(property_exists($this->$attribute, lcfirst($matches[2]))){
                    $entities = $this->$attribute->findAllByAttribute(lcfirst($matches[2]), $arguments[0]);
                    return $entities;
                }
            } 
        }
        
        if(preg_match('#^findOne(\w+)By(\w+)$#i', $name, $matches)){
            $attribute = lcfirst($matches[1]);
            
            if(property_exists($this, lcfirst($matches[1]))){
                if(is_null($this->$attribute)){
                    $className = $this->entities->getItem($attribute)->getItem(0);
                    $this->$attribute = new $className();
                }
                
                if(property_exists($this->$attribute, lcfirst($matches[2]))){
                    $entities = $this->$attribute->findAllByAttribute(lcfirst($matches[2]), $arguments[0]);
                    return $entities->getItem(0);
                }
            } 
        }
    }
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

