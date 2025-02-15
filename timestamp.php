<?php
// Start sesji
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user'])) {
    header("Location: Login.php");
    exit();
}

// Pobranie nazwy użytkownika z sesji
$user = $_SESSION['user'];

// Dane połączenia z bazą danych
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "calendar";

// Nawiązanie połączenia
$conn = mysqli_connect($hostname, $username, $password, $dbname);

// Sprawdzenie połączenia
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Pobranie aktualnego miesiąca i roku
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Korekcja wartości miesiąca i roku
if ($currentMonth < 1) {
    $currentMonth = 12;
    $currentYear--;
} elseif ($currentMonth > 12) {
    $currentMonth = 1;
    $currentYear++;
}

// Obliczenie pierwszego dnia miesiąca i liczby dni w miesiącu
$firstDayOfMonth = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
$daysInMonth = date('t', $firstDayOfMonth);
$monthName = date('F', $firstDayOfMonth);

// Obliczenie numeru dnia tygodnia pierwszego dnia miesiąca (0 = niedziela, 6 = sobota)
$firstDayOfWeek = date('w', $firstDayOfMonth);

// Pobranie dzisiejszej daty
$today = date('Y-m-d');

// Obsługa usuwania wydarzenia
if (isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];

    // Usuwanie wydarzenia z bazy danych
    $sql = "DELETE FROM events WHERE id = $event_id AND user_name = '$user'";
    if (mysqli_query($conn, $sql)) {
        echo "Event deleted successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Dodawanie wydarzenia
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $description = $_POST['description'];

    $sql = "INSERT INTO events (user_name, event_date, event_time, description)
    VALUES ('$user', '$event_date', '$event_time', '$description')";

    if (mysqli_query($conn, $sql)) {
        echo "Event added successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Pobieranie wydarzeń dla bieżącego miesiąca
$events = [];
$sql = "SELECT * FROM events WHERE user_name = '$user' AND MONTH(event_date) = $currentMonth AND YEAR(event_date) = $currentYear";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $events[$row['event_date']][] = $row;
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Calendar</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script>
       let selectedCell = null; // Przechowuje aktualnie wybrany element

         function openEventForm(date, cell) {
              const form = document.getElementById('eventForm');
              document.getElementById('event_date').value = date;
              form.style.display = 'block';

              // Usuń zaznaczenie z poprzedniego elementu
            if (selectedCell) {
               selectedCell.classList.remove('selected');
           }
             // Dodaj zaznaczenie do klikniętego elementu
            cell.classList.add('selected');
             selectedCell = cell; // Aktualizuj wybrany element
        }

        function closeForm() {
            document.getElementById('eventForm').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
             const cells = document.querySelectorAll('td:not(.empty)');
                cells.forEach(cell => {
                  const date = cell.getAttribute('data-date'); // pobierz date komórki

                cell.addEventListener('click', function() {
                 openEventForm(date, this)
               });
            });
        });

        
        
    </script>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($user); ?>!</h1>
    <h2><?php echo $monthName . " " . $currentYear; ?></h2>
    <!-- Nawigacja po miesiącach -->
    <div>
        <a href="?month=<?php echo ($currentMonth == 1) ? 12 : $currentMonth - 1; ?>&year=<?php echo ($currentMonth == 1) ? $currentYear - 1 : $currentYear; ?>">Previous</a> |
        <a href="?month=<?php echo ($currentMonth == 12) ? 1 : $currentMonth + 1; ?>&year=<?php echo ($currentMonth == 12) ? $currentYear + 1 : $currentYear; ?>">Next</a>
    </div>

    <!-- Kalendarz -->
    <table>
        <tr>
            <th>Sun</th>
            <th>Mon</th>
            <th>Tue</th>
            <th>Wed</th>
            <th>Thu</th>
            <th>Fri</th>
            <th>Sat</th>
        </tr>
        <tr>
            <?php
            // Puste komórki przed pierwszym dniem miesiąca
            for ($i = 0; $i < $firstDayOfWeek; $i++) {
                echo "<td class='empty'></td>";
            }

            // Komórki dla każdego dnia miesiąca
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
                $class = isset($events[$date]) ? 'event' : '';
                if ($date == $today) {
                    $class .= ' today'; // Dodajemy klasę 'today' dla dzisiejszego dnia
                }
                echo "<td class='$class' data-date='$date'><span>$day</span>";

                echo "<div class='event-container'>";
                // Wyświetlanie wydarzeń
                if (isset($events[$date])) {
                    foreach ($events[$date] as $event) {
                        echo "<small>" . htmlspecialchars($event['description']) . " (" . htmlspecialchars($event['event_time']) . ")</small>";

                        // Przycisk do usuwania wydarzenia
                        echo "<form method='POST' style='display:inline;'>
                                <input type='hidden' name='event_id' value='" . $event['id'] . "'>
                                <input type='submit' name='delete_event' value='Delete' onclick='return confirm(\"Are you sure?\");'>
                              </form>";
                    }
                }
                echo "</div></td>";

                // Przejście do nowego wiersza po sobocie
                if (($day + $firstDayOfWeek) % 7 == 0) {
                    echo "</tr><tr>";
                }
            }

            // Puste komórki po ostatnim dniu miesiąca
            $remainingCells = (7 - (($daysInMonth + $firstDayOfWeek) % 7)) % 7;
            for ($i = 0; $i < $remainingCells; $i++) {
                echo "<td class='empty'></td>";
            }
            ?>
        </tr>
    </table>

<!-- Formularz dodawania wydarzenia -->
<div id="eventForm" style="display:none;">
    <h3>Add Event</h3>
    <form action="" method="POST">
        <input type="hidden" id="event_date" name="event_date">
        <label for="event_time">Time:</label>
        <input type="time" id="event_time" name="event_time" required><br><br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea><br><br>
        <input type="submit" name="add_event" value="Add Event">
        <!-- Przycisk do zamknięcia formularza -->
        <input type="button" id="closeButton" value="Close" onclick="closeForm()">
    </form>
</div>

    <!-- Formularz wylogowania przeniesiony do prawego górnego rogu -->
<form id="logoutForm" action="logout.php" method="POST">
    <input type="submit" name="logout" value="Logout">
</form>
    </form>
</body>
</html>