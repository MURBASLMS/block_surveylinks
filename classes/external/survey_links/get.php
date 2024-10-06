<?php
// This file is part of Moodle - http://moodle.org/
//
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

namespace block_surveylinks\external\survey_links;

use block_surveylinks\explorance_api;
use block_surveylinks\guzzle_client;
use block_surveylinks\surveylink_model;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External function to get survey links from 'explorance' API.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get extends external_api {

    /**
     * The parameter definition for get method.
     *
     * @return external_function_parameters
     */
    public static function get_parameters(): external_function_parameters {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User id'),
            'courseid' => new external_value(PARAM_INT, 'Course id'),
        ]);
    }

    /**
     * Get the survey link data from API.
     *
     * @param int $userid User ID.
     * @param int $courseid Course ID.
     * @return array
     *
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function execute(int $userid, int $courseid): array {
        global $DB;
        ['userid' => $userid, 'courseid' => $courseid] = self::validate_parameters(
            self::get_parameters(),
            ['userid' => $userid, 'courseid' => $courseid]
        );

        // Close the session to prevent blocking while external API call is made.
        \core\session\manager::write_close();

        $useridnumber = $DB->get_field('user', 'idnumber', ['id' => $userid]);
        if (empty($useridnumber)) {
            throw new \moodle_exception('error:ws:usernotfound', 'block_surveylinks');
        }
        $courseidnumber = $DB->get_field('course', 'idnumber', ['id' => $courseid]);
        if (empty($courseidnumber)) {
            throw new \moodle_exception('error:ws:coursenotfound', 'block_surveylinks');
        }
        $api = new explorance_api(new guzzle_client());
        $surveylinks = $api->get_survey_links($useridnumber, $courseidnumber);

        // Filter and deserialize the survey link data.
        $records = [];
        foreach ($surveylinks as $surveylink) {
            if (self::is_survey_available($surveylink) && self::survey_matches_course($surveylink, $courseidnumber)) {
                $records[] = $surveylink->to_record();
            }
        }
        return $records;
    }

    /**
     * The return definition for get method.
     *
     * @return external_multiple_structure
     */
    public static function get_returns(): external_multiple_structure {
        return new external_multiple_structure(new external_single_structure([
            'surveyid' => new external_value(PARAM_TEXT, ''),
            'surveyname' => new external_value(PARAM_TEXT, ''),
            'surveysubjectid' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
            'surveyunitcode' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
            'surveycoursecode' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
            'surveyurl' => new external_value(PARAM_URL, ''),
            'startdate' => new external_value(PARAM_TEXT, ''),
            'enddate' => new external_value(PARAM_TEXT, ''),
            'status' => new external_value(PARAM_ALPHA, ''),
        ]));
    }

    /**
     * Check if the survey link is available and the survey is open.
     *
     * @param surveylink_model $surveylink
     * @return bool
     */
    public static function is_survey_available(surveylink_model $surveylink): bool {
        $now = time();
        $starttime = strtotime($surveylink->startdate);
        $endtime = strtotime($surveylink->enddate);

        if ($starttime < $now && $now < $endtime && $surveylink->status === surveylink_model::STATUS_OPEN) {
            return true;
        }

        return false;
    }

    /**
     * Check that the survey unit code matches the course unit code.
     *
     * @param surveylink_model $surveylink Model of survey link data.
     * @param string $courseidnumber ID Number for course.
     * @return bool
     */
    public static function survey_matches_course(surveylink_model $surveylink, $courseidnumber): bool {
        return $surveylink->surveyunitcode === $courseidnumber;
    }
}

