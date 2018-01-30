<?php
include('conn.php');

// aggiungo gli header per restituire json
header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

if (isset($_SESSION['utente'])) {
  try {
    // creo un array che conterrà le righe restituite dal db
    $data = array();
    if (isset($_GET['category']) && $_GET['category'] > 0 && isset($_GET['title'])) {
      // libri con una categoria e un titolo specifico
      $query = "SELECT b.BOOK_ID, b.PHOTO, b.TITLE, b.ISBN, b.AUTHOR, c.CATEGORY_NAME, b.DESCRIPTION, b.PAGES, b.PUB_DATE, b.PRICE, w.QUANTITY FROM BOOKS b, CATEGORIES c, WAREHOUSE w WHERE b.CATEGORY_ID = c.CATEGORY_ID AND b.BOOK_ID = w.BOOK_ID AND w.QUANTITY > 0 AND b.CATEGORY_ID = :cat AND REGEXP_LIKE(b.TITLE, :title, 'i') ORDER BY b.TITLE";
      $title = $_GET['title'];
      $statement = oci_parse($conn, $query);
      oci_bind_by_name($statement, ':cat', $_GET['category']);
      oci_bind_by_name($statement, ':title', $title);
      oci_execute($statement);
      
      while ($row = oci_fetch_assoc($statement)) {
        $data[] = $row;
      }
    } elseif (isset($_GET['category']) && $_GET['category'] > 0) {
      // libri con una categoria specifica
      $query = 'SELECT b.BOOK_ID, b.PHOTO, b.TITLE, b.ISBN, b.AUTHOR, c.CATEGORY_NAME, b.DESCRIPTION, b.PAGES, b.PUB_DATE, b.PRICE, w.QUANTITY FROM BOOKS b, CATEGORIES c, WAREHOUSE w WHERE b.CATEGORY_ID = c.CATEGORY_ID AND b.BOOK_ID = w.BOOK_ID AND w.QUANTITY > 0 AND b.CATEGORY_ID = :cat ORDER BY b.TITLE';
      $statement = oci_parse($conn, $query);
      oci_bind_by_name($statement, ':cat', $_GET['category']);
      oci_execute($statement);
      
      while ($row = oci_fetch_assoc($statement)) {
        $data[] = $row;
      }
    } elseif (isset($_GET['title'])) {
      // libri con un titolo specifico
      // creo la query parametrizzata
      $query = "SELECT b.BOOK_ID, b.PHOTO, b.TITLE, b.ISBN, b.AUTHOR, c.CATEGORY_NAME, b.DESCRIPTION, b.PAGES, b.PUB_DATE, b.PRICE, w.QUANTITY FROM BOOKS b, CATEGORIES c, WAREHOUSE w WHERE b.CATEGORY_ID = c.CATEGORY_ID AND b.BOOK_ID = w.BOOK_ID AND w.QUANTITY > 0 AND REGEXP_LIKE(b.TITLE, :title, 'i') ORDER BY b.TITLE";
      $title = $_GET['title'];
      // eseguo la query sul db passando l'username al parametro :title
      $statement = oci_parse($conn, $query);
      oci_bind_by_name($statement, ':title', $title);
      oci_execute($statement);
  
      while ($row = oci_fetch_assoc($statement)) {
        $data[] = $row;
      }
    } else {
      // tutti i libri
      // creo la query parametrizzata
      $query = 'SELECT b.BOOK_ID, b.PHOTO, b.TITLE, b.ISBN, b.AUTHOR, c.CATEGORY_NAME, b.DESCRIPTION, b.PAGES, b.PUB_DATE, b.PRICE, w.QUANTITY FROM BOOKS b, CATEGORIES c, WAREHOUSE w WHERE b.CATEGORY_ID = c.CATEGORY_ID AND b.BOOK_ID = w.BOOK_ID AND w.QUANTITY > 0 ORDER BY b.TITLE';
  
      $statement = oci_parse($conn, $query);
      oci_execute($statement);
  
      while ($row = oci_fetch_assoc($statement)) {
        $data[] = $row;
      }
    }
    oci_free_statement($statement);
    echo json_encode($data);
  }
  finally {
    // chiudo le connessioni
    oci_close($conn);
    exit();
  }
}