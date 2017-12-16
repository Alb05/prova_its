<?php
include('conn.php');
try {
  // aggiungo gli header per restituire json
  header('Content-Type: application/json;charset=utf-8');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
  header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

  // mi faccio dare dall'utente usrname e password
  $posted = json_decode(file_get_contents("php://input"));
  $username = $posted->username;
  $password = $posted->password;

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
        $_SESSION['utente'] = $user;
        $_SESSION['carrello'] = array();
        echo json_encode(true);
        //header('LOCATION:http://api.mano/home');
      } else {
        echo json_encode(false);
        //header('LOCATION:http://api.mano/login');
      }
    } else {
      echo json_encode(false);
      //header('LOCATION:http://api.mano/login');
    }
  } else {
    echo json_encode(false);
    //header('LOCATION:http://api.mano/login');
  }
  oci_free_statement($statement);
}
finally {
  // chiudo le connessioni
  oci_close($conn);
  exit();
}