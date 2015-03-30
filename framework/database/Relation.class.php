<?php
namespace Framework\Database;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Relation{
    private $relationType;
    private $entity;
    private $targetEntity;
    private $attribute;
    
    CONST ONE_TO_MANY = 'OneToMany';
    CONST MANY_TO_ONE = 'ManytoOne';
    CONST MANY_TO_MANY = 'ManyToMany';
    
    public function __construct($type, $entity, $targetEntity, $attribute){
        if(is_null($type) || is_null($entity) || is_null($targetEntity) || is_null($attribute)){
            throw new \Exception('Entity relation construction failed');
        }
        else{
            $this->setRelationType($type);
            $this->setEntity($entity);
            $this->setTargetEntity($targetEntity);
            $this->setAttribute($attribute);
        }
    }
    
    public function getRelationType() {
        return $this->relationType;
    }

    public function getEntity() {
        return $this->entity;
    }

    public function getTargetEntity() {
        return $this->targetEntity;
    }

    public function getAttribute() {
        return $this->attribute;
    }

    public function setRelationType($relationType) {
        $this->relationType = $relationType;
    }

    public function setEntity($entity) {
        $this->entity = $entity;
    }

    public function setTargetEntity($targetEntity) {
        $this->targetEntity = $targetEntity;
    }

    public function setAttribute($attribute) {
        $this->attribute = $attribute;
    }
}

