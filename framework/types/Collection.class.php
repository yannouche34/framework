<?php
namespace Framework\Types;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Collection implements \IteratorAggregate {
    private $_content;
    
    public function __construct($keys = array()){
        $this->_content = [];
        
        if(is_array($keys) && !empty($keys)){
            foreach($keys as $key){
                $this->_content[$key] = new Collection();
            }
        }
    }
    
    public function addItem($item, $key = null){
        if((is_null($key))){
            $this->_content[] = $item;
        }
        else{
            if(!isset($this->_content[$key])){
                $this->_content[$key] = new Collection();
            }
            
            $this->_content[$key]->addItem($item);
        }
    }
    
    public function removeItem($position){
        if(array_key_exists($position, $this->_content)){
            unset($this->_content[$position]);
        }

    }
    
    public function keyExists($key){
        return array_key_exists($key, $this->_content);
    }
    
    public function getItem($position){
        return ($this->keyExists($position)) ? $this->_content[$position] : false;
    }
    
    public function getIterator(){
        return new \ArrayIterator($this->_content);
    }
    
    public function merge($otherCollection){
        $reflexion = new \ReflectionClass('Framework\Types\Collection');
        $attribute = $reflexion->getProperty('_content');
        $attribute->setAccessible(true);
        $content = $attribute->getValue($otherCollection);
        array_merge($this->_content, $content);
    }
    
    public function count(){
        return count($this->_content);
    }
}
