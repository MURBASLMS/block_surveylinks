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
 *
 * @package   block_surveylinks
 * @copyright 2020 Tristan Mackay
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext('block_surveylinks/surveyApiUrl',
                           get_string('surveyApiUrl', 'block_surveylinks'),
                           get_string('surveyApiUrlexplain', 'block_surveylinks'),
                           ''));
        $settings->add(new admin_setting_configpasswordunmask('block_surveylinks/surveyApiKey',
                           get_string('surveyApiKey', 'block_surveylinks'),
                           get_string('surveyApiKeyexplain', 'block_surveylinks'),
                           ''));
}
