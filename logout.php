<?php
// Start sesji
session_start();

// Usunięcie danych sesji
session_unset();
session_destroy();

// Przekierowanie do strony logowania
header("Location: Login.php");
exit();
?>
