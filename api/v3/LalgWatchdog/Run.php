<?php
use CRM_LalgWatchdog_ExtensionUtil as E;

/**********************************************************************/
/**
 * LalgWatchdog.Run API
 *
 * @param array There are no parameters
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_lalg_watchdog_Run($params) {
	// Set initial values for response message
	$isError = false;
	$message = [];
	
	_do_watchdog("Send Scheduled Mailings", $isError, $message);
	_do_watchdog("Send Scheduled Reminders", $isError, $message);	
	
	// Format and return response
	if ($isError) {
		return civicrm_api3_create_error($message, $params, 'LalgWatchdog', 'run');
	}
	else {
		return civicrm_api3_create_success($message, $params, 'LalgWatchdog', 'run');
	}
}

// Helper function to test one scheduled job
function _do_watchdog($job, &$isError, &$message) {
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
		// Running OK
		$message[] = 'Information';
		$message[] = $job . " running OK.";
	}
	else {
		// Job not running (Disabled)
		$isError = true;
		$message[] = 'ERROR';
		$message[] = $job . " is not running (Disabled).  Attempting a restart.";	
		
		$jid = $result['values'][0]['id'];
		$success = CRM_Core_BAO_Job::setIsActive($jid, true);
		if ($success) {
			$message[] = 'Restart reported success.';
		}
		else {
			$message[] = 'Restart Failed.';
		}
	}
}	




/********************************************************************/
// Example created by Civix.  Kept for reference.
// /**
 // * LalgWatchdog.Run API
 // *
 // * @param array $params
 // * @return array API result descriptor
 // * @see civicrm_api3_create_success
 // * @see civicrm_api3_create_error
 // * @throws API_Exception
 // */
// function civicrm_api3_lalg_watchdog_Run($params) {
  // if (array_key_exists('magicword', $params) && $params['magicword'] == 'sesame') {
    // $returnValues = array(
      // // OK, return several data rows
      // 12 => array('id' => 12, 'name' => 'Twelve'),
      // 34 => array('id' => 34, 'name' => 'Thirty four'),
      // 56 => array('id' => 56, 'name' => 'Fifty six'),
    // );
    // // ALTERNATIVE: $returnValues = array(); // OK, success
    // // ALTERNATIVE: $returnValues = array("Some value"); // OK, return a single value

    // // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
    // return civicrm_api3_create_success($returnValues, $params, 'NewEntity', 'NewAction');
  // }
  // else {
    // throw new API_Exception(/*errorMessage*/ 'Everyone knows that the magicword is "sesame"', /*errorCode*/ 1234);
  // }
// }
