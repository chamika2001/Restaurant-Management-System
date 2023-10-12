<?php
// Function to fetch data from the database
function fetchDataFromDatabase()
{
    // Add your database connection details here
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "book";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM reservations";
    $result = $conn->query($sql);

    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    $conn->close();

    return $data;
}

// Function to remove reservation and send email
function removeReservation($id)
{
    // Add your database connection details here
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "book";

    // Establish a new connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the connection is open
    if ($conn && $conn->ping()) {
        // Fetch reservation details before deletion
        $reservation = fetchReservationDetails($conn, $id);

        // Check if the reservation exists
        if ($reservation) {
            // Delete the reservation from the main table
            $sqlDelete = "DELETE FROM reservations WHERE order_id = '$id'";
            $resultDelete = $conn->query($sqlDelete);

            // Check if the deletion was successful
            if ($resultDelete) {
                // Send email to the customer
                $emailSent = sendCancellationEmail($reservation);

                // Check if the email was sent successfully
                if ($emailSent) {
                    echo "Reservation removed successfully, and cancellation email sent.";
                } else {
                    echo "Reservation removed successfully, but there was an issue sending the cancellation email.";
                }
            } else {
                // If the query fails, display an error message
                echo "Error deleting record: " . $conn->error;
            }
        } else {
            echo "Reservation not found.";
        }

        // Close the connection
        $conn->close();
    } else {
        // If the connection is closed, display an error message
        echo "Connection closed. Cannot perform the operation.";

        // Close the connection
        $conn->close();
    }
}

// Function to fetch reservation details
function fetchReservationDetails($conn, $id)
{
    $sql = "SELECT * FROM reservations WHERE order_id = '$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false;
    }
}

// Function to send cancellation email
function sendCancellationEmail($reservation)
{
    $to = $reservation['email'];
    $subject = "Reservation Cancellation";
    $message = "Dear " . $reservation['first_name'] . " " . $reservation['last_name'] . ",\n\n";
    $message .= "Your reservation with Order ID " . $reservation['order_id'] . " has been canceled.\n\n";
    $message .= "Thank you for choosing our service.\n\n";
    $message .= "Best regards,\nThe Booking Team";

    // Additional headers
    $headers = "From: opacarophilerestaurant@gmail.com"; // Replace with your email address

    // Send email
    return mail($to, $subject, $message, $headers);
}

// Function to move today's reservations to a separate table and update the main table
function acceptTodayBooking($conn)
{
    // Get today's date
    $todayDate = date("Y-m-d");

    // Select reservations for today
    $sqlSelectToday = "SELECT * FROM reservations WHERE date = '$todayDate'";
    $resultSelectToday = $conn->query($sqlSelectToday);

    // Check if there are reservations for today
    if ($resultSelectToday->num_rows > 0) {
        // Insert today's reservations into a "today" table with a previous_id column
        $sqlMoveToToday = "INSERT INTO today (first_name, last_name, email, contact_number, package, date, time, guests, previous_id)
                           SELECT first_name, last_name, email, contact_number, package, date, time, guests, order_id
                           FROM reservations WHERE date = '$todayDate'";
        $resultMoveToToday = $conn->query($sqlMoveToToday);

        // Get the last inserted ID in the "today" table
        $lastInsertedId = $conn->insert_id;

        // Delete today's reservations from the main table
        $sqlDeleteToday = "DELETE FROM reservations WHERE date = '$todayDate'";
        $resultDeleteToday = $conn->query($sqlDeleteToday);

        // Check if all queries were successful
        return $resultMoveToToday && $resultDeleteToday;
    } else {
        // If there are no reservations for today, return false
        return false;
    }
}

// Function to fetch filtered data from the database
function fetchFilteredData($searchKey, $column)
{
    // Add your database connection details here
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "book";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM reservations WHERE $column LIKE ?");
    $searchKey = "%" . $searchKey . "%";
    $stmt->bind_param("s", $searchKey);
    $stmt->execute();

    $result = $stmt->get_result();

    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    $stmt->close();
    $conn->close();

    return $data;
}

// Fetch data from the database
$reservations = fetchDataFromDatabase();

// Check if it's a removal request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add your database connection details here
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "book";

    // Establish a new connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the "Remove All Data" button was clicked
    if (isset($_POST['remove_all'])) {
        // Remove all reservations and reset auto-increment
        removeAllReservations($conn);

        // Fetch data again after removal
        $reservations = fetchDataFromDatabase();

        // Redirect to avoid resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Check if the "Accept Today's Bookings" button was clicked
    if (isset($_POST['accept_today_booking'])) {
        // Accept today's bookings
        acceptTodayBooking($conn);

        // Fetch data again after processing
        $reservations = fetchDataFromDatabase();

        // Redirect to avoid resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Check if search form is submitted
    if (isset($_POST['search'])) {
        // Get the search key and selected column
        $searchKey = $_POST['search_key'];
        $searchColumn = $_POST['search_column'];

        // Fetch filtered data
        $reservations = fetchFilteredData($searchKey, $searchColumn);
    }

    // Check if remove reservation form is submitted
    if (isset($_POST['remove_id'])) {
        // Remove the selected reservation
        $removeId = $_POST['remove_id'];
        removeReservation($removeId);

        // Fetch data again after removal
        $reservations = fetchDataFromDatabase();
    }

    // Close the connection
    $conn->close();
}
?>

<?php
// ... (Your existing PHP code)

// Check if refresh button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['refresh'])) {
    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!-- PHP code to handle form submission and removal -->
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Access the global variables
        $servername = "localhost";
        $username = "root";
        $password = "";  // No password by default
        $dbname = "book";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if (isset($_POST['remove_id'])) {
            // Remove the selected reservation
            $removeId = $_POST['remove_id'];
            removeReservation($removeId);

            // Fetch data again after removal
            $reservations = fetchDataFromDatabase();

            // Redirect to avoid resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            // ... (rest of the form submission processing remains unchanged)
        }

        $conn->close(); // Close the connection
    }
    ?>

