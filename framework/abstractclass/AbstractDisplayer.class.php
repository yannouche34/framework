<?php
namespace Framework\AbstractClass;

use Framework\AbstractClass\AbstractClass;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class AbstractDisplayer extends AbstractClass{
    protected $template;
    protected $templateVars;
    
    const TPL_FILENAME_FORMAT = "";
    
    public function __construct($template = ''){
        $this->template = $template;
        $this->templateVars = [];
    }
    
    public function display($inCacheFile = true){
        $content = $this->parseTemplate();
        
        foreach($this->templateVars as $key => $value){
            $$key = $value;
        }
        var_dump($content);
        ob_start();
        
        eval('?>' . $content);
        $finalContent = ob_get_contents();
        ob_end_flush();
        
        foreach($this->templateVars as $key){
            unset($$key);
        }
        
        if($inCacheFile){
            $file = $this->getFile();
            if($this->mustGenerateFile()){
                file_put_contents($file, $finalContent);
            }
        }
        else{
            echo $finalContent;
        }
        
        
    }
    
    private function parseTemplate(){
                
        $tplContent = file_get_contents($this->template);
        $patterns = [
            '#\{if (.+)\}#',
            '#\{/if\}#',
            '#\{else\}#'
        ];
        $replacements = [
            '<?php if($1):?>',
            '<?php endif;?>',
            '<?php else: ?>'
        ];
        
        $tplContent = preg_replace_callback('#\{\$(.+)\}#', [$this, 'insertVars'], $tplContent);
        $tplContent = preg_replace($patterns, $replacements, $tplContent);
        
        $matches = [];
        
        while(preg_match('#\{include path=[\'"](.+)[\'"]\}#', $tplContent, $matches)){
            var_dump($matches);
            $currentTpl = $this->template;
            $this->template = ROOT_PATH . $matches[1];
            $newContent = $this->parseTemplate();
            $tplContent = str_replace($matches[0], $newContent, $tplContent);
            
            $this->template = $currentTpl;
        }
        
        return $tplContent;
    }
    
    private function insertVars($matches){
        return $this->templateVars[$matches[1]];
    }
    
    private function mustCompileTemplate(){
        
    }
    
    abstract protected function getFile();
    abstract protected function mustGenerateFile();
}

