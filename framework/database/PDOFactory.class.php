<?php
namespace Framework\Database;

use \PDO;
use Framework\AbstractClass\AbstractClass;
use Framework\Logger\Logger;

class PDOFactory{

   private static $readInstance = null;
   private static $writeInstance = null;
   
   const READ = 'read';
   const WRITE = 'write';
    /**
     * Constructeur de la classe
     *
     * @param void
     * @return void
     */
    private function __construct($mode = PDOFactory::READ) {
        parent::__construct();
    }
 
    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @param void
     * @return Singleton
     */
    public static function getInstance($mode = PDOFactory::READ) {
        try{
            if($mode == PDOFactory::READ){
                if(is_null(self::$readInstance)) {
                    self::$readInstance = new PDO('mysql:dbname=' . DATABASE . ';host=' . HOST, READ_USER, READ_PASSWORD);
                }

                return self::$readInstance;
            }
            else{
                if(is_null(self::$writeInstance)) {
                    self::$writeInstance = new PDO('mysql:dbname=' . DATABASE . ';host=' . HOST, WRITE_USER, WRITE_PASSWORD);  
                }

                 return self::$writeInstance;
            }
        }
        catch (\Exception $e){
            $logger = new Logger();
            $logger->error($e->getMessage());
        }
    }
}
