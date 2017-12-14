<?php
include('conn.php');
if (isset($_SESSION['utente'])) {
try {
if (isset($_POST['bookid']) && isset($_POST['bookqty'])) {
  $inserted = false;
  for ($i = 0; $i < count($_SESSION['carrello']); $i++) {
    if ($_SESSION['carrello'][$i]['BOOK_ID'] == $_POST['bookid']) {
      $_SESSION['carrello'][$i]['QUANTITY'] += $_POST['bookqty'];
      $inserted = true;
    }
  }
  if (!$inserted) {
    $_SESSION['carrello'][] = array('BOOK_ID' => $_POST['bookid'], 'QUANTITY' => $_POST['bookqty']);
  }
}
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Carrello</title>
  </head>
  <body>
    <h1>Carrello</h1>
    <a href="logout.php"><button>Logout</button></a>
    <a href="elenco.php"><button>Torna agli acquisti</button></a>
    <?php
    if (count($_SESSION['carrello']) > 0) {
      echo '<a href="ordina.php"><button>Acquista</button></a><br><br>';
    }
    else {
      echo '<br><br>';
    }
    ?>
    <table cellpadding="8px" border="1px">
      <thead>
        <th>Titolo</th>
        <th>Descrizione</th>
        <th>Quantità</th>
        <th>Prezzo Totale</th>
        <th>Rimuovi</th>
      </thead>
      <tbody>
        <?php
        if (count($_SESSION['carrello']) == 0) {
          echo '<p>il tuo carrello è vuoto</p>';
        } else {
          foreach ($_SESSION['carrello'] as $libro) {
            $query = 'SELECT TITLE, DESCRIPTION, PRICE FROM BOOKS WHERE BOOK_ID = :bookid';
            $statement = oci_parse($conn, $query);
            oci_bind_by_name($statement, ':bookid', $libro['BOOK_ID']);
            oci_execute($statement);
            while ($row = oci_fetch_assoc($statement)) {
              echo '<tr><td>'.$row['TITLE'].'</td><td>'.$row['DESCRIPTION'].'</td><td>'.$libro['QUANTITY'].'</td><td>'.($row['PRICE']*$libro['QUANTITY']).'</td></tr>';
            }
            oci_free_statement($statement);
          }
        }
        ?>
      </tbody>
    </table>
  </body>
</html>
<?php
}
finally {
  oci_close($conn);
}
} else {
  echo '<p>non sei loggato</p>';
  header('refresh:3;index.php');
}
?>