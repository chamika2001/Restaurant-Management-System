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

    $sql = "SELECT * FROM today";
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

// Function to remove reservation
function removeReservation($conn, $id)
{
    // Check if the connection is open
    if ($conn && $conn->ping()) {
        $sql = "DELETE FROM today WHERE order_id = '$id'";
        
        // Execute the query
        $result = $conn->query($sql);

        // Check if the query was successful
        if ($result) {
            return true;
        } else {
            // If the query fails, display an error message
            echo "Error deleting record: " . $conn->error;
            return false;
        }
    } else {
        // If the connection is closed, display an error message
        echo "Connection closed. Cannot perform the operation.";
        return false;
    }
}

// Function to send cancellation email
function sendCancellationEmail($email)
{
    $to = $email;
    $subject = "Reservation Cancellation";
    $message = "Dear customer,\n\nYour reservation has been canceled.\n\nThank you for considering our service.";
    $headers = "From: opacarophilerestaurant@gmail.com"; // Replace with your email address

    // Send email
    return mail($to, $subject, $message, $headers);
}

// Function to fetch filtered data from the database
function fetchFilteredData($conn, $searchKey, $column)
{
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM today WHERE $column LIKE ?");
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

    return $data;
}

// Function to accept reservation and send email
function acceptReservation($conn, $id, $email)
{
    // Check if the connection is open
    if ($conn && $conn->ping()) {
        // Remove the selected reservation
        $sqlRemove = "DELETE FROM today WHERE order_id = '$id'";
        $resultRemove = $conn->query($sqlRemove);

        if ($resultRemove) {
            // Send a thank you email to the customer
            $subject = "Thank You for Visiting Our Restaurant";
            $message = "Dear customer,\n\nThank you for coming to our restaurant. We hope you enjoyed your time. Please visit us again!";
            $headers = "From: opacarophilerestaurant@gmail.com"; // Change this to your email address

            mail($email, $subject, $message, $headers);

            return true;
        } else {
            // If the query fails, display an error message
            echo "Error removing record: " . $conn->error;
            return false;
        }
    } else {
        // If the connection is closed, display an error message
        echo "Connection closed. Cannot perform the operation.";
        return false;
    }
}

// Fetch data from the database
$reservations = fetchDataFromDatabase();

// Check if it's a removal request or accept reservation request
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

    // Check if the "Remove" button was clicked
    if (isset($_POST['remove_reservation'])) {
        // Remove the selected reservation
        $removeId = $_POST['remove_id'];
        removeReservation($conn, $removeId);

        // Send cancellation email
        $removeEmail = $_POST['remove_email'];
        $emailSent = sendCancellationEmail($removeEmail);

        // Check if the email was sent successfully
        if ($emailSent) {
            echo "Reservation removed successfully, and cancellation email sent.";
        } else {
            echo "Reservation removed successfully, but there was an issue sending the cancellation email.";
        }

        // Fetch data again after removal
        $reservations = fetchDataFromDatabase();
    } elseif (isset($_POST['remove_all'])) {
        // Remove all reservations and reset auto-increment
        removeAllReservations($conn);

        // Fetch data again after removal
        $reservations = fetchDataFromDatabase();
    } elseif (isset($_POST['search'])) {
        // Get the search key and selected column
        $searchKey = $_POST['search_key'];
        $searchColumn = $_POST['search_column'];

        // Fetch filtered data
        $reservations = fetchFilteredData($conn, $searchKey, $searchColumn);
    } elseif (isset($_POST['refresh'])) {
        // Fetch data again after refreshing
        $reservations = fetchDataFromDatabase();
    } elseif (isset($_POST['accept_reservation'])) {
        // Accept the reservation
        $acceptId = $_POST['remove_id'];
        $acceptEmail = $_POST['remove_email'];
        acceptReservation($conn, $acceptId, $acceptEmail);

        // Fetch data again after accepting
        $reservations = fetchDataFromDatabase();
    }

    // Close the connection
    $conn->close();
}
?>

<?php
// Function to remove all reservations
function removeAllReservations($conn)
{
    $sqlDelete = "DELETE FROM today";
    
    // Execute the delete query
    $resultDelete = $conn->query($sqlDelete);

    // Check if the query was successful
    if ($resultDelete) {
        // Reset auto-increment
        $sqlResetAutoIncrement = "ALTER TABLE today AUTO_INCREMENT = 1";
        $resultResetAutoIncrement = $conn->query($sqlResetAutoIncrement);

        // Check if resetting auto-increment was successful
        if ($resultResetAutoIncrement) {
            return true;
        } else {
            // If resetting auto-increment fails, display an error message
            echo "Error resetting auto-increment: " . $conn->error;
            return false;
        }
    } else {
        // If deleting records fails, display an error message
        echo "Error deleting all records: " . $conn->error;
        return false;
    }
}
?>


<!-- HTML form -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="images/logo.png" type="image/png"> 
    <title>Today Reservations</title>
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
                <h4 class="ph4">Today Reservations</h4>
                <br>
                <h1>Today<span>Reservations</span></h1>
            </div>
        </div>

    </div>

    <br><br>
    
        <h1 class="mh1">Today Reservations</h1>
        <!-- Search Form -->
        <form class="search-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="search_key">Search Key:</label>
            <input type="text" name="search_key" id="search_key" required>
            <label for="search_column">Search Column:</label>
            <select name="search_column" id="search_column" required>
                <option value="order_id">Order ID</option>
                <option value="previous_id">Previous ID</option>
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

        <!-- Display fetched data -->
        <?php if (!empty($reservations)) : ?>
            
            <table class="reservation-table">
                <tr>
                    <th>Order ID</th>
                    <th>Previous ID</th>
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
                    <tr>
                        <td>TNO <?php echo $reservation['order_id']; ?></td>
                        <td>PNO <?php echo $reservation['previous_id']; ?></td>
                        <td><?php echo $reservation['first_name']; ?></td>
                        <td><?php echo $reservation['last_name']; ?></td>
                        <td><?php echo $reservation['email']; ?></td>
                        <td><?php echo $reservation['contact_number']; ?></td>
                        <td><?php echo $reservation['package']; ?></td>
                        <td><?php echo $reservation['date']; ?></td>
                        <td><?php echo $reservation['time']; ?></td>
                        <td><?php echo $reservation['guests']; ?></td>
                        <td>
                        <div class="button-container">
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                <input type="hidden" name="remove_id" value="<?php echo $reservation['order_id']; ?>">
                                <input type="hidden" name="remove_email" value="<?php echo $reservation['email']; ?>">
                                <button type="submit" name="accept_reservation" class="accept-btn">Accept</button>
                            </form>
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                <input type="hidden" name="remove_id" value="<?php echo $reservation['order_id']; ?>">
                                <input type="hidden" name="remove_email" value="<?php echo $reservation['email']; ?>">
                                <button type="submit" name="remove_reservation" class="remove-btn">Remove</button>
                            </form>
                        </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <!-- Button Container -->
        <div class="button-container">
            <!-- Remove All Data Button -->
            <form class="remove-all-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <button type="submit" name="remove_all">Remove All Data</button>
            </form>

            <!-- Previous Reservations Button -->
            <form class="previous-reservations-form" method='post' action='fetch.php'>
                <button type='submit' name='previous_reservations'>Early Reservations</button>
            </form>

            <!-- Today's Reservations Button -->
            <form class="today-reservations-form" method='post' action='contact_fetch.php'>
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


</body>

</html>
