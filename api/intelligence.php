<?php
include('conn.php');

try{

  header('Content-Type: application/json;charset=utf-8');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
  header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

  if (isset($_SESSION['utente']) && $_SESSION['utente']['GROUP_ID'] == 1) {
    $data = ['BOOKS' => array(), 'CATEGORIES' => array()];
    $books_query = "SELECT i.BOOK_ID, b.TITLE, b.AUTHOR, (SUM(i.QUANTITY_SOLD) - SUM(NVL(r.QUANTITY_RETURNED, 0))) QUANTITY_SOLD FROM ORDER_ITEMS i JOIN BOOKS b ON i.BOOK_ID = b.BOOK_ID LEFT OUTER JOIN ORDER_RETURN r ON i.ORDER_ID = r.ORDER_ID AND i.BOOK_ID = r.BOOK_ID GROUP BY i.BOOK_ID, b.TITLE, b.AUTHOR ORDER BY QUANTITY_SOLD DESC FETCH FIRST 3 ROWS ONLY";
    $books_stmt = oci_parse($conn, $books_query);
    oci_execute($books_stmt);
    while ($row = oci_fetch_assoc($books_stmt)) {
      $data['BOOKS'][] = $row;
    }
    oci_free_statement($books_stmt);

    $categories_query = "SELECT c.CATEGORY_ID, c.CATEGORY_NAME, (SUM(i.QUANTITY_SOLD) - SUM(NVL(r.QUANTITY_RETURNED, 0))) QUANTITY_SOLD FROM CATEGORIES c JOIN BOOKS b ON c.CATEGORY_ID = b.CATEGORY_ID JOIN ORDER_ITEMS i ON b.BOOK_ID = i.BOOK_ID LEFT OUTER JOIN ORDER_RETURN r ON i.ORDER_ID = r.ORDER_ID AND i.BOOK_ID = r.BOOK_ID GROUP BY c.CATEGORY_ID, c.CATEGORY_NAME ORDER BY QUANTITY_SOLD DESC FETCH FIRST 3 ROWS ONLY";
    $categories_stmt = oci_parse($conn, $categories_query);
    oci_execute($categories_stmt);
    while ($row = oci_fetch_assoc($categories_stmt)) {
      $data['CATEGORIES'][] = $row;
    }
    oci_free_statement($categories_stmt);
    echo json_encode($data);
  } else {
    // non admin
    echo json_encode(false);
  }
}
finally {
  oci_close($conn);
}