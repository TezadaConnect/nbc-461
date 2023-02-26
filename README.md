**PUP eQAR Local System Installation**

Created on 02/07/2023


**Installing PHP**

1.  Go to [**www.apachefriends.org/download.html**](http://www.apachefriends.org/download.html)
2.  Download the **8.0** version
3.  Open the file and follow the installation wizard for complete instructions


**Installing OpenVPN**

1.  Needs authorization. Please refer to the development team for the OpenVPN access documentation.


**Installing Composer**

1.  Go to getcomposer.org/download/
2.  Download the file and install
3.  Run composer -v to check if successfully installed


**Running Apache and MySQL Server in XAMPP**

1.  Open XAMPP Control Panel
2.  Find the Apache and MySQL rows and click Start as show in the image below

![Graphical user interface, application Description automatically generated](media/d0b4a45cdc573132254a1cfefb027f6e.png)

1.  Make sure Apache and MySQL turned Green as same as shown below

![Graphical user interface, application Description automatically generated](media/f31746ff4ca29cf407e026dd8819b2ac.png)


**Downloading Microsoft Drivers for PHP for SQL Server**

This is to enable connection to the external database from PUP which uses SQL server. If you already have Microsoft drivers for PHP for SQL Server found in your **php** \< **ext** folder, you may skip the steps 1-4.

1.  Go to https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server?view=sql-server-ver16
2.  Click [**Download Microsoft Drivers for PHP for SQL Server (Windows)**](https://go.microsoft.com/fwlink/?linkid=2199011) **(v. 5.10 is the latest)**
3.  **Unzip the file and copy the two (2) files: php_pdo_sqlsrv_80_ts_x64 and php_sqlsrv_80_ts_x64**
4.  **Paste the two files in your php \< ext folder**
5.  **Add the following extensions in php.ini and save:**

    **extension=pdo_sqlsrv_80_ts_x64**

    **extension=sqlsrv_80_ts_x64**


**Downloading the Repository/Project**

There are two ways to download the project/repository: Manual download or Clone using GitHub Desktop.

Manual download of repository

1.  Go to [**https://github.com/araemmanuel/pupeqar**](https://github.com/araemmanuel/pupeqar)
2.  Click the **\<\>** Code button and click **Download Zip**
3.  Unzip the file and move it in a directory of your choice

Clone the Repository

1.  Open GitHub Desktop
2.  On the top navigation, click **File** and click **Clone Repository**
3.  Click **URL** tab and paste the repository URL [**https://github.com/araemmanuel/pupeqar**](https://github.com/araemmanuel/pupeqar) as shown in the image
4.  Select the local path of your choice. An example is shown below

    ![Graphical user interface, text, application Description automatically generated](media/b3606ef430247b72305633cec2e3a808.png)

5.  Click **Clone**


**Installing Dependencies**

1.  Open the Terminal/PowerShell/Command Prompt in the **root** directory of the project/repository. You can do this by opening the root directory of the project and right click on the empty area and Click **Open Terminal**. You can also do this in a code editor and run in its Terminal, if applicable
2.  When the Terminal is open, type and run the following command: **composer install**
3.  Wait for the process to be completed


**Configuring ENV file**

1.  Needs authorization. Please refer to the development team for the ENV file configuration


**Creating Database**

This project uses MySQL in managing the database.

1.  Use the MySQL credentials based on ENV file


**Migrating the Database**

1.  Open the Terminal/PowerShell/Command Prompt in the **root** directory of the project/repository. You can do this by opening the root directory of the project and right click on the empty area and Click **Open Terminal**. You can also do this in a code editor and run in its Terminal, if applicable
2.  When the Terminal is open, type and run the following command: **php artisan migrate**


**Seeding the Database**

This project has pre-defined data items stored in the database.

1.  Open the Terminal/PowerShell/Command Prompt in the **root** directory of the project/repository. You can do this by opening the root directory of the project and right click on the empty area and Click **Open Terminal**. You can also do this in a code editor and run in its Terminal, if applicable
2.  When the Terminal is open, type and run the following command: **php artisan db:seed**
3.  Wait for the seeding to be completed


**Running the Project/Repository**

1.  Open the Terminal/PowerShell/Command Prompt in the root directory of the project/repository. You can do this by opening the root directory of the project and right click on the empty area and Click **Open Terminal**. You can also do this in a code editor and run in its Terminal, if applicable
2.  When the Terminal is open, type and run the following command: **php artisan serve**
3.  Open your browser and go to **http:://127.0.0.1:8000**

See the whole document:
https://docs.google.com/document/d/1MVcaCiUGMV34vqhuQKlaFm_ij2eihB77/edit?usp=share_link&ouid=106767701194907342086&rtpof=true&sd=true




Copyright 2022

PUP eQAR Development Team:
Mary Jean Labastida
Earl Janiel F. Compra
Kenyleen D. Pan
