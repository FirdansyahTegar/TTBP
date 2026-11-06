<?php
/**
 * Local Print Bridge untuk Printer Dot Matrix Epson LX-310 ESC/P
 * Memungkinkan aplikasi yang di-host di Cloud (https://ttbp.karismaerp.com)
 * tetap dapat mencetak langsung ke printer lokal/LAN (\\10.10.10.29\EPSON LX-310 ESC/P)
 * melalui XAMPP lokal (http://localhost/TTBP/lx310_bridge.php) dengan lebar penuh 100%.
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Private-Network: true');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__ . '/system/');
}
function &get_instance() {
    static $dummy = null;
    if ($dummy === null) $dummy = new stdClass();
    return $dummy;
}

require_once __DIR__ . '/application/libraries/Ttbp_lx310_direct.php';
$engine = new Ttbp_lx310_direct();

if (!$engine->is_supported()) {
    echo json_encode([
        'status'    => false,
        'supported' => false,
        'message'   => 'Bridge lokal hanya berjalan pada OS Windows dengan dukungan PowerShell.'
    ]);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'status');

if ($action === 'status' || $action === 'printers') {
    $printers = $engine->get_installed_printers();
    $default_lx310 = $engine->find_lx310_printer();
    echo json_encode([
        'status'        => true,
        'supported'     => true,
        'printers'      => $printers,
        'default_lx310' => $default_lx310
    ]);
    exit;
}

if ($action === 'print') {
    $raw_json = isset($_POST['sheets_json']) ? $_POST['sheets_json'] : '';
    $printer  = isset($_POST['printer']) ? $_POST['printer'] : '';

    if (empty($raw_json)) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (is_array($input)) {
            $sheets  = isset($input['sheets']) ? $input['sheets'] : [];
            $printer = isset($input['printer']) ? $input['printer'] : $printer;
        } else {
            $sheets = [];
        }
    } else {
        $sheets = json_decode($raw_json, true);
    }

    if (empty($sheets) || !is_array($sheets)) {
        echo json_encode(['status' => false, 'message' => 'Data lembar TTBP kosong.']);
        exit;
    }

    $res = $engine->print_sheets($sheets, $printer);
    echo json_encode($res);
    exit;
}

echo json_encode(['status' => false, 'message' => 'Action tidak dikenal.']);

