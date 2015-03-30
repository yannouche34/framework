<?php
/**
 * Created by PhpStorm.
 * User: Yannouche
 * Date: 30/03/2015
 * Time: 11:26
 */
use \Transliterator;

use Framework\AbstractClass\AbstractClass;

class Chaine extends AbstractClass{
    static public function systemize($str){
        $transliterator = Transliterator::createFromRules(
            "::Latin-ASCII; ::Lower; [^[:L:][:N:]]+ > '-';"
        );

        return trim($transliterator->transliterate($str),'-' );
    }
}