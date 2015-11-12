<?php

/**
 * The Practice Model
 *
 * @author Hemant Mann
 */
class Practice extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type integer
     * @index
     * 
     * @label Zocdoc id of practice
     */
    protected $_zocdoc_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * 
     * @validate required, alpha, min(3), max(255)
     * @label Name of the practice center (i.e Hospital etc)
     */
    protected $_name;
}