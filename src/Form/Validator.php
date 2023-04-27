<?php

namespace App\Form;

class Validator
{
    const NOT_EMPTY = 'notEmpty';

    const STRING = 'string';
    const NUMBER = 'number';

    public static function validate($dataObject, $field, $type = self::STRING, $condition = self::NOT_EMPTY){
        self::validateExists($dataObject, $field);

        if($type == self::NUMBER){
            self::validateIsNumber($dataObject, $field);
        }
        if($type == self::STRING){
            self::validateIsString($dataObject, $field);
        }


        if($condition == self::NOT_EMPTY ) {
            self::validateNotNull($dataObject, $field);
            if($type == self::STRING){
                self::validateNotEmptyString($dataObject, $field);
            }
        }
    }

    private static function validateExists($dataObject, $field){
        if(!property_exists($dataObject, $field)){
            throw new \Exception("Favor preencher $field");
        }
    }

    private static function validateNotNull($dataObject, $field) {
        if($dataObject->$field == null) {
            throw new \Exception("Favor preencher $field");
        }
    }

    private static function validateNotEmptyString($dataObject, $field) {
        if($dataObject->$field == '') {
            throw new \Exception("Favor preencher $field");
        }
    }

    private static function validateIsNumber($dataObject, $field){
        if(!is_numeric($dataObject->$field)){
            throw new \Exception("Campo $field não é um número válido");
        }
    }
    private static function validateIsString($dataObject, $field){
        if(!is_string($dataObject->$field)){
            throw new \Exception("Campo $field não é um texto válido");
        }
    }
}
