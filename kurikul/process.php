<?php
    include 'popuna_array.php';
    


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_aktivnosti = $_POST['value'];
    popuni($id_aktivnosti);
    
    $data = $Aktivnost_array["Naziv"];
    echo $Aktivnost_array["Naziv"];

    echo json_encode($data);

    // Free resources
    
}
?>
