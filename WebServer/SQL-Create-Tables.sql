/*
    This file contains all commands used to setup the MySql server so that the, 
    webserver inserts the data into the correct tables.
*/
CREATE TABLE `Location` (
	`Name` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`Name`)
);

CREATE TABLE `Sensor` (
	`Name` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`Name`)
);

CREATE TABLE `Temperature` (
	`Location` VARCHAR(30) NOT NULL,
	`Timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`Celsius` FLOAT NOT NULL,
	`Sensor` VARCHAR(30),
    FOREIGN KEY (`Location`) REFERENCES Location(`Name`),
    FOREIGN KEY (`Sensor`) REFERENCES Sensor(`Name`)
);

CREATE TABLE `CO2` (
	`Location` VARCHAR(30) NOT NULL,
	`Timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`PPM` FLOAT NOT NULL,
	`Sensor` VARCHAR(30),
    FOREIGN KEY (`Location`) REFERENCES Location(`Name`),
    FOREIGN KEY (`Sensor`) REFERENCES Sensor(`Name`)
);

CREATE TABLE `Particles` (
	`Location` VARCHAR(30) NOT NULL,
	`Timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `PM10_STD` FLOAT,
    `PM25_STD` FLOAT,
    `PM100_STD` FLOAT,
    `PM10_ATM` FLOAT,
    `PM25_ATM` FLOAT,
    `PM100_ATM` FLOAT,
    `PART_03` FLOAT,
    `PART_05` FLOAT,
    `PART_10` FLOAT,
    `PART_25` FLOAT,
    `PART_50` FLOAT,
    `PART_100` FLOAT,
	`Sensor` VARCHAR(30),
    FOREIGN KEY (`Location`) REFERENCES Location(`Name`),
    FOREIGN KEY (`Sensor`) REFERENCES Sensor(`Name`)
);

INSERT INTO Location (Name) VALUES ('home_desktop');
INSERT INTO Sensor (Name) VALUES ('MHZ19B');
INSERT INTO Sensor (Name) VALUES ('PMS5003T');