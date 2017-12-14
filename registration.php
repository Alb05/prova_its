<?php include('conn.php'); ?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration</title>
  </head>
  <body>
  <?php
  if (!isset($_POST['username']) && !isset($_POST['password']) && !isset($_POST['firstname']) && !isset($_POST['lastname']) && !isset($_POST['mail']) && !isset($_POST['phone']) && !isset($_POST['address']) && !isset($_POST['city']) && !isset($_POST['postalcode']) && !isset($_POST['country'])) {
  ?>
  <h1>Register</h1>
  <form action="registration.php" method="POST">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" size="32" maxlength="32" value=""/><br><br>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" size="32" value=""/><br><br>
    <label for="firstname">Firstname</label>
    <input type="text" id="firstname" name="firstname" size="32" maxlength="32" value=""/><br><br>
    <label for="lastname">Lastname</label>
    <input type="text" id="lastname" name="lastname" size="32" maxlength="32" value=""/><br><br>
    <label for="mail">Mail</label>
    <input type="text" id="mail" name="mail" size="64" maxlength="64" value=""/><br><br>
    <label for="phone">Phone</label>
    <input type="text" id="phone" name="phone" size="32" maxlength="32" value=""/><br><br>
    <label for="address">Address</label>
    <input type="text" id="address" name="address" size="64" maxlength="64" value=""/><br><br>
    <label for="city">City</label>
    <input type="text" id="city" name="city" size="32" maxlength="32" value=""/><br><br>
    <label for="postalcode">Postal Code</label>
    <input type="text" id="postalcode" name="postalcode" size="16" maxlength="16" value=""/><br><br>
    <label for="country">Country</label>
    <input type="text" id="country" name="country" size="32" maxlength="32" value=""/><br><br>
    <input type="submit" value="Login"><br><br>
  </form>

  <?php
  } else {
    try {
      // mi faccio dare dall'utente i dati
      $username = $_POST['username'];
      $first_name = $_POST['firstname'];
      $last_name = $_POST['lastname'];
      $mail = $_POST['mail'];
      $phone = $_POST['phone'];
      $address = $_POST['address'];
      $city = $_POST['city'];
      $password = $_POST['password'];
      $postal_code = $_POST['postalcode'];
      $country = $_POST['country'];

      // creo un array che conterrà lq righe restituitedal db
      $data = array();
      $user = array();

      // creo la query parametrizzata per cercare l'utente
      $search_query = 'SELECT USERNAME FROM USERS WHERE USERNAME = :usrn';
      $search_statement = oci_parse($conn, $search_query);
      oci_bind_by_name($search_statement, ':usrn', $username);

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

          // eseguo la query sul db passando l'username al parametro :usrn
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
              echo "<p>registrazione avvenuta con successo!</p>";
              header('refresh:3;index.php');
            } else {
              echo "<p>errore di commit</p>";
            }
          } else {
            echo "<p>errore query inserimento</p>";
          }
          oci_free_statement($insert_statement);
        } else {
          echo "<p>l'utente esiste già</p>";
        }
      } else {
        echo "<p>errore query ricerca</p>";
      }
    }
    finally {
      // chiudo le connessioni
      oci_close($conn);
    }
  }
  ?>
  </body>
</html>