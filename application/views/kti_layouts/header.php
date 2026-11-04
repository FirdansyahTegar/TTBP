<?php
// Backward compatibility wrapper for layouts/header
$this->load->view('layouts/header', isset($data) ? $data : (isset($view_data) ? $view_data : get_defined_vars()));

