<?php
include('conn.php');
if (isset($_SESSION['utente'])) {
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Elenco</title>
  </head>
  <body>
    <h1>Elenco</h1>
    <a href="logout.php"><button>Logout</button></a>
    <a href="carrello.php"><button>Carrello</button></a><br>
    <form action="elenco.php" method="GET">
      <select name="catSelect" id="catSelect">
        <option value="0">All</option>
        <?php
        $sel_query = 'SELECT CATEGORY_ID, CATEGORY_NAME FROM CATEGORIES ORDER BY CATEGORY_NAME';
        $sel_statement = oci_parse($conn, $sel_query);
        oci_execute($sel_statement);
        while ($selrow = oci_fetch_assoc($sel_statement)) {
          echo '<option value="'.$selrow['CATEGORY_ID'].'">'.$selrow['CATEGORY_NAME'].'</option>';
        }
        oci_free_statement($sel_statement);
        ?>
      </select>
      <input type="submit" value="Cerca">
    </form>
    <table cellpadding="8px" border="1px">
      <thead>
        <th>Titolo</th>
        <th>ISBN</th>
        <th>Autore</th>
        <th>Categoria</th>
        <th>Descrizione</th>
        <th>Numero di pagine</th>
        <th>Data di pubblicazione</th>
        <th>Prezzo</th>
        <th>Quantità disponibile</th>
        <th>Aggiungi</th>
      </thead>
      <tbody>
      <?php
      try {
        if (!isset($_GET['catSelect']) || $_GET['catSelect'] == 0) {
          // creo la query parametrizzata
          $query = 'SELECT b.BOOK_ID, b.TITLE, b.ISBN, b.AUTHOR, c.CATEGORY_NAME, b.DESCRIPTION, b.PAGES, b.PUB_DATE, b.PRICE, w.QUANTITY FROM BOOKS b, CATEGORIES c, WAREHOUSE w WHERE b.CATEGORY_ID = c.CATEGORY_ID AND b.BOOK_ID = w.BOOK_ID ORDER BY b.TITLE';

          // eseguo la query sul db passando l'username al parametro :usrn
          $statement = oci_parse($conn, $query);
          oci_execute($statement);
          
          while ($row = oci_fetch_assoc($statement)) {
            if (count($row) > 0) {
              $str = '<tr><td>'.$row['TITLE'].'</td><td>'.$row['ISBN'].'</td><td>'.$row['AUTHOR'].'</td><td>'.$row['CATEGORY_NAME'].'</td><td>'.$row['DESCRIPTION'].'</td><td>'.$row['PAGES'].'</td><td>'.$row['PUB_DATE'].'</td><td>'.$row['PRICE'].' €</td><td>'.$row['QUANTITY'].'</td><td><form action="carrello.php" method="POST"><input type="hidden" name="bookid" value="'.$row['BOOK_ID'].'"><select name="bookqty">';
              for ($i = 1; $i <= intval($row['QUANTITY']); $i++) {
                $str .= '<option value='.$i.'>'.$i.'</option>';
              }
              $str .= '</select><input type="submit" value="aggiungi al carrello"></form></td></tr>';
              echo $str;
            } else {
              echo '<p>nessun elemento presente</p>';
            }
          }
          oci_free_statement($statement);
        } else {
          // creo la query parametrizzata
          $query2 = 'SELECT b.BOOK_ID, b.TITLE, b.ISBN, b.AUTHOR, c.CATEGORY_NAME, b.DESCRIPTION, b.PAGES, b.PUB_DATE, b.PRICE, w.QUANTITY FROM BOOKS b, CATEGORIES c, WAREHOUSE w WHERE b.CATEGORY_ID = c.CATEGORY_ID AND b.BOOK_ID = w.BOOK_ID AND b.CATEGORY_ID = :cat ORDER BY b.TITLE';

          // eseguo la query sul db passando l'username al parametro :usrn
          $statement2 = oci_parse($conn, $query2);
          oci_bind_by_name($statement2, ':cat', $_GET['catSelect']);          
          oci_execute($statement2);

          while ($row = oci_fetch_assoc($statement2)) {
            if (count($row) > 0) {
              $str = '<tr><td>'.$row['TITLE'].'</td><td>'.$row['ISBN'].'</td><td>'.$row['AUTHOR'].'</td><td>'.$row['CATEGORY_NAME'].'</td><td>'.$row['DESCRIPTION'].'</td><td>'.$row['PAGES'].'</td><td>'.$row['PUB_DATE'].'</td><td>'.$row['PRICE'].' €</td><td>'.$row['QUANTITY'].'</td><td><form action="carrello.php" method="POST"><input type="hidden" name="bookid" value="'.$row['BOOK_ID'].'"><select name="bookqty">';
              for ($i = 1; $i <= intval($row['QUANTITY']); $i++) {
                $str .= '<option value='.$i.'>'.$i.'</option>';
              }
              $str .= '</select><input type="submit" value="aggiungi al carrello"></form></td></tr>';
              echo $str;
            } else {
              echo '<p>nessun elemento presente</p>';
            }
          }

          /*
          while ($row2 = oci_fetch_assoc($statement2)) {
            if (count($row2) > 0) {
              $str2 = '<tr><td>'.$row2['TITLE'].'</td><td>'.$row2['ISBN'].'</td><td>'.$row2['AUTHOR'].'</td><td>'.$row2['CATEGORY_NAME'].'</td><td>'.$row2['DESCRIPTION'].'</td><td>'.$row2['PAGES'].'</td><td>'.$row2['PUB_DATE'].'</td><td>'.$row2['PRICE'].' €</td><td>'.$row2['QUANTITY'].'</td><td><form action="carrello.php" method="POST"><input type="hidden" name="bookid" value="'.$row2['BOOK_ID'].'"><input type="hidden" name="bookid" value="'.$row2['BOOK_ID'].'"><select name="bookqty">';
              for ($j = 1; $j <= intval($row2['QUANTITY']); $j++) {
                $str2 .= '<option value='.$j.'>'.$j.'</option>';
              }
              $str2 = '</select><input type="submit" value="aggiungi al carrello"></form></td></tr>';
              echo $str2;
            } else {
              echo '<p>nessun elemento presente</p>';
            }
          }
          */
          oci_free_statement($statement2);
        }
      }
      finally {
        // chiudo le connessioni
        oci_close($conn);
        exit();
      }
      ?>
      </tbody>
    </table>
  </body>
</html>
<?php
} else {
  echo '<p>non sei loggato</p>';
  header('refresh:3;index.php');
}
?>