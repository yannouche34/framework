<?php
namespace Framework\AbstractClass;

use Framework\AbstractClass\AbstractClass;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
abstract class AbstractController extends AbstractClass{
    protected $manager;
    protected $page;
    
    public function __construct(){
        parent::__construct();
    }
    
    public function getManager() {
        return $this->manager;
    }

    public function setManager($manager) {
        $this->manager = $manager;
    }

    public function getPage() {
        return $this->page;
    }

    public function setPage($page) {
        $this->page = $page;
    }


}
