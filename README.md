# lalg-watchdog
Creates an API which monitors the state of the Send Mailings and Send Reminders scheduled Jobs, and re-enables them if they are disabled.  
Intended to be called directly from a cron script, though available elsewhere.
Logs results in the Drupal error log, and returns a descriptive Success or Error message.  If run from cron on CiviHosting, this message 
can be used to send an email message to administrators.
