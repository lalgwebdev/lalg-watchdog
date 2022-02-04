<?php
use CRM_LalgWatchdog_ExtensionUtil as E;

/**********************************************************************/
/**
 * LalgWatchdog.Checkon API
 *   Checks the Scheduled Mailing and Scheduled Reminder jobs are running
 *
 * @param array There are no parameters
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_lalg_watchdog_Checkon($params) {
 	// Set initial values for response message
	$isError = false;
	$message = [];
	
	_do_watchdog_on("Send Scheduled Mailings", $isError, $message);
	_do_watchdog_on("Send Scheduled Reminders", $isError, $message);	
	
	// Format and return response
	if ($isError) {
		return civicrm_api3_create_error($message, $params, 'LalgWatchdog', 'run');
	}
	else {
		return civicrm_api3_create_success($message, $params, 'LalgWatchdog', 'run');
	}
}

// Helper function to test one scheduled job
function _do_watchdog_on($job, &$isError, &$message) {
	// Get current state of job
	$result = civicrm_api3('Job', 'get', [
	  'sequential' => 1,
	  'return' => ["id", "is_active"],
	  'name' => $job,
	]);

	if ($result['count'] != 1) {
		// If not found
		$isError = true;
		$message[] = 'ERROR';
		$message[] = "Can't find Scheduled Job: " . $job;
	}
	elseif ($result['values'][0]['is_active'] == 1) {
		// Running as expected
		$message[] = 'Information';
		$message[] = $job . " running OK.";
	}
	else {
		// Job Disabled 
		$isError = true;
		$message[] = 'ERROR';
		$message[] = $job . " is Disabled.  Attempting to restart it.";	
		
		$jid = $result['values'][0]['id'];
		$success = CRM_Core_BAO_Job::setIsActive($jid, true);
		if ($success) {
			$message[] = 'Restart reported Success.';
		}
		else {
			$message[] = 'Restart Failed.';
		}
	}
}

