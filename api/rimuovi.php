<?php
include('conn.php');

header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

if (isset($_SESSION['utente'])) {
  $posted = json_decode(file_get_contents("php://input"));
  $bookid = $posted->bookid;
  $bookqty = $posted->bookqty;

  for ($i = 0; $i < count($_SESSION['carrello']); $i++) {
    if ($_SESSION['carrello'][$i]['BOOK_ID'] == $bookid) {
      if ($_SESSION['carrello'][$i]['QUANTITY'] <= $bookqty) {
        unset($_SESSION['carrello'][$i]);
      } else {
        $_SESSION['carrello'][$i]['QUANTITY'] -= $bookqty;
      }
    }
  }
  //header('LOCATION:carrello.php');
  echo json_encode(true);
} else {
  echo json_encode(false);
  //header('refresh:3;index.php');
}