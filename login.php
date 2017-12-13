<?php
include('conn.php');
try {
  // aggiungo gli header per restituire json
  header('Content-Type: application/json;charset=utf-8');
  header('Access-Control-Allow-Origin: *');

  // mi faccio dare dall'utente usrname e password
  $username = $_POST['username'];
  $password = $_POST['password'];

  // creo la query parametrizzata
  $query = 'SELECT * FROM USERS WHERE USERNAME = :usrn';

  // creo un array che conterrà lq righe restituitedal db
  $data = array();

  // eseguo la query sul db passando l'username al parametro :usrn
  $statement = oci_parse($conn, $query);
  oci_bind_by_name($statement, ':usrn', $username);

  if (oci_execute($statement)) {
    // mi faccio dare dal db le righe e le aggiungo all'array
    while ($row = oci_fetch_assoc($statement)) {
      $data[] = $row;
    }

    // controllo che esista l'utente
    if (count($data) > 0) {
      $user = $data[0];
      // creo un hash con la password fornita dall'utente e il sale ottenuto dal db
      $input = hash('sha512', $password.$user['SALT']);
      //controllo che la password sia uguale alla password sul db
      if (strcmp($input, $user['PASSWORD']) == 0) {
        echo json_encode($data[0]);
      } else {
        echo json_encode(NULL);
      }
    } else {
      echo json_encode(NULL);
    }
  } else {
    echo json_encode(NULL);
  }
  oci_free_statement($statement);
}
finally {
  // chiudo le connessioni
  oci_close($conn);
}