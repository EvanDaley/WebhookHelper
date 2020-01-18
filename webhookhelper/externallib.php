<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External Web Service
 *
 * @package    localwebhookhelper
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot."/local/webhooks/locallib.php");
require_once(__DIR__.'/../../config.php');

class local_webhookhelper_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function setstate_parameters() {
        return new external_function_parameters(
                array(
                    'id' => new external_value(PARAM_INT, 'The id of the webhook service'),
                    'enable' => new external_value(PARAM_INT, 'The desired enabled value (0 or 1)')
                )
        );
    }

    /**
     * Returns welcome message
     * @param string $queryObject
     * @return string welcome message
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function setstate($id, $enable = 0) {
        global $USER;
        global $DB;

        $response = [
            'status' => 200
        ];

        try {
            // Perform the update.
            $record = $DB->get_record("local_webhooks_service", array("id" => $id), "*", MUST_EXIST);
            $record->enable = $enable;
            $DB->update_record("local_webhooks_service", $record, false);

            // Clear the local webhooks cache so that it sees the change.
            local_webhooks_cache_reset();
            local_webhooks_events::service_updated($record->id);

            $actionText = $enable ? 'enabled' : 'disabled';
            $response['message'] = "Successfully $actionText service $id and reset the webhook cache.";
            $response['id'] = $id;
            $response['enabled'] = $enable;
        } catch (\Throwable $e) {
            $response['status'] = 500;
            $response['message'] = $e->getMessage();
        }

        return json_encode($response);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function setstate_returns() {
        return new external_value(PARAM_RAW, 'The welcome message + user first name');
    }
}
