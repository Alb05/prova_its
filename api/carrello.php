<?php
include('conn.php');

header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

if (isset($_SESSION['utente'])) {
  try {
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
        echo json_encode(true);
      }
    } else {
      $data = array();
      if (count($_SESSION['carrello']) > 0) {
        foreach ($_SESSION['carrello'] as $libro) {
          $query = 'SELECT BOOK_ID, TITLE, DESCRIPTION, PRICE FROM BOOKS WHERE BOOK_ID = :bookid ORDER BY TITLE';
          $statement = oci_parse($conn, $query);
          oci_bind_by_name($statement, ':bookid', $libro['BOOK_ID']);
          oci_execute($statement);
          while ($row = oci_fetch_assoc($statement)) {
            $row["QUANTITY"] = $libro['QUANTITY'];
            $data[] = $row;
          }
          oci_free_statement($statement);
        }
        echo json_encode($data);
      }
    }
  }
  finally {
    oci_close($conn);
  }
} else {
  echo json_encode(false);
  //header('refresh:3;index.php');
}