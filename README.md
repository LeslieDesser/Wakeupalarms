<h2>FreepBX Module - Wakeup Alarms - V14.0.0.2</h2>

This is a significantly modified version of the official hotelwakeup module.

It was first modified by myself, Leslie Desser, with the assistance of Lorne Gaetz some time around 2013/4.

The modified version has been in daily use on my customised version of a 2.11 PBX in a Flash system

I have decided to re-do this enhanced version so that it will be compatible with V14 of FreePBX.

I have based it on a skeleton of the 'ringgroups' module as that had a structure that I could work with, while the existing V14 hotelwakeup module has some parts of the interface that I could not understand or extend.

<h3>Enhancements</h3>

The existing V14 hotelwakeup module is rather basic in that it allows for the creation of an alarm, and its deletion.

There is no provision for repeating alarms and the modification of future alarms.

It defines a database table for alarms at installation, but the table is never used.  Alarms are created as Asterisk .call files.

In working through the code, I have found many issues and some significant bugs.

This new version allows for one-off, daily and weekly repeating alarms with the ability to amend all alarms.

Repeating alarms can be suspended and then subsequently reinstated.

These new features are available only through the web based interface.  The phone based interface has remained functionally unchanged though the code has be reviewed and fixed where bugs were found.  

So as not to clash with the Hotelwakeup module, we have used feature code *67 rather than *68.  Dialling *67 from a phone allows the caller to create new alarms specifying a time.  If the time is in the past then the next day is assumed, else the alarm is set for the current date.  Existing alarms (for the next 24 hours or so) can be listed and individually deleted.

At the moment there is only a single global 'Settings' record, but the database tables have been designed to allow for multiple setting types - both at a global level and specific ones per extension.  This would allow for different ring and repeat patterns to be selected at the time the alarm is set. 

Our intention is to make these enhancements in the not too distant future.

<h3>Method of Operation</h3>

Future alarms are set up and saved as database records.

There is a cron job set up that runs every hour at two minute before the hour to initiate a database scan process.

This process extracts from the database all alarms that are due within the next 24-25 hours, writes an Asterisk call-file for each, and then updates the database record to move on to the next scheduled time, or deletes the db record if it is a one-off alarm.

The GUI shows scheduled alarms for both the call files due within 24-25 hours and the future ones in the database.

All alarms - whether in the database or as a call file can be modified or deleted.  Database records can be suspended and will then not be triggered until the suspension is removed.

<h3>Help wanted</h3>

At the moment the bulk of the functionality is only available from the web based interface, which requires admin login to the system.

For maximum benefit, the module needs to be ported to the User Control Panel.

<h3>Install Instructions</h3>

Click on the green 'Clone or download' button and select Download ZIP and save the file.

In Module Admin click Upload Modules > select Upload (From Hard Disk) > Choose file > Upload (From Hard Disk) and follow instructions.

The module appears under Applications > Wakeup Alarms.

Use the Info button for usage instructions.
