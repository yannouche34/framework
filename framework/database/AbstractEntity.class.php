<?php

namespace Framework\Database;

use \DateTime;
use \Exception;

use Framework\Database\PDOFactory;
use Framework\Types\Collection;
use Framework\AbstractClass\AbstractClass;

/*
 * TODO: gestion manytomany
 */

abstract class AbstractEntity extends AbstractClass{

    protected $idAttribute;
    protected $tableName;
    protected $queries;
    protected $fieldsMapping;
    protected $relations;
    public $dateCreation;
    public $dateModification;

    public function __construct() {
        parent::__construct();
        
        $logger = $this->getLogger();
        $logger->debug('Construct new Entity');
        
        $mappings = new Collection();
        $mappings->addItem(new Mapping('dateCreation', 'date_creation'));
        $mappings->addItem(new Mapping('dateModification', 'date_modification'));
        
        $this->setFieldsMapping($mappings);
        $this->setQueries(new Collection());
        $this->setRelations(new Collection());
    }

    public function getIdAttribute() {
        return $this->idAttribute;
    }

    public function getTableName() {
        return $this->tableName;
    }

    public function getQueries() {
        return $this->queries;
    }

    public function getFieldsMapping() {
        return $this->fieldsMapping;
    }

    public function setIdAttribute($id) {
        $this->idAttribute = $id;
    }

    public function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    public function setQueries($queries) {
        $this->queries = $queries;
    }

    public function setFieldsMapping($fieldsMapping) {
        $this->fieldsMapping = $fieldsMapping;
    }

    public function getRelations() {
        return $this->relations;
    }

    public function getEntitiesByRelations() {
        $logger = $this->getLogger();
        $logger->debug('Getting current entity relations');
        
        try{
            $relations = $this->getRelations();

            $logger->debug($relations->count() . ' relations found');

            foreach ($relations as $relation) {
                switch ($relation->getRelationType()) {
                    case Relation::ONE_TO_MANY:
                        $this->{$relation->getAttribute()} = $this->getOnetoMany($relation);
                        break;
                    case Relation::MANY_TO_ONE:
                        $this->{$relation->getAttribute()} =  $this->getManyToOne($relation);
                        break;
                    case Relation::MANY_TO_MANY:
                        $this->{$relation->getAttribute()} = $this->getManyToMany($relation);
                        break;
                }
            }
        }
        catch (Exception $ex) {
            $logger->error($ex->getMessage());
        }
    }

    public function setRelations($relations) {
        $this->relations = $relations;
    }

    private function getFields() {
        $logger = $this->getLogger();
        $logger->debug('Getting fields for queries');
        
        try{
            $properties = [];
            $excludedProperties = ['idAttribute', 'tableName', 'queries', 'fieldsMapping', 'relations', 'logger'];
            $logger->debug('Excluded properties: ' . print_r($excludedProperties, true));
            
            //Whe also get attributes for the One To Many relations because they don't have column on base
            $logger->debug('Removing One to Many relations attributes (see comments)');
            foreach ($this->getRelations() as $relation) {
                if ($relation->getRelationType() == 'oneToMany') {
                    $excludedProperties[] = $relation->getAttribute();
                }
            }

            $reflexion = New \ReflectionClass(get_class($this));

            foreach ($reflexion->getProperties() as $attribute) {
                $name = $attribute->getName();
                if (!in_array($name, $excludedProperties)) {
                    $properties[] = $name;
                }
            }

            $logger->debug('Final properties collection returned: ' . print_r($properties, true));
            return $properties;
        } 
        catch (Exception $e) {
            $logger->error($e->getMessage());
        }
        
    }

    public function read($id, $withRelations = true) {
        $logger = $this->getLogger();
        $logger->debug('Reading entity from base');
        
        try{
            
            $logger->debug('Generate query');
            
            $query = '';
            $mappings = $this->getAllMappings();
            $fields = $this->getFields();
            
            foreach ($mappings as $mapping) {
                if (($position = array_search($mapping->getAttribute(), $fields)) !== false) {
                    $fields[$position] = '`' . $mapping->getColumn() . '` AS `' . $mapping->getAttribute() . '`';
                }
            }
            
            $query = 'SELECT ' . implode(', ', $fields) . ' FROM `' . $this->getTableName() . '` WHERE `' . $this->getIdField() . '` = ?';
            $logger->debug('query generated: ' . $query);
            
            $logger->debug('Executing query and fetching into current object');
            
            $pdo = PDOFactory::getInstance(PDOFactory::READ);
            $statement = $pdo->prepare($query);
            $statement->setFetchMode(\PDO::FETCH_INTO, $this);

            $statement->execute([$id]);
            $statement->fetch();

            if ($withRelations) {
                $logger->debug('Getting relations for the current entity');
                $this->getEntitiesByRelations();
            }
        }
        catch(Exception $e){
            $logger->error($e->getMessage());
        }
    }

