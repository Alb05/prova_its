<?php
include('conn.php');
if (isset($_SESSION['utente'])) {
  session_destroy();
  header('LOCATION:index.php');
} else {
  header('LOCATION:index.php');
}