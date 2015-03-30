<?php
namespace Framework\Database;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Mapping{
    private $attribute;
    private $column;
    
    public function __construct($attribute, $column){
        $this->setAttribute($attribute);
        $this->setColumn($column);
    }
    public function getAttribute() {
        return $this->attribute;
    }

    public function getColumn() {
        return $this->column;
    }

    public function setAttribute($attribute) {
        $this->attribute = $attribute;
    }

    public function setColumn($column) {
        $this->column = $column;
    }
}