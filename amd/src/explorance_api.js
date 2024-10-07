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
 * Module for accessing Explorance API Data.
 *
 * @module     block_surveylinks/explorance_api
 * @class      explorance_api
 * @copyright  2021 Andrew Madden <andrewmadden@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from 'core/ajax';

/**
 * Get survey links for a user.
 *
 * @param {int} userid the user to get the surveylinks for.
 * @param {int} courseid the course to get the surveylinks for.
 *
 * @return {Promise} resolves to an object representing surveylinks model.
 */
function getSurveyLinks(userid, courseid) {

    const args = {
        'userid': userid,
        'courseid': courseid,
    };

    return Ajax.call([{
        methodname: 'block_surveylinks_get_survey_links',
        args: args
    }])[0];
}

export {getSurveyLinks};
