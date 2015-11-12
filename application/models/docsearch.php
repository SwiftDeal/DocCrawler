<?php

/**
 * The Location Model
 *
 * @author Hemant Mann
 */
class DocSearch extends Shared\Model {
    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_doctor_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_speciality_id;    

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_address;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * @index
     */
    protected $_city;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 2
     * @index
     *
     * @label State Code (it is of two characters, eg: NY|CA)
     */
    protected $_state_code;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     *
     * @label Zip code (it is of 5 digits eg: 10007)
     */
    protected $_zip_code;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @length 18, 15
     */
    protected $_latitude;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @length 18, 15
     */
    protected $_longitude;
}