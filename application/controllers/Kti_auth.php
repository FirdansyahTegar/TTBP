<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . 'controllers/Auth.php');

/**
 * Backward compatibility subclass for Auth controller
 */
class Kti_auth extends Auth {
    public function __construct() {
        parent::__construct();
    }
}
