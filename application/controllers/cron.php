<?php

/**
 * Scheduler Class which executes daily and perfoms the initiated job
 * 
 * @author Faizan Ayubi
 */
use Shared\Doc as Doc;

class CRON extends Auth {

    public function __construct($options = array()) {
        parent::__construct($options);
        $this->willRenderLayoutView = false;
        $this->willRenderActionView = false;
    }

    public function index() {
        $this->log("CRON Job Started");
        $docCrawler = new Doc();
        $docCrawler->fetch();
        $this->log("CRON Job Ended");
    }
}
