<?php
include('conn.php');
session_destroy();
header('LOCATION:index.php');