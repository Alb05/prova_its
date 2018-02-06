<?php
include('conn.php');

function retOrd($conn, $orderid, $bookid, $bookqty) {
  $find_query = "SELECT QUANTITY_SOLD FROM ORDER_ITEMS WHERE ORDER_ID = :orderid AND BOOK_ID = :bookid";
  $find_statement = oci_parse($conn, $find_query);
  oci_bind_by_name($find_statement, ':orderid', $orderid);
  oci_bind_by_name($find_statement, ':bookid', $bookid);
  oci_execute($find_statement);
  $row = oci_fetch_assoc($find_statement);
  if ($row) {
    if ($bookqty > 0 && $bookqty <= $row['QUANTITY_SOLD']) {
      $update_query = "UPDATE ORDER_RETURN SET QUANTITY_RETURNED = :bookqty WHERE ORDER_ID = :orderid AND BOOK_ID = :bookid";
      $update_statement = oci_parse($conn, $update_query);
      oci_bind_by_name($update_statement, ':orderid', $orderid);
      oci_bind_by_name($update_statement, ':bookid', $bookid);
      oci_bind_by_name($update_statement, ':bookqty', $bookqty);
      oci_execute($update_statement);
      oci_commit($conn);
      oci_free_statement($update_statement);
      return true;
    } else {
      // valori errati
      return false;
    }
  } else {
    $qty_query = "SELECT QUANTITY_SOLD FROM ORDER_ITEMS WHERE ORDER_ID = :orderid AND BOOK_ID = :bookid";
    $qty_stmt = oci_parse($conn, $qty_query);
    oci_bind_by_name($qty_stmt, ':orderid', $orderid);
    oci_bind_by_name($qty_stmt, ':bookid', $bookid);
    oci_execute($qty_stmt);
    $qty_sold = oci_fetch_assoc($qty_stmt);
    if ($bookqty > 0 && $bookqty <= $qty_sold['QUANTITY_SOLD']) {
      $insert_query = "INSERT INTO ORDER_RETURN (ORDER_ID, BOOK_ID, QUANTITY_RETURNED, STATUS_ID) VALUES (:orderid, :bookid, :bookqty, 1)";
      $insert_statement = oci_parse($conn, $insert_query);
      oci_bind_by_name($insert_statement, ':orderid', $orderid);
      oci_bind_by_name($insert_statement, ':bookid', $bookid);
      oci_bind_by_name($insert_statement, ':bookqty', $bookqty);
      oci_execute($insert_statement);
      oci_commit($conn);
      oci_free_statement($insert_statement);
      return true;
    } else {
      // valori errati
      return false;
    }
  }
  oci_free_statement($find_statement);
}


try {
  // aggiungo gli header per restituire json
  header('Content-Type: application/json;charset=utf-8');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
  header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

  if (isset($_SESSION['utente'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // se la richiesta è di tipo POST leggo l'operazione da effettuare
      $posted = json_decode(file_get_contents("php://input"));
      $method = $posted->method;
      if ($method == 'returnItem') {
        if (isset($posted->orderid) && isset($posted->orderid) && isset($posted->orderid)) {
          $orderid = $posted->orderid;
          $bookid = $posted->bookid;
          $bookqty = $posted->bookqty;
          echo json_encode(retOrd($conn, $orderid, $bookid, $bookqty));
        } else {
          // parametri non settati
          echo json_encode(false);
        }
      } elseif ($method == 'removeOrder') {
        if (isset($posted->orderid)) {
          $orderid = $posted->orderid;
          $items = array();
          $find_query = "SELECT ORDER_ID, BOOK_ID, QUANTITY_SOLD, PRICE FROM ORDER_ITEMS WHERE ORDER_ID = :orderid";
          $find_statement = oci_parse($conn, $find_query);
          oci_bind_by_name($find_statement, ':orderid', $orderid);
          oci_execute($find_statement);
          while ($row = oci_fetch_assoc($find_statement)) {
            $items[] = $row;
          }
          oci_free_statement($find_statement);
          $result = true;
          foreach ($items as $item) {
            $result &= retOrd($conn, $item['ORDER_ID'], $item['BOOK_ID'], $item['QUANTITY_SOLD']);
          }
          echo json_encode($result);
        }
      } else {
        // metodo errato
        echo json_encode(false);
      }
    } else {
      // se la richiesta è una GET restituisco gli ordini effettuati
      $data = array();
      $user = $_SESSION['utente'];
      $orders_query = "SELECT ORDER_ID, ORDER_DATE FROM ORDERS WHERE USER_ID = :userid ORDER BY ORDER_DATE DESC";
      $orders_statement = oci_parse($conn, $orders_query);
      oci_bind_by_name($orders_statement, ':userid', $user['USER_ID']);
      oci_execute($orders_statement);
      while ($row = oci_fetch_assoc($orders_statement)) {
        $row['ITEMS'] = array();
        $items_query = "SELECT ord.BOOK_ID, ord.TITLE, ord.PRICE, ord.QUANTITY_SOLD, NVL(ret.QUANTITY_RETURNED, 0) QUANTITY_RETURNED, NVL(ret.STATUS_ID, 0) STATUS_ID, NVL(stat.STATUS_TYPE, 'Normale') STATUS_TYPE FROM (SELECT o.ORDER_ID, o.ORDER_DATE, i.BOOK_ID, b.TITLE, i.PRICE, i.QUANTITY_SOLD FROM ORDERS o, ORDER_ITEMS i, BOOKS b WHERE o.ORDER_ID = i.ORDER_ID AND b.BOOK_ID = i.BOOK_ID AND :userid = o.USER_ID AND o.ORDER_ID = :orderid) ord LEFT OUTER JOIN ORDER_RETURN ret ON ord.ORDER_ID = ret.ORDER_ID AND ord.BOOK_ID = ret.BOOK_ID LEFT OUTER JOIN ORDERS_STATUS stat ON ret.STATUS_ID = stat.STATUS_ID ORDER BY ord.ORDER_DATE DESC";
        $items_statement = oci_parse($conn, $items_query);
        oci_bind_by_name($items_statement, ':userid', $user['USER_ID']);
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
  }
}
finally {
  // chiudo le connessioni
  oci_close($conn);
}