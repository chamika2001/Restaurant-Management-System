<?php
// index.php

// Function to check if the same date and time have been entered three times in the database
function checkSameDateTime($conn, $date, $time, $table)
{
    $sql = "SELECT COUNT(*) AS count FROM $table WHERE date = '$date' AND time = '$time'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'] >= 3;
}

// Function to get the list of disabled time options
function getDisabledTimes($conn, $date, $table)
{
    $disabledTimes = [];
    $sql = "SELECT time FROM $table WHERE date = '$date' GROUP BY time HAVING COUNT(*) >= 3";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $disabledTimes[] = $row['time'];
    }
    return $disabledTimes;
}


// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "book";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $contact_number = $_POST["contact_number"];
    $package = $_POST["package"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $guests = $_POST["guests"];

    // Determine the target table based on the booking date
    $targetTable = (strtotime($date) == strtotime(date('Y-m-d'))) ? 'today' : 'reservations';

    // Check if the same date and time have been entered three times in the database
    $sameDateTimeReservations = checkSameDateTime($conn, $date, $time, 'reservations');
    $sameDateTimeToday = checkSameDateTime($conn, $date, $time, 'today');

    if ($sameDateTimeReservations || $sameDateTimeToday) {
        // Handle the case when the same date and time have already been reserved three times
        // You can show an error message or take any other action as needed.
        echo "<script>alert('Already reserved this time! Please select another date or time.');</script>";
    } else {

        // Insert the reservation data into the appropriate table
        $sql = "INSERT INTO $targetTable (first_name, last_name, email, contact_number, package, date, time, guests) VALUES ('$first_name', '$last_name', '$email', '$contact_number', '$package', '$date', '$time', '$guests')";

        if ($conn->query($sql) === TRUE) {
            // Get the auto-incremented order ID
            $lastInsertedID = $conn->insert_id;

            // Update the order_id with the auto-incremented ID
            $prefix = ($targetTable == 'today') ? 'TNO' : 'PNO';
            $updateSql = "UPDATE $targetTable SET order_id = '$formattedOrderID' WHERE order_id = $lastInsertedID AND order_id IS NULL";
            $conn->query($updateSql);

            // Send email confirmation to the customer
            $to = $email;
            $subject = "Reservation Confirmation";
            $message = "Dear $first_name $last_name,\n\nThank you for your reservation at Our Restaurant.\n\nDetails:\nOrder ID: $prefix $lastInsertedID\nDate: $date\nTime: $time\nGuests: $guests\n\nWe look forward to serving you!\n\nBest regards,\nThe Our Restaurant Team";
            $headers = "From: opacarophilerestaurant@gmail.com";

            mail($to, $subject, $message, $headers);

            // Redirect to the thank you page
            header("Location: thankyou.php");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

$conn->close();
?>

<!-- HTML form -->

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="images/logo.png" type="image/png"> 
    <title>Booking</title>
    <link rel="stylesheet" href="css/bookingnew.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

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
                <li><a href="index.php">Home</a></li>
                <li><a class="active" href="bookingnew.php">Booking</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="packages.php">Packages</a></li>
                <li><a href="contact.php">Contact</a></li>

            </ul>    
            <div class="al">
                <h4 class="ph4">B O O K I N G</h4>
                <br>
                <h1>Booking</h1>
            </div>
        </div>
    
    </div>
       
    <div class="bd">

		<div class="wrapper" >
            
			<div class="inner">
				<div class="image-holder">
					<img src="images/about-1.jpg" alt="">
				</div>
     
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <h3>Restaurant Reservation</h3>
                <div class="form-group">
                    <input type="text" name="first_name" placeholder="First Name" class="form-control" required>
                    <input type="text" name="last_name" placeholder="Last Name" class="form-control" required>
                </div>

                <div class="form-wrapper">
                    <input type="email" name="email" placeholder="Email Address" class="form-control" required>
                    <i class="zmdi zmdi-email"></i>
                </div>
                <div class="form-wrapper">
                    <input type="tel" name="contact_number" placeholder="Contact Number" class="form-control" required>
                    <i class="zmdi zmdi-account"></i>
                </div>
                <div class="form-wrapper">
                    <select name="package" class="form-control" required>
                        <option value="" disabled selected>Package</option>
                        <option value="rom">Romantic Date</option>
                        <option value="friends">Chilling with Friends</option>
                        <option value="fam">Family Gathering</option>
                        <option value="party">Surprise Parties</option>
                        <option value="Office">Office Parties</option>
                        <option value="other">Other Celebrations</option>
                    </select>
                    <i class="zmdi zmdi-caret-down" style="font-size: 17px"></i>
                </div>
                <div class="form-wrapper">
                    <input type="date" name="date" placeholder="Date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-wrapper">
                    <select name="time" id="timeSelect" class="form-control" required>
                        <option value="" disabled selected>Time</option>
                        <option value="9-11">09.00 AM - 11.00 AM</option>
                        <option value="11-1">11.00 AM - 01.00 PM</option>
                        <option value="1-3">01.00 PM - 03.00 PM</option>
                        <option value="3-5">03.00 PM - 05.00 PM</option>
                        <option value="5-7">05.00 PM - 07.00 PM</option>
                        <option value="7-9">07.00 PM - 09.00 PM</option>
                    </select>
                    <i class="zmdi zmdi-caret-down" style="font-size: 17px"></i>
                </div>
                <div class="form-wrapper">
                    <input type="number" name="guests" placeholder="Number of Guests" class="form-control" required min="1" max="10">
                </div>
                <button type="submit">Booking <i class="zmdi zmdi-arrow-right"></i></button>
            </form>

   


			</div>
		</div>
		
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
    </div>

    <script>
       document.addEventListener("DOMContentLoaded", function () {
    const timeSelect = document.getElementById("timeSelect");
    const timeOptions = timeSelect.getElementsByTagName("option");
    const selectedTime = timeSelect.value;

    // Check if the same date and time have been entered five times
    <?php
    if ($sameDateTime) {
        echo "const sameDateTime = true;";
    } else {
        echo "const sameDateTime = false;";
    }
    ?>

    if (sameDateTime) {
        // Disable the selected time option
        for (let i = 0; i < timeOptions.length; i++) {
            if (timeOptions[i].value === selectedTime) {
                timeOptions[i].disabled = true;
                break;
            }
        }
    }

    // Disable other times that have reached the limit
    <?php
    $disabledTimes = getDisabledTimes($conn, $date);
    echo "const disabledTimes = " . json_encode($disabledTimes) . ";";
    ?>

    disabledTimes.forEach(function (disabledTime) {
        for (let i = 0; i < timeOptions.length; i++) {
            if (timeOptions[i].value === disabledTime) {
                timeOptions[i].disabled = true;
                break;
            }
        }
    });
});

    </script>

</body>

</html>
