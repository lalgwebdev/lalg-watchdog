<?php
use CRM_LalgWatchdog_ExtensionUtil as E;

/**********************************************************************/
/**
 * LalgWatchdog.Checkoff API
 *   Checks the Scheduled Mailing and Scheduled Reminder jobs are disabled
 *
 * @param array There are no parameters
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_lalg_watchdog_Checkoff($params) {
 	// Set initial values for response message
	$isError = false;
	$message = [];
	
	_do_watchdog_off("Send Scheduled Mailings", $isError, $message);
	_do_watchdog_off("Send Scheduled Reminders", $isError, $message);	
	
	// Format and return response
	if ($isError) {
		return civicrm_api3_create_error($message, $params, 'LalgWatchdog', 'run');
	}
	else {
		return civicrm_api3_create_success($message, $params, 'LalgWatchdog', 'run');
	}
} 

// Helper function to test one scheduled job
function _do_watchdog_off($job, &$isError, &$message) {
	// Get current state of job
	$result = civicrm_api3('Job', 'get', [
	  'sequential' => 1,
	  'return' => ["id", "is_active"],
	  'name' => $job,
	]);

	if ($result['count'] != 1) {
		// If not found
		$isError = true;
		$message[] = getcwd() . "  ERROR. Can't find Scheduled Job: " . $job;
	}
	elseif ($result['values'][0]['is_active'] == 0) {
		// Disabled as expected
		$message[] = getcwd() . "  " . $job . " is disabled OK.";
	}
	else {
		// Job running 
		$jid = $result['values'][0]['id'];
		$success = CRM_Core_BAO_Job::setIsActive($jid, false);
		if ($success) {
			$message[] = getcwd() . '  WARNING: ' . $job . ' is running. Disabled OK.';
		}
		else {
			$message[] = getcwd() . '  ERROR: ' . $job . ' is running. Disable Failed.';
		}
	}
}	
