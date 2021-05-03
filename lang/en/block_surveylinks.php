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
 * Completion Progress block settings
 *
 * @package   block_surveylinks
 * @copyright 2020 Tristan Mackay
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Survey Links';
$string['block_surveylinks'] = 'Survey Links';

// Capabilities.
$string['block_surveylinks:addinstance'] = 'Add a new Survey Links block';
$string['block_surveylinks:myaddinstance'] = 'Add a new Survey Links block to the Moodle page';
$string['block_surveylinks:viewmysurveylinks'] = 'View own survey links fetched from remote API.';

// Privacy.
$string['privacy:metadata'] = 'Does not store any individual user data.';

// Settings.
$string['apibaseuri'] = "Base API URI";
$string['apibaseuri_desc'] = "The base URI used to create HTTP requests to API.";
$string['apisecret'] = "API Secret";
$string['apisecret_desc'] = "The client API secret to be sent in the header of HTTP requests to api.";

// Block config.
$string['blockconfig:extratext'] = 'Extra text';
$string['blockconfig:header'] = 'Display configuration';
$string['blockconfig:linktext'] = 'Link text';
$string['blockconfig:logo'] = 'Survey logo file';
$string['blockconfig:resetdefault'] = 'Reset to defaults';
$string['blockconfig:resetdefault_help'] = 'Clicking the reset button will set all display fields to their default value.';

// View.
$string['view:loading'] = 'Loading...';

// Event.
$string['event:httprequestfailed'] = 'HTTP request failed';

// Exceptions.
$string['error:api:credentials'] = 'Invalid API secret has been set. See plugin settings.';
$string['error:api:nobaseuri'] = 'Invalid API base URI has been set. See plugin settings.';
$string['error:http:get'] = 'HTTP GET request failed';
$string['error:ws:usernotfound'] = 'User with a valid ID number could not be found.';
$string['error:ws:coursenotfound'] = 'Course with a valid ID number could not be found.';