    public function write($withRelations = true) {
        $logger = $this->getLogger();
        $logger->debug('Write entity data into base');
        
        try{
            $mappings = $this->getAllMappings();
            $fields = $this->getFields();

            $columns = [];
            $markers = [];
            $values = [];

            foreach($fields as $field){
                if($field != $this->getIdAttribute()){
                    $columns[] = $field;
                    $markers[] = ':' . $field;
                    $values[':' . $field] = $this->$field;
                }
            }

            if(!$this->{$this->getIdAttribute()}){
                $query = 'INSERT INTO `' . $this->getTableName() . '` (`' . implode('`, `', $columns) . '`) VALUES(' . implode(', ', $markers) . ')';
                $this->setDateCreation(new DateTime());
                $values[':dateCreation'] = $this->getDateCreation()->format('Y-m-d H:i:s');
            }
            else{
                $columnstoUpdate = [];
                $values[':' . $this->getIdAttribute()] = $this->{$this->getIdAttribute()};
                for($i = 0; $i < count($columns); $i++){
                    $columnstoUpdate[] = '`' . $columns[$i] . '` = ' . $markers[$i];
                }

                $query = 'UPDATE `' . $this->getTableName() . '` SET ' . implode(', ', $columnstoUpdate) . ' WHERE `' . $this->getIdAttribute() . '` = :' . $this->getIdAttribute();
                $this->setDateModification(new DateTime());
                $values[':dateModification'] = $this->getDateModification()->format('Y-m-d H:i:s');
            }

            foreach($mappings as $mapping){
                $query = str_replace('`' . $mapping->getAttribute() . '`', '`' . $mapping->getColumn() . '`', $query);
            }

            $pdo = PDOFactory::getInstance(PDOFactory::WRITE);
            $statement = $pdo->prepare($query);
            $statement->execute($values);

            if(withRelations){
                foreach ($this->relations as $relation){
                    if($relation->getRelationType() == Relation::One_TO_MANY){
                        $entities = $relation->getAttribute();

                        foreach($entities as $entity){
                            $entity->write();
                        }
                    }
                }
            }

        } catch (Exception $e) {
            $logger->error($e->getMessage());
        }
        
        
    }

    public function delete($destruct = true) {
        $query = 'DELETE FROM `' . $this->getTableName() . '` WHERE `' . $this->getIdField() . '` = ?';

        if (IS_DEV) {
            echo $query . "\n";
        } else {
            $pdo = PDOFactory::getInstance(PDOFactory::WRITE);
            $statement = $pdo->prepare($query);
            $statement->execute([$this->{$this->getIdAttribute()}]);
        }

        if ($destruct)
            unset($this);
    }

    public function duplicate($write = false) {
        $this->{$this->getIdAttribute()} = null;

        if ($write)
            $this->write();

        return $this;
    }

    public function findAllByAttribute($attribute, $value) {
        $query = '';
        $mappings = $this->getAllMappings();
        $fields = $this->getFields();

        foreach ($mappings as $mapping) {
            if (($position = array_search($mapping->getAttribute(), $fields)) !== false) {
                $fields[$position] = '`' . $mapping->getColumn() . '` AS `' . $mapping->getAttribute() . '`';
                if($mapping->getAttribute() == $attribute){
                    $attribute = $mapping->getColumn();
                }
            }
        }

        $query = 'SELECT ' . implode(', ', $fields) . ' FROM `' . $this->getTableName() . '` WHERE `' . $attribute . '` = ?';

        $pdo = PDOFactory::getInstance(PDOFactory::READ);
        $statement = $pdo->prepare($query);

        $statement->execute([$value]);
        $result = $statement->fetchAll(\PDO::FETCH_CLASS, \get_class($this));
        $entities = new Collection();
        
        foreach($result as $item){
            $entities->addItem($item);
        }
        
        return $entities;
    }

    private function getIdField() {
        $mappings = $this->getAllMappings();
        $column = $this->getIdAttribute();

        foreach ($mappings as $mapping) {
            if ($this->getIdAttribute() == $mapping->getAttribute()) {
                $column = $mapping->getColumn();
            }
        }

        return $column;
    }

    private function getOneToMany($relation){
        $logger = $this->getLogger();
        $logger->debug('Getting a One To Many entity');
        
        try{
            $entityClass = $relation->getEntity();
            $entity = new $entityClass();
            $logger->debug('Entity class: ' . $entityClass);
            $logger->debug('Trying to get all relatives ' . $entityClass . 'whith id atribute: ' . $relation->getTargetEntity() . ' and value: ' . $this->{$this->getIdAttribute()});
            $entities = $entity->findAllByAttribute($relation->getTargetEntity(), $this->{$this->getIdAttribute()});
            
            $logger->debug($entities->count() . 'entities found');
            
            $collection = new Collection();

            foreach ($entities as $e) {
                $e->{$relation->getTargetEntity()} = $this;
                $collection->addItem($e);
            }

            unset($entityClass);
            unset($entity);
            unset($entities);

            return $collection;
        }
        catch(exception $e){
            $logger->error($e->getMessage());
        }
    }
    
    private function getManytoOne($relation){
        $entityClass = $relation->getEntity();
        $entity = new $entityClass();
        unset($entityClass);
        $entity->read($this->{$relation->getAttribute()}, false);
       
        return $entity;
    }
    
    private function getManytoMany($relation){
        throw new Exception('Not Implemented');
    }
    
    protected function getAllMappings(){
        if(is_subclass_of($this, 'Framework\Database\AbstractEntity')){
            $mapping = $this->getFieldsMapping();
            
            if(get_parent_class($this) != 'Framework\Database\AbstractEntity'){
                $mapping->merge(parent::getAllMappings()); 
            }
            
            return $mapping;
        }
        else{
            return new Collection();
        }
    }
    function getDateCreation() {
        return $this->dateCreation;
    }

    function getDateModification() {
        return $this->dateModification;
    }

    function setDateCreation($dateCreation) {
        $this->dateCreation = $dateCreation;
    }

    function setDateModification($dateModification) {
        $this->dateModification = $dateModification;
    }
}
