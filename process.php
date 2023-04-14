<?php
error_reporting(E_STRICT | E_ALL);
require_once "classes/sender.php";

$POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);

if (empty($POST["email-address"])) {
    echo "Email address can not be empty";
    exit;
}
if (empty($POST["body"])) {
    echo "Email body can not be empty";
    exit;
}
if (isset($_FILES["attachment"]) && empty($_FILES["attachment"]["name"])) {
    $dir = "attachment/";
    $location =  $dir.$_FILES["attachment"]["name"];
    $temp_file  =  $FILES['pet_image']['tmp_name'];
}
if(move_uploaded_file($temp_file, $location)){
    $POST["file"] = $location;

    $sender = new Sender;

    $sender->process_data($POST)
    ->send_email();
// delete file after sending email;
    unlink($location);
}