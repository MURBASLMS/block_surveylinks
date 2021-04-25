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
 * Test the plugin privacy provider implementation.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_surveylinks_provider_testcase extends advanced_testcase {
    
    /**
     * This method runs before every test.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    /**
     * Test the provider get_message implementation.
     */
    public function test_get_null_provider_message() {
        $this->assertEquals('privacy:metadata', \block_surveylinks\privacy\provider::get_reason());
    }
}
