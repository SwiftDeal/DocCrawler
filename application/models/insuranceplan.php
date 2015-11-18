<?php

/**
 * The Insurance Search Model
 *
 * @author Hemant Mann
 */
class InsurancePlan extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_insurance_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * @index
     */
    protected $_name;

}
