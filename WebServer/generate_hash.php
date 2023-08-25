<!--
    This PHP file will generate the password that can be stored in the auth.ini 
    file in the [auth_hash] -> hash field. Run this on your local computer only, 
    by running the command `php -S localhost:8000` when in this folder and 
    access the website by going to http://localhost:8000/generate_hash.php.
    Remember to save/remember the plaintext password for authentication when 
    inserting data using the insert_data.php!
-->
<?php
    $hashed_password = password_hash('MyPassword', PASSWORD_DEFAULT);

    echo $hashed_password;
    if (password_verify('MyPassword', $hashed_password)) {
        echo "\n\rPASSED!";
    }
?>
