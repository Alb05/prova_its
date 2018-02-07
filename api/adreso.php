<?php
include('conn.php');

try {
  // aggiungo gli header per restituire json
  header('Content-Type: application/json;charset=utf-8');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
  header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

  if (isset($_SESSION['utente']) && $_SESSION['utente']['GROUP_ID'] == 1) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // se la richiesta è di tipo POST leggo l'operazione da effettuare
      $posted = json_decode(file_get_contents("php://input"));
      $method = $posted->method;
      if (isset($posted->orderid) && isset($posted->bookid)) {
        $orderid = $posted->orderid;
        $bookid = $posted->bookid;
        if ($method == 'recieved') {
          $recieved_query = "UPDATE ORDER_RETURN SET STATUS_ID = 2 WHERE ORDER_ID = :orderid AND BOOK_ID = :bookid AND STATUS_ID = 1";
          $recieved_stmt = oci_parse($conn, $recieved_query);
          oci_bind_by_name($recieved_stmt, ':orderid', $orderid);
          oci_bind_by_name($recieved_stmt, ':bookid', $bookid);
          oci_execute($recieved_stmt);
          oci_commit($conn);
          oci_free_statement($recieved_stmt);
          echo json_encode(true);
        } elseif ($method == 'completed') {
          $insert_query = "UPDATE WAREHOUSE SET QUANTITY = (SELECT (QUANTITY + (SELECT QUANTITY_RETURNED FROM ORDER_RETURN WHERE ORDER_ID = :orderid AND BOOK_ID = :bookid)) QUANTITY FROM WAREHOUSE WHERE BOOK_ID = :bookid) WHERE BOOK_ID = :bookid AND (SELECT STATUS_ID FROM ORDER_RETURN WHERE ORDER_ID = :orderid AND BOOK_ID = :bookid) = 2";
          $insert_stmt = oci_parse($conn, $insert_query);
          oci_bind_by_name($insert_stmt, ':orderid', $orderid);
          oci_bind_by_name($insert_stmt, ':bookid', $bookid);
          oci_execute($insert_stmt);
          oci_commit($conn);
          oci_free_statement($insert_stmt);
          $completed_query = "UPDATE ORDER_RETURN SET STATUS_ID = 3 WHERE ORDER_ID = :orderid AND BOOK_ID = :bookid AND STATUS_ID = 2";
          $completed_stmt = oci_parse($conn, $completed_query);
          oci_bind_by_name($completed_stmt, ':orderid', $orderid);
          oci_bind_by_name($completed_stmt, ':bookid', $bookid);
          oci_execute($completed_stmt);
          oci_commit($conn);
          oci_free_statement($completed_stmt);
          echo json_encode(true);
        } else {
          // metodo sbagliato
          echo json_encode(false);
        }
      } else {
          // mancano i parametri
          echo json_encode(false);
        }
    } else {
      // se la richiesta è una GET restituisco gli ordini effettuati
      $data = array();
      $orders_query = "SELECT DISTINCT r.ORDER_ID, o.ORDER_DATE, u.USER_ID, u.LAST_NAME || ' ' || u.FIRST_NAME NAME, u.MAIL FROM ORDERS o, ORDER_RETURN r, USERS u WHERE o.ORDER_ID = r.ORDER_ID AND o.USER_ID = u.USER_ID ORDER BY ORDER_DATE";
      $orders_statement = oci_parse($conn, $orders_query);
      oci_execute($orders_statement);
      while ($row = oci_fetch_assoc($orders_statement)) {
        $row['ITEMS'] = array();
        $items_query = "SELECT r.BOOK_ID, b.TITLE, b.PRICE, i.QUANTITY_SOLD, r.QUANTITY_RETURNED, r.STATUS_ID, s.STATUS_TYPE FROM ORDER_RETURN r, ORDER_ITEMS i, ORDERS o, BOOKS b, ORDERS_STATUS s WHERE r.ORDER_ID = i.ORDER_ID AND r.BOOK_ID = i.BOOK_ID AND r.BOOK_ID = b.BOOK_ID AND r.STATUS_ID = s.STATUS_ID AND r.ORDER_ID = o.ORDER_ID AND r.ORDER_ID = :orderid";
        $items_statement = oci_parse($conn, $items_query);
        //oci_bind_by_name($items_statement, ':userid', $row['USER_ID']);
        oci_bind_by_name($items_statement, ':orderid', $row['ORDER_ID']);
        oci_execute($items_statement);
        while ($item = oci_fetch_assoc($items_statement)) {
          $row['ITEMS'][] = $item;
        }
        oci_free_statement($items_statement);
        $data[] = $row;
      }
      oci_free_statement($orders_statement);
      echo json_encode($data);
    }
  } else {
    // utente sbagliato
    echo json_encode(false);
  }
}
finally {
  // chiudo le connessioni
  oci_close($conn);
}
