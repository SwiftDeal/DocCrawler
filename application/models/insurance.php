<?php

/**
 * The Insurance Model
 *
 * @author Hemant Mann
 */
class Insurance extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * @index
     * 
     * @validate required, alpha, min(3), max(255)
     * @label Name of the insurance firm
     */
    protected $_name;
}
