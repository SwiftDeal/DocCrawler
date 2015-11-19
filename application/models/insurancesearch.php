<?php

/**
 * The Insurance Search Model
 *
 * @author Hemant Mann
 */
class InsuranceSearch extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_ins_plan_id;

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
     * @type integer
     * @index
     *
     * @label Zip code (it is of 5 digits eg: 10007)
     */
    protected $_zip_code;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * @index
     */
    protected $_city;
}
