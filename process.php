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

$sender = new Sender;

$sender->process_data($POST)
->send_email();