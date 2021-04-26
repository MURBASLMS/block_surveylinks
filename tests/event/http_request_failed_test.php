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
 * This is a Moodle file.
 *
 * This is a longer description of the file.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_surveylinks\event\http_request_failed;

defined('MOODLE_INTERNAL') || die();

class block_surveylinks_http_request_failed_testcase extends advanced_testcase {
    
    /**
     * This method runs before every test.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    /**
     * Test the event was triggered.
     */
    public function test_event_is_triggered() {
        $sink = $this->redirectEvents();
        http_request_failed::create([
            'other' => [
                'reason' => 'Test error message.',
            ]
        ])->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf(http_request_failed::class, $event);
        $this->assertEquals('Test error message.', $event->other['reason']);
    }
}
