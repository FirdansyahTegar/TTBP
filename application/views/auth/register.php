<?php
// Wrapper for kti_auth/register
$this->load->view('kti_auth/register', isset($data) ? $data : (isset($view_data) ? $view_data : get_defined_vars()));

