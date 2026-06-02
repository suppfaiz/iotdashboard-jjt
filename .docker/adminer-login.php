<?php
function adminer_object() {
    require_once "./adminer-class.php";
    return new AdminerSoftware();
}
include "./adminer.php";
