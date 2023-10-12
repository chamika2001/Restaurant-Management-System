<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "book"; // Change this to the actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if remove button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_id'])) {
    $removeId = $_POST['remove_id'];

    // Remove the data from the database
    $deleteSql = "DELETE FROM submissions WHERE contact_id = $removeId";
    if ($conn->query($deleteSql) === TRUE) {
        echo "Record removed successfully.";
    } else {
        echo "Error removing record: " . $conn->error;
    }

    // Redirect to avoid resubmitting the form data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Check if refresh button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['refresh'])) {
    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Check if remove all button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_all'])) {
    // Remove all data from the 'submissions' table
    $deleteAllSql = "DELETE FROM submissions";
    if ($conn->query($deleteAllSql) === TRUE) {
        echo "All records removed successfully.";

        // Reset the auto-increment counter for 'contact_id'
        $resetAutoIncrementSql = "ALTER TABLE submissions AUTO_INCREMENT = 1";
        if ($conn->query($resetAutoIncrementSql) === TRUE) {
            echo "Auto-increment counter reset successfully.";
        } else {
            echo "Error resetting auto-increment counter: " . $conn->error;
        }
    } else {
        echo "Error removing all records: " . $conn->error;
    }

    // Redirect to avoid resubmitting the form data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch data from the 'submissions' table
$sql = "SELECT * FROM submissions";
$result = $conn->query($sql);

// Variables for search
$searchResults = array();
$searchMessage = "";

// Check if search button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchKey = $_POST['search_key'];
    $searchColumn = $_POST['search_column'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM submissions WHERE $searchColumn LIKE ?");
    $searchKey = "%" . $searchKey . "%";
    $stmt->bind_param("s", $searchKey);
    $stmt->execute();

    $result = $stmt->get_result();

    // Fetch search results
    $searchResults = $result->fetch_all(MYSQLI_ASSOC);

    // Display data in a table
    if ($result->num_rows > 0) {
        $searchMessage = "Search results:";
    } else {
        $searchMessage = "No results found.";
    }

    $stmt->close();
}

// Close the connection
$conn->close();
?>

<!-- HTML form -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="images/logo.png" type="image/png"> 
    <title>Customer Quries</title>
    <link rel="stylesheet" href="css/today.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>

<body>

    <div class="wrappers">
        <div class="main-container">
            <video autoplay muted loop class="background-video">
                <source src="images/vid.mp4" type="video/mp4">
            </video>
            <div class="overlay"></div>
        </div>
        <div class="navbar">

            <img class="logo" src="images/logo.png">
            <ul>
                <li><a  href="index.php">Home</a></li>
                <li><a href="bookingnew.php">Booking</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="packages.php">Packages</a></li>
                <li><a href="contact.php">Contact</a></li>

            </ul>    

            <div class="al">
                <h4 class="ph4">Customer Quries</h4>
                <br>
                <h1>Customer Quries</h1>
            </div>
        </div>
    </div>
    <br><br>
    <h1 class="mh1">Customer Quries</h1>
    <!-- Search Form -->
    <form class="search-form" method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
        <input type='text' name='search_key' placeholder='Search...'>
        <select name='search_column'>
            <option value='contact_id'>Contact ID</option>
            <option value='name'>Name</option>
            <option value='email'>Email</option>
            <option value='message'>Message</option>
        </select>
        <div class="button-container">
                    <button type="submit" name="search" class="searchb">Search</button>
                </form>

                <!-- Refresh Button -->
                <form class="refresh-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <button type="submit" name="refresh">Refresh</button>
                </form>
        </div>

    <?php
    if (!empty($searchMessage)) {
        echo "<p class='search-message'>$searchMessage</p>";
    }

    if (!empty($searchResults) || $result->num_rows > 0) {
        echo "<table class='reservation-table'>";
        echo "<tr class='table-header'><th>Contact ID</th><th>Name</th><th>Email</th><th>Message</th><th>Remove</th></tr>";

        // Display search results or all data in a table
        $dataToShow = !empty($searchResults) ? $searchResults : $result;

        foreach ($dataToShow as $row) {
            echo "<tr class='table-row'>";
            echo "<td>" . $row["contact_id"] . "</td>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["email"] . "</td>";
            echo "<td>" . $row["message"] . "</td>";
            echo "<td><form method='post' action='" . $_SERVER['PHP_SELF'] . "'><input type='hidden' name='remove_id' value='" . $row["contact_id"] . "'><button class='remove-btn' type='submit'>Remove</button></form></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else if ($result->num_rows > 0) {
        // Display data in a table if there are no search results
        echo "<table class='reservation-table>";
        echo "<tr><th>Contact ID</th><th>Name</th><th>Email</th><th>Message</th><th>Remove</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["contact_id"] . "</td>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["email"] . "</td>";
            echo "<td>" . $row["message"] . "</td>";
            echo "<td><form method='post' action='" . $_SERVER['PHP_SELF'] . "'><input type='hidden' name='remove_id' value='" . $row["contact_id"] . "'><button type='submit'>Remove</button></form></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No data found.";
    }
    ?>

    <div class="button-container">
        <!-- Remove All Button -->
        <form class="remove-all-form" method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
            <button class="action-btn" type='submit' name='remove_all'>Remove All</button>
        </form>

        <!-- Previous Reservations Button -->
        <form class="previous-reservations-form" method='post' action='fetch.php'>
            <button class="action-btn" type='submit' name='previous_reservations'>Early Reservations</button>
        </form>

        <!-- Today's Reservations Button -->
        <form class="today-reservations-form" method='post' action='today.php'>
            <button class="action-btn" type='submit' name='today_reservations'>Today's Reservations</button>
        </form>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="footer-col">
                    <h4>company</h4>
                    <ul>
                        <li><a href="aboutus.php">about us</a></li>
                        <li><a href="index.php#services">our services</a></li>
                     
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contact</h4>
                    <ul>
                    <li><a href="https://maps.app.goo.gl/sDskgv8TrvkfiXcEA"><i class="fa fa-map-marker-alt me-3"></i> No.17, Union Place, Colombo 02</a></li>
                        <li><a href="#"><i class="fa fa-phone-alt me-3"></i> +94 112 123456</a></li>
                        <li><a href="mailto:opacarophilerestaurant@gmail.com"><i class="fa fa-envelope me-3"></i>opacarophilerestaurant@gmail.com</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Opening</h4>
                    <ul>
                        <li><a href="#"><u>Monday - Saturday</u></a></li>
                        <li><a href="#">9 AM - 9 PM</a></li>
                        <li><a href="#"><u>Sunday</u></a></li>
                        <li><a href="#">10 AM - 8 PM</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>follow us</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                    <h4>Admin Login</h4>
                    <ul>
                        <li><a href="login.php">Uses Only Admin Panel</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>
