# Webserver files
This folder contains all files related to or required to be on a webserver for everything to work.
Currently these files contain:
- SQL commands to create the requred tables in a MySql database (SQL-Create-Tables.sql)
- A test python script to see if the insert_data.php file works when hosted on a webserver. (Test-PHP-Webserver.py.example)
- A file that inserts data from a authenticated POST request into a MySql database. (insert_data.php)
- A file that is able to generate a password hash of the password required when authenticating over the POST request, should only be run offline to aquire hash. (generate_hash.php)
- A authentication example file that contains the MySql credentials and the hashed password for the POST request authentication.
