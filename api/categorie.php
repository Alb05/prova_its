<?php
include('conn.php');
if (isset($_SESSION['utente'])) {
  try {
    // aggiungo gli header per restituire json
    header('Content-Type: application/json;charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
  
    // creo un array che conterrà le righe restituite dal db
    $data = array();
  
    // creo la query parametrizzata
    $query = 'SELECT * FROM CATEGORIES ORDER BY CATEGORY_NAME';
  
    // eseguo la query sul db passando l'username al parametro :usrn
    $statement = oci_parse($conn, $query);
    oci_execute($statement);
  
    while ($row = oci_fetch_assoc($statement)) {
      $data[] = $row;
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