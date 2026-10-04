<div align="center">

  <h1>UMS - Utility Management System</h1>

  <p>
    <b>Streamlining utility bill management and accounting for everyone.</b>
  </p>

  <h4>
    <a href="#about">About</a> •
    <a href="#features">Features</a> •
    <a href="#tech-stack">Tech Stack</a> •
    <a href="#initial-setup">Initial Setup</a> •
    <a href="#preview">Preview</a>
  </h4>
</div>

<br />

## About

**UMS (Utility Management System)** is a comprehensive, web-based application designed to bridge the gap between utility service providers and consumers.

Managing utility bills and accounting can be complex. UMS simplifies this by offering a centralized platform that handles everything from billing cycles to user payments. Built using a robust and widely-accessible technology stack, this system ensures a seamless efficient experience for all stakeholders.


## Features

* **Multi-Role Access:** Dedicated portals for Admins, Service Providers, and End-Users.
* **Smart Billing:** Automated bill generation and tracking.
* **Dashboard Analytics:** Real-time overview of usage and payment status.
* **Complaint Management:** Allows for sending complaints regarding utility break downs and other disruptions.
* **Automatic Interest Calculations:** Allows for applying and tracking late fees.
* **Triggers:** SQL triggers allow higher level staff to make changes to a large amount of data at once.


## Tech Stack

* CSS
* PHP
* SQL
* HTML


## Initial Setup

* **Prerequisites:** Microsoft SQL Server, IDE, XAMPP, [ODBC Drivers](https://learn.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server?view=sql-server-ver17)
* Put the relevant .dll files at **xampp/php/ext** for the website to access the database.
* Put the project folder at **xampp/htdocs**. Only the **public/** folder is meant to be served.
* **Import the backup file** (`database/UtilitySys_New.bak`) using SQL Server Management Studio.
* Copy the server name from the connection menu in Microsoft SQL Server and replace the **servername** at **src/config/db.php**.
* Access the website using the following link:

  ```bash
  localhost/UMS-Utility-Management-System-/public/
  ```


## Preview

![UMS preview](docs/brag-demo.gif)
