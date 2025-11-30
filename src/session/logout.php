<?php
function logout(): void{
    session_start();
    session_destroy();
    header("location: /");
}

?>