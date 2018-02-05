<?php
include('conn.php');
try {
  // aggiungo gli header per restituire json
  header('Content-Type: application/json;charset=utf-8');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
  header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

  if (isset($_SESSION['utente'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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