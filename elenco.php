<?php include('conn.php'); ?>
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
      </thead>
      <tbody>
      <?php
      try {
        // creo la query parametrizzata
        $query = 'SELECT b.TITLE, b.ISBN, b.AUTHOR, c.CATEGORY_NAME, b.DESCRIPTION, b.PAGES, b.PUB_DATE, b.PRICE, w.QUANTITY FROM BOOKS b, CATEGORIES c, WAREHOUSE w WHERE b.CATEGORY_ID = c.CATEGORY_ID AND b.BOOK_ID = w.BOOK_ID';

        // eseguo la query sul db passando l'username al parametro :usrn
        $statement = oci_parse($conn, $query);
        oci_execute($statement);
        
        while ($row = oci_fetch_assoc($statement)) {
          echo '<tr><td>'.$row['TITLE'].'</td><td>'.$row['ISBN'].'</td><td>'.$row['AUTHOR'].'</td><td>'.$row['CATEGORY_NAME'].'</td><td>'.$row['DESCRIPTION'].'</td><td>'.$row['PAGES'].'</td><td>'.$row['PUB_DATE'].'</td><td>'.$row['PRICE'].' €</td><td>'.$row['QUANTITY'].'</td></tr>';
        }
        oci_free_statement($statement);
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