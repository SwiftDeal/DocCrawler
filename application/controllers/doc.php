<?php

/**
 * Description of doc
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Doc extends Admin {

    /**
     * @before _secure, _admin, changeLayout
     */
    public function create() {
        $this->seo(array("title" => "Create Doctor", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        if (RequestMethods::post("action") == "createDoc") {
        	$doctor = new Doctor(array(
        		"name" => RequestMethods::post("name"),
        		"suffix" => RequestMethods::post("suffix"),
        		"speciality_id" => RequestMethods::post("speciality_id"),
        		"gender" => RequestMethods::post("gender"),
        		"zocdoc_id" => RequestMethods::post("zocdoc_id", ""),
        		"practice_id" => RequestMethods::post("practice_id", "")
        	));
        	$doctor->save();
        }
    }

    /**
     * @before _secure, _admin, changeLayout
     */
    public function all() {
    	$this->seo(array("title" => "All Doctor", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        $limit = RequestMethods::get("limit", 10);
        $page = RequestMethods::get("page", 1);

        $doctors = Doctor::all(array(), array("name", "suffix"), "created", "desc", $limit, $page);
        $count = Doctor::count();

        $view->set("count", $count);
        $view->set("doctors", $doctors);
        $view->set("page", $page);
        $view->set("limit", $limit);
    }

}
