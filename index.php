<?php include('conn.php'); ?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
  </head>
  <body>
  <?php
  if (!isset($_POST['username']) && !isset($_POST['password'])) {
  ?>
  <h1>Login</h1>
  <form action="index.php" method="POST">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" size="32" maxlength="32" value=""/><br><br>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" size="32" value=""/><br><br>
    <input type="submit" value="Login"><br><br>
  </form>
  <a href="registration.php">Registrati</a>
  <?php
  } else {
    try {
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
            header('LOCATION:elenco.php');
          } else {
            echo '<p>la password non è corretta</p>';
            header('refresh:5;index.php');
          }
        } else {
          echo "<p>l'utente ".$username." non esiste</p>";
          header('refresh:5;index.php');
        }
      } else {
        echo '<p>Oops something went horribly wrong :(</p>';
        header('refresh:5;index.php');
      }
      oci_free_statement($statement);
    }
    finally {
      // chiudo le connessioni
      oci_close($conn);
      exit();
    }
  }
  ?>
  </body>
</html>