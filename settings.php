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
 * Plugin settings.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_surveylinks/apibaseuri',
            get_string('apibaseuri', 'block_surveylinks'),
            get_string('apibaseuri_desc', 'block_surveylinks'),
            ''));

    $settings->add(new admin_setting_configpasswordunmask('block_surveylinks/apisecret',
            get_string('apisecret', 'block_surveylinks'),
            get_string('apisecret_desc', 'block_surveylinks'),
            ''));

    // Display defaults.
    $settings->add(new admin_setting_heading('block_surveylinks/displaydefaults',
            get_string('displaydefaults', 'block_surveylinks'),
            get_string('displaydefaults_desc', 'block_surveylinks')));

    $settings->add(new admin_setting_configstoredfile('block_surveylinks/defaultlogo',
            get_string('defaultlogo', 'block_surveylinks'),
            get_string('defaultlogo_desc', 'block_surveylinks'),
            'logo'));

    $settings->add(new admin_setting_configtextarea('block_surveylinks/defaultlinktext',
            get_string('defaultlinktext', 'block_surveylinks'),
            get_string('defaultlinktext_desc', 'block_surveylinks'),
            ''));

    $settings->add(new admin_setting_configtextarea('block_surveylinks/defaultextratext',
            get_string('defaultextratext', 'block_surveylinks'),
            get_string('defaultextratext_desc', 'block_surveylinks'),
            ''));
}
