CREATE DATABASE IF NOT EXISTS theAgoraDB DEFAULT CHARSET = utf8;
USE theAgoraDB;
/*Table can store more information metrics about the business later*/
CREATE TABLE theAgoraDB.business (
	businessId INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
	locationBased VARCHAR(50) NOT NULL,
    logo VARCHAR(20) NULL,
    email VARCHAR(40) NULL,
    mobile INT NULL,
    verified BOOLEAN NOT NULL,
    suspended BOOLEAN NOT NULL
) ENGINE=INNODB;

INSERT INTO theAgoraDB.business (`businessId`, `name`, `locationBased`, `logo`, `email`, `mobile`, `verified`, `suspended`) VALUES
("1", "The Warehouse","Auckland", "thewarehouse.jpg", "warehouse@gmail.com","111","1", "0"),
("2", "Jaycar","Wellington", "jaycar.jpg","Hammett@xtra.com","12345","1", "0"),
("3", "ARA","Christchurch", "", "Claudia@hotmail.com","6666666","1", "0");

CREATE TABLE theAgoraDB.users (
	businessId INT NOT NULL,
    username VARCHAR(50) PRIMARY KEY NOT NULL,
    passwordHash VARCHAR(60) NOT NULL,
	firstName VARCHAR(30) NULL,
	lastName VARCHAR(30) NULL,
	FOREIGN KEY (businessId) REFERENCES business(businessId)
) ENGINE=INNODB;

/*NOTE: Passwords are prehashed in php*/
INSERT INTO theAgoraDB.users (`businessId`,`username`,`passwordHash`,`firstName`,`lastName`) VALUES
("1", "username","$2y$10$oew6gSUtwFIqHdo9yAHJ7uLML93rPIlZ08bYmfuMDlelH3uPhVqYi","Clark","Kent"),
("2", "user","$2y$10$CeCxU/.5cl9S2AZexfW/v.4E3/YG.TISEMxziP0iEklus0NL9aane","Bob","Baker"),
("3", "admin","$2y$10$3skK1l67QMotW/8.5uKWTu3ATQTYbmO2Fr3xPJoLiMOkCfJTi1KiG","Simon","Ackermann");


CREATE TABLE theAgoraDB.listings (
	referenceCode VARCHAR(10) PRIMARY KEY,
	seller VARCHAR(50) NOT NULL,
    datePosted DATE NOT NULL,
    title VARCHAR(50) NOT NULL,
    itemDescription VARCHAR(500) NULL,
	price FLOAT NOT NULL,
    rate VARCHAR(10) NOT NULL,
    itemImage VARCHAR(50) NULL,
    hashtags VARCHAR(100) NULL,
    FOREIGN KEY (seller) REFERENCES users(username)
) ENGINE=INNODB;

INSERT INTO theAgoraDB.listings (`referenceCode`, `seller`, `datePosted`, `title`, `itemDescription`, `price`, `rate`,`itemImage`,`hashtags`) VALUES
("AAAAAAAAAA", "username", "2021-06-06", "Apples", "fresh", "6.00", "kg", "apples1.jpg", "#fresh, #apple"),
("AAAAAAAAAB", "user", "2021-06-06", "Watermelons", "fresh", "2", "each", "watermelons.jpg", "#fresh, #watermelon"),
("AAAAAAAAAC", "admin", "2021-06-06", "Apples", "fresh", "1.00", "kg", "apples2.jpg", "#apple"),
("AAAAAAAAAD", "user", "2021-06-06", "Pears", "fresh", "9999.00", "kg", "pears.jpg", ""),
("AAAAAAAAAE", "username", "2021-06-06", "Pears", "fresh", "7", "g", "pears2.jpg", "#pear");

CREATE TABLE theAgoraDB.sold (
	referenceCode VARCHAR(10) PRIMARY KEY,
    seller VARCHAR(50) NOT NULL,
    buyer VARCHAR(50) NOT NULL,
    dateSold DATETIME NOT NULL,
    FOREIGN KEY (referenceCode) REFERENCES listings(referenceCode),
    FOREIGN KEY (seller) REFERENCES users(username),
    FOREIGN KEY (buyer) REFERENCES users(username)
) ENGINE=INNODB;

CREATE TABLE theAgoraDB.contacts (
    user VARCHAR(50) NOT NULL,
    contact VARCHAR(50) NOT NULL,
    FOREIGN KEY (user) REFERENCES users(username),
    FOREIGN KEY (contact) REFERENCES users(username),
    PRIMARY KEY (user, contact)
) ENGINE=INNODB;

