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

/**
 * Model for Explorance survey links data.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_surveylinks;

class surveylink_model extends model {

    /** Status when survey is open. */
    const STATUS_OPEN = 'Open';

    /** Status when survey is closed. */
    const STATUS_CLOSED = 'Closed';

    /**
     * An associative array keyed by the attribute name for model with the value being a comma delimited
     * string of the path to get attribute value from in raw associative array data from API.
     *
     * For example:
     *      Raw data = '{"name":"Exam","value":50,"_embedded":{"unitOfferings": []}'
     * [
     *      'name' => 'name',
     *      'value' => 'value'
     *      'unitoffers' => '_embedded,unitOfferings'
     * ]
     */
    const ATTRIBUTE_MAP = [
        'surveyid' => 'surveyId',
        'surveyname' => 'surveyName',
        'surveysubjectid' => 'surveySubjectID',
        'surveyunitcode' => 'surveyUnitCode',
        'surveycoursecode' => 'surveyCourseCode',
        'surveyurl' => 'surveyUrl',
        'startdate' => 'startDate',
        'enddate' => 'endDate',
        'status' => 'status',
    ];
}
