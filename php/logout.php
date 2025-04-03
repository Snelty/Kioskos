<?php
session_start();
session_destroy();
header("Location: ../html/iniciarsesion.html");
exit();
?>
