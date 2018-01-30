<?php
include('conn.php');

header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

if (isset($_SESSION['utente'])) {
  $user = $_SESSION['utente'];
  try {
    // se ci sono elementi nel carrello creo l'ordine
    if (count($_SESSION['carrello']) > 0) {
      $insert_query = 'INSERT INTO ORDERS (USER_ID, ORDER_DATE, ORDER_STATUS) VALUES (:userid, CURRENT_DATE, 1)';
      $insert_stmt = oci_parse($conn, $insert_query);
      oci_bind_by_name($insert_stmt, ':userid', $user['USER_ID']);
      oci_execute($insert_stmt);
      oci_free_statement($insert_stmt);

      // mi faccio dare l'id dell'ordine appena creato
      $order_query = 'SELECT ORDER_ID FROM ORDERS WHERE USER_ID = :userid AND TO_DATE(ORDER_DATE) = TO_DATE(CURRENT_DATE) ORDER BY ORDER_DATE DESC';
      $order_stmt = oci_parse($conn, $order_query);
      oci_bind_by_name($order_stmt, ':userid', $user['USER_ID']);
      if (oci_execute($order_stmt)) {
        $order = oci_fetch_assoc($order_stmt);
        oci_free_statement($order_stmt);
        // per ogni elemento nel carrello mi faccio dare la quantità a magazzino
        foreach ($_SESSION['carrello'] as $book) {
          $quantity_query = 'SELECT QUANTITY FROM WAREHOUSE WHERE BOOK_ID = :bookid';
          $quantity_stmt = oci_parse($conn, $quantity_query);
          oci_bind_by_name($quantity_stmt, ':bookid', $book['BOOK_ID']);
          if (oci_execute($quantity_stmt)) {
            $row = oci_fetch_assoc($quantity_stmt);
            // se la quantità richiesta è disponibile tolgo dal magazzino la quantità richiesta
            if ($row['QUANTITY'] >= $book['QUANTITY'] && $book['QUANTITY'] > 0) {
              $modify_query = 'UPDATE WAREHOUSE SET QUANTITY=:bookqty WHERE BOOK_ID = :bookid';
              $modify_stmt = oci_parse($conn, $modify_query);
              $quantity = intval($row['QUANTITY']) - intval($book['QUANTITY']);
              oci_bind_by_name($modify_stmt, ':bookqty', $quantity);
              oci_bind_by_name($modify_stmt, ':bookid', $book['BOOK_ID']);
              oci_execute($modify_stmt);
              oci_free_statement($modify_stmt);

              // inserisco il libro nella lista dell'ordine
              $insert2_query = 'INSERT INTO ORDER_ITEMS (ORDER_ID, BOOK_ID, QUANTITY_SOLD, PRICE) VALUES (:ordid, :bookid, :bookqty, (SELECT PRICE FROM BOOKS WHERE BOOK_ID = :bookid))';
              $insert2_stmt = oci_parse($conn, $insert2_query);
              oci_bind_by_name($insert2_stmt, ':ordid', $order['ORDER_ID']);
              oci_bind_by_name($insert2_stmt, ':bookid', $book['BOOK_ID']);
              oci_bind_by_name($insert2_stmt, ':bookqty', $book['QUANTITY']);
              oci_execute($insert2_stmt);
              oci_free_statement($insert2_stmt);
            }
            else {
              // se l'ordine è sbagliato faccio la rollback e termino l'operazione
              oci_rollback($conn);
              echo json_encode(false);
              $_SESSION['carrello'] = array();
              exit();
            }
          }
        }
        // se l'ordine è riuscito faccio la commit e svuoto il carrello
        oci_commit($conn);
        echo json_encode(true);
        $_SESSION['carrello'] = array();
      }
    } else {
      echo json_encode(false);
    }
  }
  finally {
    oci_close($conn);
    exit();
  }
}