<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/15/13
 * Time: 10:40 AM
 */

namespace ClassCentral\SiteBundle\Swiftype;

/**
 * Field  for swiftytpe api
 * https://swiftype.com/documentation/overview#field_types
 * Class SwiftypeField
 * @package ClassCentral\SiteBundle\Swiftype
 */
class SwiftypeField {

    public $name;

    public $value;

    public $type;

    const FIELD_STRING = 'string';
    const FIELD_TEXT = 'text';
    const FIELD_ENUM = 'enum';
    const FIELD_INTEGER = 'integer';
    const FIELD_FLOAT = 'float';
    const FIELD_DATE = 'date';
    const FIELD_LOCATION = 'location';

    public static $types = array(
        self::FIELD_STRING, self::FIELD_TEXT, self::FIELD_ENUM, self::FIELD_INTEGER,
        self::FIELD_FLOAT, self::FIELD_DATE, self::FIELD_LOCATION
    );

    public static function get($name, $value, $type)
    {
        if(!in_array($type,self::$types))
        {
            throw new \Exception("Field of $type is not valid Swiftype field");
        }

        $field = new SwiftypeField();
        $field->name = $name;
        $field->value = $value;
        $field->type = $type;

        return $field;
    }
} 