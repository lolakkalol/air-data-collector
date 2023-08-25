<!--
    This file is the one that is responsible for accepting POST requests 
    containing sensor data and inserting them into the correct tables in the 
    MySql server. This file first makes sure that the client is using SSL and 
    then authenticates using Basic Authentication as it was deemed secure enough 
    for this non sensetive/critical application. It then connects to the MySql 
    server, deconstructs the data in the post request and inserts it into the 
    MySql database. All SQL statements were user input is used are escaped.
-->

<?php
    // Require HTTPS connection
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        header("HTTP/1.0 404 Not Found");
        exit;
    }

    $ini_auth = parse_ini_file("../auth.ini", true);

    $hashed_password = $ini_auth['auth_hash']['hash'];

    // Use the password provided from HTTP basic auth
    if (!isset($_SERVER['PHP_AUTH_PW'])) {
        header("HTTP/1.0 404 Not Found");
        exit;
    } else {
        $user_supplied_password = $_SERVER['PHP_AUTH_PW'];

        // Check if password hashes match
        if (!($hashed_password == password_verify($user_supplied_password, $hashed_password))) { 
            header("HTTP/1.0 404 Not Found");
            exit;
        }
    }

    header('HTTP/1.0 200 OK');

    // We are authenticated
    // Open MySQL connection
    $servername    = $ini_auth['sql_auth']['servername'];
    $username      = $ini_auth['sql_auth']['username'];
    $password      = $ini_auth['sql_auth']['password'];
    $database_name = $ini_auth['sql_auth']['database_name'];
    $port          = $ini_auth['sql_auth']['port'];
 
    // Create MySQL connection fom PHP to MySQL server
    $connection = mysqli_connect(
                                $servername, $username,
                                $password, $database_name,
                                $port
                                );

    // Check connection
    if (mysqli_connect_errno()) {
       die("MySQL connection failed: " . $connection->connect_error);
    }

    if (!isset($_POST['Location'])) {
        die("No location data sent!");
    }

    // Check if location is valid
    $Location = mysqli_real_escape_string($connection, $_POST['Location']);
    $sql = "SELECT Location.Name FROM Location WHERE Name = '{$Location}'";
    $result = $connection->query($sql);

    if ($result->num_rows == 0) {
        die("Location specified not found!");
    }

    // Check if temperature was provided
    if(isset($_POST['MHZ19B-Temperature'])) {
        // Insert temperature into database
        $Temperature = mysqli_real_escape_string($connection, $_POST['MHZ19B-Temperature']);
        $sql = "INSERT INTO Temperature (Location, Celsius, Sensor) VALUES ('$Location', $Temperature, 'MHZ19B')";
        $result = $connection->query($sql);
        if ($result) {
            echo "Insertet Temperature data\n\r";
        }
    }

    // Check if CO2 was provided from MHZ19B
    if(isset($_POST['MHZ19B-CO2'])) {
        // Insert temperature into database
        $CO2 = mysqli_real_escape_string($connection, $_POST['MHZ19B-CO2']);
        $sql = "INSERT INTO CO2 (Location, PPM, Sensor) VALUES ('$Location', $CO2, 'MHZ19B')";
        $result = $connection->query($sql);
        if ($result) {
            echo "Insertet CO2 data\n\r";
        }
    }

    if (isset($_POST['PM10_STD']) || isset($_POST['PM25_STD']) || isset($_POST['PM100_STD']) || 
        isset($_POST['PM10_ATM']) || isset($_POST['PM25_ATM']) || isset($_POST['PM100_ATM']) ||
        isset($_POST['PART_03'])  || isset($_POST['PART_05'])  || isset($_POST['PART_10'])   ||
        isset($_POST['PART_25'])  || isset($_POST['PART_50'])  || isset($_POST['PART_100'])) {
        // Insert particle data into database
        $PM10_STD  = mysqli_real_escape_string($connection, $_POST['PM10_STD']);
        $PM25_STD  = mysqli_real_escape_string($connection, $_POST['PM25_STD']);
        $PM100_STD = mysqli_real_escape_string($connection, $_POST['PM100_STD']);
        $PM10_ATM  = mysqli_real_escape_string($connection, $_POST['PM10_ATM']);
        $PM25_ATM  = mysqli_real_escape_string($connection, $_POST['PM25_ATM']);
        $PM100_ATM = mysqli_real_escape_string($connection, $_POST['PM100_ATM']);
        $PART_03   = mysqli_real_escape_string($connection, $_POST['PART_03']);
        $PART_05   = mysqli_real_escape_string($connection, $_POST['PART_05']);
        $PART_10   = mysqli_real_escape_string($connection, $_POST['PART_10']);
        $PART_25   = mysqli_real_escape_string($connection, $_POST['PART_25']);
        $PART_50   = mysqli_real_escape_string($connection, $_POST['PART_50']);
        $PART_100  = mysqli_real_escape_string($connection, $_POST['PART_100']);
        
        // If a value was not provided or empty replace it with NULL
        $PM10_STD  = $PM10_STD  != "" ? $PM10_STD  : "NULL";
        $PM25_STD  = $PM25_STD  != "" ? $PM25_STD  : "NULL";
        $PM100_STD = $PM100_STD != "" ? $PM100_STD : "NULL";
        $PM10_ATM  = $PM10_ATM  != "" ? $PM10_ATM  : "NULL";
        $PM25_ATM  = $PM25_ATM  != "" ? $PM25_ATM  : "NULL";
        $PM100_ATM = $PM100_ATM != "" ? $PM100_ATM : "NULL";
        $PART_03   = $PART_03   != "" ? $PART_03   : "NULL";
        $PART_05   = $PART_05   != "" ? $PART_05   : "NULL";
        $PART_10   = $PART_10   != "" ? $PART_10   : "NULL";
        $PART_25   = $PART_25   != "" ? $PART_25   : "NULL";
        $PART_50   = $PART_50   != "" ? $PART_50   : "NULL";
        $PART_100  = $PART_100  != "" ? $PART_100  : "NULL";

        $sql = "INSERT INTO Particles (Location, PM10_STD, PM25_STD, PM100_STD, PM10_ATM, PM25_ATM, PM100_ATM, PART_03, PART_05, PART_10, PART_25, PART_50, PART_100, Sensor) VALUES ('$Location', $PM10_STD, $PM25_STD, $PM100_STD, $PM10_ATM, $PM25_ATM, $PM100_ATM, $PART_03, $PART_05, $PART_10, $PART_25, $PART_50, $PART_100, 'PMS5003T')";
        $result = $connection->query($sql);
        if ($result) {
            echo "Insertet Particle data\n\r";
        }
    }
    

?>