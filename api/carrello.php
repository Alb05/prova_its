<?php
include('conn.php');
if (isset($_SESSION['utente'])) {
  try {
    header('Content-Type: application/json;charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $posted = json_decode(file_get_contents("php://input"));
      $bookid = $posted->bookid;
      $bookqty = $posted->bookqty;

      if (isset($bookid) && isset($bookqty)) {
        $inserted = false;
        for ($i = 0; $i < count($_SESSION['carrello']); $i++) {
          if ($_SESSION['carrello'][$i]['BOOK_ID'] == $bookid) {
            $_SESSION['carrello'][$i]['QUANTITY'] += $bookqty;
            $inserted = true;
          }
        }
        if (!$inserted) {
          $_SESSION['carrello'][] = array('BOOK_ID' => $bookid, 'QUANTITY' => $bookqty);
        }
      }
    } else {
      echo json_encode($_SESSION['carrello']);
    }
  }
  finally {
    oci_close($conn);
  }
} else {
  echo '<p>non sei loggato</p>';
  header('refresh:3;index.php');
}