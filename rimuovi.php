<?php
include('conn.php');
if (isset($_SESSION['utente'])) {
  for ($i = 0; $i < count($_SESSION['carrello']); $i++) {
    if ($_SESSION['carrello'][$i]['BOOK_ID'] == $_POST['bookid']) {
      unset($_SESSION['carrello'][$i]);
    }
  }
  header('LOCATION:carrello.php');
} else {
  echo '<p>non sei loggato</p>';
  header('refresh:3;index.php');
}