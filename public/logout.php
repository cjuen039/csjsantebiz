<?php
session_start();
session_destroy();
header("Location: /tasksmngr/public/login.php");
exit();
