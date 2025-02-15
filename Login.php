<?php
// Start sesji
session_start();

// Dane do połączenia z bazą danych
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "calendar";

// Nawiązanie połączenia z bazą danych
$conn = mysqli_connect($hostname, $username, $password, $dbname);

// Sprawdzenie połączenia
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Deklaracja zmiennej na komunikaty
$error_message = "";
$success_message = "";

// Obsługa formularza rejestracji
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];

    // Sprawdzenie, czy hasła są zgodne
    if ($password !== $repeat_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Sprawdzenie, czy nazwa użytkownika jest unikalna
        $check_sql = "SELECT * FROM users WHERE name = '$name'";
        $result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($result) > 0) {
            $error_message = "Username already exists!";
        } else {
            // Hashowanie hasła
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Dodanie użytkownika do bazy danych
            $sql = "INSERT INTO users (name, password) VALUES ('$name', '$hashed_password')";

            if (mysqli_query($conn, $sql)) {
                $success_message = "Registration successful! You can now log in.";
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Obsługa formularza logowania
if (isset($_POST['login'])) {
    $name = $_POST['login_name'];
    $password = $_POST['login_password'];

    // Sprawdzenie, czy użytkownik istnieje
    $sql = "SELECT * FROM users WHERE name = '$name'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Weryfikacja hasła
        if (password_verify($password, $user['password'])) {
            // Zapisz nazwę użytkownika w sesji
            $_SESSION['user'] = $name;

            // Przekierowanie do timestamp.php
            header("Location: timestamp.php");
            exit();
        } else {
            $error_message = "Incorrect password or name!";
        }
    } else {
        $error_message = "Incorrect password or name!";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register or Login</title>
    <link rel="stylesheet" type="text/css" href="stylesLogin.css">
</head>
<body>
    <div class="form-container">
        <div class="form-wrapper">
            <!-- Formularz Rejestracji -->
            <div class="form-section">
                <h1>Register</h1>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="repeat_password">Repeat Password:</label>
                        <input type="password" id="repeat_password" name="repeat_password" required>
                    </div>
                    <button type="submit" name="register">Register</button>
                </form>
            </div>

            <!-- Formularz Logowania -->
            <div class="form-section">
                <h1>Login</h1>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="login_name">Name:</label>
                        <input type="text" id="login_name" name="login_name" required>
                    </div>
                    <div class="form-group">
                        <label for="login_password">Password:</label>
                        <input type="password" id="login_password" name="login_password" required>
                    </div>
                    <button type="submit" name="login">Login</button>
                </form>

                <!-- Kontener na wiadomości poniżej przycisku Login -->
                <div id="login-message-box" class="message-box"></div>
            </div>
        </div>
    </div>

    <!-- Skrypt JavaScript na końcu strony -->
    <script>
        // Funkcja do wyświetlania wiadomości
        function showMessage(message) {
            var messageBox = document.getElementById("login-message-box");

            // Ustaw wiadomość
            messageBox.textContent = message;

            // Pokaż wiadomość (dodaj klasę show)
            messageBox.classList.add("show");

            // Po 4 sekundach usuń wiadomość
            setTimeout(function() {
                messageBox.classList.remove("show");
            }, 4000); // 4 sekundy
        }

        // Sprawdzenie i wyświetlenie komunikatu, jeśli jest
        <?php if ($error_message): ?>
            showMessage("<?php echo $error_message; ?>");
        <?php elseif ($success_message): ?>
            showMessage("<?php echo $success_message; ?>");
        <?php endif; ?>
    </script>
</body>
</html>
