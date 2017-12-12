<?php
include('conn.php');

$query = 'SELECT USERNAME, LAST_NAME FROM USERS';/* WHERE USER_ID = :id';*/
//$id = $_GET['id'];
$data = array();

$statement = oci_parse($conn, $query);
//oci_bind_by_name($statement, ':id', $id);
oci_execute($statement);


header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');

while ($row = oci_fetch_assoc($statement)) {
  $data[] = $row;
}

//var_dump($data);

echo json_encode($data);

oci_free_statement($statement);
oci_close($conn);

?>