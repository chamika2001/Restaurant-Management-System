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

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $message = $_POST["message"];

    // Insert the submission data into the database using a prepared statement
    $sql = "INSERT INTO submissions (name, email, message) VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        // Send thank-you email
        $to = $email;
        $subject = "Thank You for Your Submission";
        $thankYouMessage = "Dear $name,\n\nThank you for your submission. We have received your message and will address it as soon as possible.\n\nBest regards,\nThe Booking Team";

        // Additional headers
        $headers = "From: opacarophilerestaurant@gmail.com"; // Replace with your email address

        // Send email
        mail($to, $subject, $thankYouMessage, $headers);

        header("Location: thankyoucon.php");
    } else {
        echo "Error: " . $sql . "<br>" . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>


<!-- Your HTML form ... -->


<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/contact.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    
    <!-- Font Awesome Cdn -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <!-- Font Awesome Cdn -->
    <link rel="icon" href="images/logo.png" type="image/png"> 
    <title>Contact Us</title>
</head>

<body>

    <div class="wrapper">
        <div class="main-container">
            <video autoplay muted loop class="background-video">
                <source src="images/vid.mp4" type="video/mp4">
            </video>
            <div class="overlay"></div>
        </div>
        <div class="navbar"> <!--navbar-->

            <img class="logo" src="images/logo.png">
            <ul>
                <li><a  href="index.php">Home</a></li>
                <li><a href="bookingnew.php">Booking</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="packages.php">Packages</a></li>
                <li><a class="active" href="contact.php">Contact</a></li>

            </ul>    

            <div class="al">
                <h4 class="ph4"> <span class="sp1">C O N T A C  </span><span class="sp2">T</span><span class="sp1">U S</span></h4>
                <br>
                <h1>Contact Us</h1>
            </div>
        </div>
    
    </div>
    <br><br>
    <div>
        
        <h1 class="mh3">Contact US</h1>
    </div>
    <br><br><br><br>
    <div class="container">
        <div class="content">
          <div class="left-side">
            <div class="address details">
              <i class="fas fa-map-marker-alt"></i>
              <div class="topic">Address</div>
              <div class="text-one">No.17, Union Place<br>,Colombo 02</div>
            </div>
            <div class="phone details">
              <i class="fas fa-phone-alt"></i>
              <div class="topic">Phone</div>
              <div class="text-one">+94 112 123456</div>
            </div>
            <div class="email details">
              <i class="fas fa-envelope"></i>
              <div class="topic">Email</div>
              <div class="text-one">opacarophilerestaurant@gmail.com</div>
            </div>
          </div>
          <div class="right-side">
            <div class="topic-text">Send us a message</div>
            <p><br><b>If you have any work from me or any types of quries related to my tutorial, you can send me message from here . It's my pleasure to help you.</b></p>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
              <div class="input-box">
                  <input type="text" name="name" placeholder="Enter your name" required>
              </div>
              <div class="input-box">
                  <input type="email" name="email" placeholder="Enter your email" required>
              </div>
              <div class="input-box message-box">
                  <textarea name="message" placeholder="Enter your message" required></textarea>
              </div>
              <div class="button">
                  <input type="submit" value="Send Now">
              </div>
          </form>
        </div>
        </div>
      </div>
    <br>
    <footer class="footer">
        <div class="containers">
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
<!--kjk;fdk;dfl'-->
