<?php

/**
 * The Doctor Model
 *
 * @author Hemant Mann
 */
class Doctor extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required, alpha, min(3), max(100)
     * @label name of the doctor
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 60
     * 
     * @validate required, alpha, min(3), max(32)
     * @label Suffix (values: "MD", "LAc" etc)
     */
    protected $_suffix;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     * 
     * @label Speciality id of doctor
     */
    protected $_speciality_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 3
     * 
     * @validate required 
     * @label Gender (He|She)
     */
    protected $_gender;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 64
     * 
     * @validate required 
     * @label ZocDoc id
     */
    protected $_zocdoc_id;
}