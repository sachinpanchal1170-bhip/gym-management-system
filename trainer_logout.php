<?php
// Use the SAME session name as trainer.php
session_name("trainer_session");
session_start();

// Unset all trainer session variables
session_unset();

// Destroy the session completely
session_destroy();

// Redirect to trainer login page
header("Location: trainer_login.php");
exit();
