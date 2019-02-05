# paytm_payment_verification_using_mail
An unofficial Paytm script that verfies user PayTM transaction id and paid amount by comparing with mail to allow user registration. Here is the [DEMO](https://paytm.nktutorial.com/)

## Getting Started
download and upload your file on hosting server or localhost then edit the all necessary information like gmail credential and database detail in data.php file and set the minimum amount to receive amounts from user.

### Installing

A step by step information that you needed to add to run this script
1. Gmail credentials
2. Database details
3. The minimum amount that the user must have paid to register

## How Does This Work?
When a transaction is made both the sender and the receiver receives an email which has the same transaction id. This script takes the transaction id from the participant and verifies it from the receiver's inbox. If the amount and transaction id matches the user's name and registration number(just another sample field) gets registered in the database.

### Gmail Not Connecting?
if your gmail is not connecting with the script after entering the true credential then just go to this [link](https://myaccount.google.com/u/0/security) and allow access to less secure app after that you will be able to connect successfully.
