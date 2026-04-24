CREATE DATABASE house_rent;
USE house_rent;

CREATE TABLE flat(
 flat_id INT PRIMARY KEY,
 area VARCHAR(50),
 asking_rent FLOAT,
 bedroom INT,
 status VARCHAR(20),
 owner_id INT
);

CREATE TABLE links(
 agreement_id INT PRIMARY KEY,
 tenant_id INT,
 flat_id INT
);

CREATE TABLE monthly_bill(
 agreement_id INT,
 billing_month VARCHAR(20),
 base_rent FLOAT,
 maintainance FLOAT,
 electricity FLOAT,
 gas FLOAT,
 payment_status VARCHAR(20),
 PRIMARY KEY(agreement_id,billing_month)
);