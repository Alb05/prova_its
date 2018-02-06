<?php
include('conn.php');
header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

echo json_encode(isset($_SESSION['utente']) && $_SESSION['utente']['GROUP_ID'] == 1);