<?php
include('conn.php');

header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

if (isset($_SESSION['utente'])) {
  try {
    // controllo il tipo di richiesta
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // se la richiesta è di tipo POST leggo l'operazione da effettuare
      $posted = json_decode(file_get_contents("php://input"));
      $method = $posted->method;
      if (isset($method)) {
        // se la richiesta è per aggiungere un elemento
        if ($method == 'add') {
          $bookid = $posted->bookid;
          $bookqty = $posted->bookqty;
          if (isset($bookid) && isset($bookqty)) {
            // mi faccio dare dal db la quantità del libro selezionato
            $quantity_query = 'SELECT QUANTITY FROM WAREHOUSE WHERE BOOK_ID = :bookid';
            $quantity_stmt = oci_parse($conn, $quantity_query);
            oci_bind_by_name($quantity_stmt, ':bookid', $bookid);
            oci_execute($quantity_stmt);
            $row = oci_fetch_assoc($quantity_stmt);
            $inserted = false;
            $found = false;
            // cerco il libro nel carrello
            for ($i = 0; $i < count($_SESSION['carrello']); $i++) {
              if ($_SESSION['carrello'][$i]['BOOK_ID'] == $bookid) {
                $found = true;
                // se lo trovo lo aggiungo al carrello solo se la quantità richiesta è disponibile
                if ($row['QUANTITY'] >= ($_SESSION['carrello'][$i]['QUANTITY'] + $bookqty) && $bookqty > 0) {
                  $_SESSION['carrello'][$i]['QUANTITY'] += $bookqty;
                  $inserted = true;
                }
              }
            }
            // se il libro non è stato trovato lo aggiungo al carrello se la quantità richiesta è disponibile
            if (!$found) {
              if ($row['QUANTITY'] >= $bookqty && $bookqty > 0) {
                $_SESSION['carrello'][] = array('BOOK_ID' => $bookid, 'QUANTITY' => $bookqty);
                echo json_encode(true);
              } else {
                echo json_encode(false);
              }
            // se il libro è stato trovato ma non inserito
            } elseif ($found && !$inserted) {
              echo json_encode(false);
            // se il libro è stato trovato e inserito
            } else {
              echo json_encode(true);
            }
            oci_free_statement($quantity_stmt);
          } else {
            echo json_encode(false);
          }
        } elseif ($method == 'modify') {
          // se la richieasta è per la modifica di un elemento nel carrello
          $bookid = $posted->bookid;
          $bookqty = $posted->bookqty;
          if (isset($bookid) && isset($bookqty)) {
            // mi faccio dare dal db la quantità dl libro selezionato
            $quantity_query = 'SELECT QUANTITY FROM WAREHOUSE WHERE BOOK_ID = :bookid';
            $quantity_stmt = oci_parse($conn, $quantity_query);
            oci_bind_by_name($quantity_stmt, ':bookid', $bookid);
            oci_execute($quantity_stmt);
            $row = oci_fetch_assoc($quantity_stmt);
            $inserted = false;
            for ($i = 0; $i < count($_SESSION['carrello']); $i++) {
              // cerco l'elemento e se la quantità richiesta è disponibile lo modifico
              if ($_SESSION['carrello'][$i]['BOOK_ID'] == $bookid && $row['QUANTITY'] >= $bookqty && $bookqty > 0) {
                $_SESSION['carrello'][$i]['QUANTITY'] = $bookqty;
                $inserted = true;
              }
            }
            if (!$inserted) {
              echo json_encode(false);
            } else {
              echo json_encode(true);
            }
            oci_free_statement($quantity_stmt);
          } else {
            echo json_encode(false);
          }
        } elseif ($method == 'remove') {
          // per rimuovere l'elemento
          $bookid = $posted->bookid;
          // cero l'elemento nel carrello
          for ($i = 0; $i < count($_SESSION['carrello']); $i++) {
            // se lo trovo lo rimuovo dal carrello
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
      // se la richiesta è una GET restituisco il contenuto del carrello
      $data = array();
      if (count($_SESSION['carrello']) > 0) {
        foreach ($_SESSION['carrello'] as $libro) {
          $query = 'SELECT b.BOOK_ID, b.PHOTO, b.TITLE, b.DESCRIPTION, b.PRICE, w.QUANTITY FROM BOOKS b, WAREHOUSE w WHERE b.BOOK_ID = w.BOOK_ID AND b.BOOK_ID = :bookid ORDER BY b.TITLE';
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