<?php
// Function to remove all reservations
function removeAllReservations($conn)
{
    $sqlDelete = "DELETE FROM reservations";
    $sqlResetAutoIncrement = "ALTER TABLE reservations AUTO_INCREMENT = 1";
    
    // Execute the delete query
    $resultDelete = $conn->query($sqlDelete);

    // Execute the reset auto-increment query
    $resultResetAutoIncrement = $conn->query($sqlResetAutoIncrement);

    // Check if both queries were successful
    if ($resultDelete && $resultResetAutoIncrement) {
        return true;
    } else {
        // If any query fails, display an error message
        echo "Error deleting all records: " . $conn->error;
        return false;
    }
}

// Check if it's a remove all request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_all'])) {
    // Add your database connection details here
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "book";

    // Establish a new connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Remove all reservations and reset auto-increment
    removeAllReservations($conn);

    // Fetch data again after removal
    $reservations = fetchDataFromDatabase();

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();

    $conn->close();
}
?>
<?php
// Check if it's an "accept today booking" request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accept_today_booking'])) {
    // Add your database connection details here
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "book";

    // Establish a new connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Accept today's bookings and get the result
    $result = acceptTodayBooking($conn);

    // Fetch data again after processing
    $reservations = fetchDataFromDatabase();

    // Display the result
    if ($result) {
        echo "Today's reservations moved successfully.";
    } else {
        echo "No today's reservations to move.";
    }

    $conn->close();
}
?>


<!-- HTML form -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="images/logo.png" type="image/png"> 
    <title>Early Reservations</title>
    <link rel="stylesheet" href="css/today.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
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
                <h4 class="ph4">Early Reservations</h4>
                <br>
                <h1>Early<span>Reservations</span></h1>
            </div>
        </div>
    </div>

    <br><br>
    <h1 class="mh1">Early Reservations</h1>
    <!-- HTML form -->
    <form  class="search-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label class="form-label" for="search_key">Search Key:</label>
        <input class="form-input" type="text" name="search_key" id="search_key" required>
        <label class="form-label" for="search_column">Search Column:</label>
        <select class="form-select" name="search_column" id="search_column" required>
            <option value="order_id">Order ID</option>
            <option value="first_name">First Name</option>
            <option value="last_name">Last Name</option>
            <option value="email">Email</option>
            <option value="contact_number">Contact Number</option>
            <option value="package">Package</option>
            <option value="date">Date</option>
            <option value="time">Time</option>
            <option value="guests">Guests</option>
        </select>
        <div class="button-container">
                    <button type="submit" name="search" class="searchb">Search</button>
                </form>

                <!-- Refresh Button -->
                <form class="refresh-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <button type="submit" name="refresh">Refresh</button>
                </form>
        </div>
    </form>

    <?php if (!empty($reservations)) : ?>
        
        <div class="reservation-table">
            <table class="reservation-table" >
                <tr class="table-header">
                    <th>Order ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>Package</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Guests</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($reservations as $reservation) : ?>
                    <tr class="table-row">
                        <td>PNO <?php echo $reservation['order_id']; ?></td>
                        <td><?php echo $reservation['first_name']; ?></td>
                        <td><?php echo $reservation['last_name']; ?></td>
                        <td><?php echo $reservation['email']; ?></td>
                        <td><?php echo $reservation['contact_number']; ?></td>
                        <td><?php echo $reservation['package']; ?></td>
                        <td><?php echo $reservation['date']; ?></td>
                        <td><?php echo $reservation['time']; ?></td>
                        <td><?php echo $reservation['guests']; ?></td>
                        <td>
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                <input type="hidden" name="remove_id" value="<?php echo $reservation['order_id']; ?>">
                                <button class="remove-btn" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>

    <div class="button-container">
        <!-- Button to accept today's bookings -->
        <form class="remove-all-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <button class="action-btn" type="submit" name="accept_today_booking">Accept Today's Bookings</button>
        </form>

        <!-- Button to remove all data -->
        <form class="remove-all-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <button class="action-btn" type="submit" name="remove_all">Remove All Data</button>
        </form>

        <!-- Today's Reservations Button -->
        <form class="today-reservations-form" method='post' action='today.php'>
            <button class="action-btn" type='submit' name='today_reservations'>Today's Reservations</button>
        </form>

        <!-- Today's Reservations Button -->
        <form class="previous-reservations-form" method='post' action='contact_fetch.php'>
                <button type='submit' name='today_reservations'>Customer Quries</button>
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


<!-- Add the button to accept today's bookings in your HTML form -->

</body>

</html>
