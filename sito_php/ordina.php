<?php
include('conn.php');
if (isset($_SESSION['utente'])) {
  $user = $_SESSION['utente'][0];
  try {
    if (count($_SESSION['carrello']) > 0) {
      $insert_query = 'INSERT INTO ORDERS (USER_ID, ORDER_DATE) VALUES (:userid, CURRENT_DATE)';
      $insert_stmt = oci_parse($conn, $insert_query);
      oci_bind_by_name($insert_stmt, ':userid', $user['USER_ID']);
      oci_execute($insert_stmt);
      oci_free_statement($insert_stmt);

      $order_query = 'SELECT ORDER_ID FROM ORDERS WHERE USER_ID = :userid AND TO_DATE(ORDER_DATE) = TO_DATE(CURRENT_DATE) ORDER BY ORDER_DATE DESC';
      $order_stmt = oci_parse($conn, $order_query);
      oci_bind_by_name($order_stmt, ':userid', $user['USER_ID']);
      if (oci_execute($order_stmt)) {
        $order = oci_fetch_assoc($order_stmt);
        oci_free_statement($order_stmt);

        foreach ($_SESSION['carrello'] as $book) {
          $quantity_query = 'SELECT QUANTITY FROM WAREHOUSE WHERE BOOK_ID = :bookid';
          $quantity_stmt = oci_parse($conn, $quantity_query);
          oci_bind_by_name($quantity_stmt, ':bookid', $book['BOOK_ID']);
          if (oci_execute($quantity_stmt)) {
            $row = oci_fetch_assoc($quantity_stmt);
            if ($row['QUANTITY'] >= $book['QUANTITY'] && $book['QUANTITY'] > 0) {
              $modify_query = 'UPDATE WAREHOUSE SET QUANTITY=:bookqty WHERE BOOK_ID = :bookid';
              $modify_stmt = oci_parse($conn, $modify_query);
              $quantity = intval($row['QUANTITY']) - intval($book['QUANTITY']);
              oci_bind_by_name($modify_stmt, ':bookqty', $quantity);
              oci_bind_by_name($modify_stmt, ':bookid', $book['BOOK_ID']);
              oci_execute($modify_stmt);
              oci_free_statement($modify_stmt);

              $insert2_query = 'INSERT INTO ORDER_ITEMS (ORDER_ID, BOOK_ID, QUANTITY_SOLD) VALUES (:ordid, :bookid, :bookqty)';
              $insert2_stmt = oci_parse($conn, $insert2_query);
              oci_bind_by_name($insert2_stmt, ':ordid', $order['ORDER_ID']);
              oci_bind_by_name($insert2_stmt, ':bookid', $book['BOOK_ID']);
              oci_bind_by_name($insert2_stmt, ':bookqty', $book['QUANTITY']);
              oci_execute($insert2_stmt);
              oci_free_statement($insert2_stmt);
            }
            else {
              oci_rollback($conn);
              echo "<p>non è possibile effettuare l'ordine</p>";
              $_SESSION['carrello'] = array();
              header('refresh:3;elenco.php');
              exit();
            }
          }
        }
        oci_commit($conn);
        echo "<p>ordine eseguito</p>";
        $_SESSION['carrello'] = array();
        header('refresh:3;elenco.php');
      }
    } else {
      echo '<p>il carrello è vuoto</p>';
      header('refresh:3;elenco.php');
    }
  }
  finally {
    oci_close($conn);
    exit();
  }
} else {
  echo '<p>non sei loggato</p>';
  header('refresh:3;index.php');
}