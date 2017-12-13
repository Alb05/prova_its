<?php
include('conn.php');

// aggiungo gli header per restituire json
header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');

// mi faccio dare dall'utente usrname e password
$username = $_POST['usrn'];
$password = $_POST['pwd'];

// creo la query parametrizzata
$query = 'SELECT SALT, PASSWORD FROM USERS WHERE USERNAME = :usrn';

// creo un array che conterrà lq righe restituitedal db
$data = array();

// eseguo la query sul db passando l'username al parametro :usrn
$statement = oci_parse($conn, $query);
oci_bind_by_name($statement, ':usrn', $username);
oci_execute($statement);

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
    echo json_encode(true);
  } else {
    echo json_encode(false);
  }
} else {
  echo json_encode(false);
}

// chiudo le connessioni
oci_free_statement($statement);
oci_close($conn);