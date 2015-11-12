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
    protected $_street;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_area;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_city;

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