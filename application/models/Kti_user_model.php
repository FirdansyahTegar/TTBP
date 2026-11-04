<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . 'models/User_model.php');

/**
 * Backward compatibility subclass for User_model
 */
class Kti_user_model extends User_model {
    public function __construct() {
        parent::__construct();
    }
}
