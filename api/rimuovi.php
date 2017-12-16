<?php
include('conn.php');

header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

if (isset($_SESSION['utente'])) {
  $posted = json_decode(file_get_contents("php://input"));
  $bookid = $posted->bookid;

  for ($i = 0; $i < count($_SESSION['carrello']); $i++) {
    if ($_SESSION['carrello'][$i]['BOOK_ID'] == $bookid) {
      unset($_SESSION['carrello'][$i]);
    }
  }
  //header('LOCATION:carrello.php');
  echo json_encode(true);
} else {
  echo json_encode(false);
  //header('refresh:3;index.php');
}