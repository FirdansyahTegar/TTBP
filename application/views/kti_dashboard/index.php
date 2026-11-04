<?php
// Backward compatibility wrapper for dashboard/index
$this->load->view('dashboard/index', isset($data) ? $data : (isset($view_data) ? $view_data : get_defined_vars()));

