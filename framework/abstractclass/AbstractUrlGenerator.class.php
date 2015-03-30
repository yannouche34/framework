<?php
namespace Framework\AbstractClass;

use Framework\AbstractClass\AbstractClass;

abstract class AbstractUrlGenerator extends AbstractClass{
    protected $urlList;
    
    public function __construct(){
        $this->urlList = new Collection();
    }
    
    public function getUrlMask($urlName){
        return $this->urlList->getItem($urlName);
    }
    
    public function extractDataFromUrl(){
        $i = 0;
        $nbUrl = $this->urlList->count();
        $isUrlFound = false;
        $matches = [];
        
        while($i < $nbUrl && !$isUrlFound){
            $currentUrl = $this->urlList->getItem($i);
            if(preg_match($currentUrl->getItem(0), $_SERVER['REQUEST_URI'], $matches)){
                $isUrlfound = $matches;
            }
            
            $i++;
        }
        
        return $isUrlFound;
    }
}