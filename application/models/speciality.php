<?php

/**
 * The Speciality Model
 *
 * @author Hemant Mann
 */
class Speciality extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required, alpha, min(3), max(100)
     * @label Name of the speciality doctor has
     */
	protected $_name;
}