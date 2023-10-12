<?php
// Function to check if username and password match
function authenticateUser($conn, $username, $password)
{
    $sql = "SELECT * FROM login WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

// Rest of your existing code

// Check if login form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Add your database connection details here
    $servername = "localhost";
    $usernameDB = "root";
    $passwordDB = "";
    $dbname = "book";

    $conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Authenticate the user
    if (authenticateUser($conn, $username, $password)) {
        // Redirect to the appropriate page based on the selected option
        $selectedOption = $_POST['redirect_option'];
        switch ($selectedOption) {
            case 'today':
                header("Location: today.php");
                exit();
            case 'previous':
                header("Location: fetch.php");
                exit();
            case 'contact':
                header("Location: contact_fetch.php");
                exit();
            default:
                // Handle other cases or redirect to a default page
                break;
        }
    } else {
        // Display an error message for invalid credentials
        echo "Invalid username or password.";
    }

    // Close the connection
    $conn->close();
}
?>


<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="images/logo.png" type="image/png"> 
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/login.css">
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
                <li><a  href="index.php">Home</a></li>
                <li><a href="bookingnew.php">Booking</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="packages.php">Packages</a></li>
                <li><a href="contact.php">Contact</a></li>

            </ul>   

            <div class="al">
                <h4 class="ph4">Admin Login</h4>
                <br>
                <h1>Admin Login</h1>
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
                    <h3>LOGIN</h3>

                    <div class="form-wrapper">
                        <input type="username" name="username" placeholder="User name" class="form-control" required>
                        <i class="zmdi zmdi-email"></i>
                    </div>

                    <div class="form-wrapper">
                        <input type="password" name="password" placeholder="Password" class="form-control" required>
                        <i class="zmdi zmdi-account"></i>
                    </div>

                    <div class="form-wrapper">
                        <select name="redirect_option" class="form-control" required>
                            <option value="" disabled selected>What Data You Want To Check</option>
                            <option value="today">Today Reservations</option>
                            <option value="previous">Early Reservations</option>
                            <option value="contact">Customer Query</option>
                        </select>
                        <i class="zmdi zmdi-caret-down" style="font-size: 17px"></i>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="login">Login
                            <i class="zmdi zmdi-arrow-right"></i>
                        </button>
                        <button type="reset">Reset
                            <i class="zmdi zmdi-arrow-right"></i>
                        </button>
                    </div>

                    <?php
                    // Display error message if authentication failed
                    if (isset($error)) {
                        echo "<p>Error: $error</p>";
                    }
                    ?>

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


</body>

</html>
<!--

    
-->