<?php
include('conn.php');
try {
  // aggiungo gli header per restituire json
  header('Content-Type: application/json;charset=utf-8');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
  header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

  // mi faccio dare dall'utente i dati
  $posted = json_decode(file_get_contents("php://input"));
  $username = $posted->username;
  $first_name = $posted->firstname;
  $last_name = $posted->lastname;
  $mail = $posted->mail;
  $phone = $posted->phone;
  $address = $posted->address;
  $city = $posted->city;
  $password = $posted->password;
  $postal_code = $posted->postalcode;
  $country = $posted->country;

  // creo un array che conterrà lq righe restituitedal db
  $data = array();
  $user = array();

  // creo la query parametrizzata per cercare l'utente
  $search_query = 'SELECT USERNAME FROM USERS WHERE MAIL = :mail';
  $search_statement = oci_parse($conn, $search_query);
  oci_bind_by_name($search_statement, ':mail', $mail);

  // eseguo la query di ricerca
  if (oci_execute($search_statement)) {
    while ($row = oci_fetch_assoc($search_statement)) {
      $user[] = $row;
    }
    oci_free_statement($search_statement);
    if (count($user) == 0) {
      // creo la query parametrizzata per l'inserimento
      $insert_query = 'INSERT INTO USERS (USERNAME, FIRST_NAME, LAST_NAME, MAIL, PHONE, ADDRESS, CITY, SALT, PASSWORD, GROUP_ID, CREATED_AT, POSTAL_CODE, COUNTRY) VALUES (:username, :firstname, :lastname, :mail, :phone, :address, :city, :salt, :password, 2, CURRENT_DATE, :postalcode, :country)';
      
      // genero il sale e la password
      $salt = hash('sha512', openssl_random_pseudo_bytes(128));
      $password_hash = hash('sha512', $password.$salt);

      // eseguo la query sul db passando le variabili
      $insert_statement = oci_parse($conn, $insert_query);
      oci_bind_by_name($insert_statement, ':username', $username);
      oci_bind_by_name($insert_statement, ':firstname', $first_name);
      oci_bind_by_name($insert_statement, ':lastname', $last_name);
      oci_bind_by_name($insert_statement, ':mail', $mail);
      oci_bind_by_name($insert_statement, ':phone', $phone);
      oci_bind_by_name($insert_statement, ':address', $address);
      oci_bind_by_name($insert_statement, ':city', $city);
      oci_bind_by_name($insert_statement, ':salt', $salt);
      oci_bind_by_name($insert_statement, ':password', $password_hash);
      oci_bind_by_name($insert_statement, ':postalcode', $postal_code);
      oci_bind_by_name($insert_statement, ':country', $country);

      // eseguo la query di inserimento
      if (oci_execute($insert_statement)) {
        // faccio il commit delle modifiche
        if (oci_commit($conn)) {
          echo json_encode(true);
          //header('LOCATION:/login');
        } else {
          echo json_encode(false);
          //header('LOCATION:/login');
        }
      } else {
        oci_rollback($conn);
        echo json_encode(false);
        //header('LOCATION:/login');
      }
      oci_free_statement($insert_statement);
    } else {
      echo json_encode(false);
      //header('LOCATION:/login');
    }
  } else {
    echo json_encode(false);
    //header('LOCATION:/login');
  }
}
finally {
  // chiudo le connessioni
  oci_close($conn);
}
