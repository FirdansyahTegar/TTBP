<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . 'controllers/Dashboard.php');

/**
 * Backward compatibility subclass for Dashboard controller
 */
class Kti_dashboard extends Dashboard {
    public function __construct() {
        parent::__construct();
    }
}

