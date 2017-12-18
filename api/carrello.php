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
      $method = $posted->method;

      if (isset($method)) {
        if ($method == 'add') {
          if (isset($bookid) && isset($bookqty)) {
            $quantity_query = 'SELECT QUANTITY FROM WAREHOUSE WHERE BOOK_ID = :bookid';
            $quantity_stmt = oci_parse($conn, $quantity_query);
            oci_bind_by_name($quantity_stmt, ':bookid', $bookid);
            oci_execute($quantity_stmt);
            $row = oci_fetch_assoc($quantity_stmt);
            $inserted = false;
            $found = false;
            for ($i = 0; $i < count($_SESSION['carrello']); $i++) {
              if ($_SESSION['carrello'][$i]['BOOK_ID'] == $bookid) {
                $found = true;
                if ($row['QUANTITY'] >= ($_SESSION['carrello'][$i]['QUANTITY'] + $bookqty) && $bookqty > 0) {
                  $_SESSION['carrello'][$i]['QUANTITY'] += $bookqty;
                  $inserted = true;
                }
              }
            }
            if (!$inserted && !$found) {
              if ($row['QUANTITY'] >= $bookqty && $bookqty > 0) {
                $_SESSION['carrello'][] = array('BOOK_ID' => $bookid, 'QUANTITY' => $bookqty);
                echo json_encode(true);
              } else {
                echo json_encode(false);
              }
            } else {
              echo json_encode(false);
            }
          } else {
            echo json_encode(false);
          }
        } elseif ($method == 'modify') {
          if (isset($bookid) && isset($bookqty)) {
            $quantity_query = 'SELECT QUANTITY FROM WAREHOUSE WHERE BOOK_ID = :bookid';
            $quantity_stmt = oci_parse($conn, $quantity_query);
            oci_bind_by_name($quantity_stmt, ':bookid', $bookid);
            oci_execute($quantity_stmt);
            $row = oci_fetch_assoc($quantity_stmt);
            $inserted = false;
            for ($i = 0; $i < count($_SESSION['carrello']); $i++) {
              if ($_SESSION['carrello'][$i]['BOOK_ID'] == $bookid && $row['QUANTITY'] >= $bookqty) {
                $_SESSION['carrello'][$i]['QUANTITY'] = $bookqty;
                $inserted = true;
              }
            }
            if (!$inserted) {
              echo json_encode(false);
            } else {
              echo json_encode(true);
            }
          } else {
            echo json_encode(false);
          }
        } elseif ($method == 'remove') {
          for ($i = 0; $i < count($_SESSION['carrello']); $i++) {
            if ($_SESSION['carrello'][$i]['BOOK_ID'] == $bookid) {
              unset($_SESSION['carrello'][$i]);
              $_SESSION['carrello'] = array_values($_SESSION['carrello']);
            }
          }
          echo json_encode(true);
        } else {
          echo json_encode(false);
        }
      }
    } else {
      $data = array();
      if (count($_SESSION['carrello']) > 0) {
        foreach ($_SESSION['carrello'] as $libro) {
          $query = 'SELECT b.BOOK_ID, b.TITLE, b.DESCRIPTION, b.PRICE, w.QUANTITY FROM BOOKS b, WAREHOUSE w WHERE b.BOOK_ID = w.BOOK_ID AND b.BOOK_ID = :bookid ORDER BY b.TITLE';
          $statement = oci_parse($conn, $query);
          oci_bind_by_name($statement, ':bookid', $libro['BOOK_ID']);
          oci_execute($statement);
          while ($row = oci_fetch_assoc($statement)) {
            $row["ORD_QTY"] = $libro['QUANTITY'];
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
}