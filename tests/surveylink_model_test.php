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
 * Test the surveylink model.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_surveylinks;

/**
 * Test the surveylink model.
 */
class surveylink_model_test extends \advanced_testcase {

    /**
     * This method runs before every test.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test creating model with valid data record.
     *
     * @covers \block_surveylinks\surveylink_model::create_model_with_valid_data
     */
    public function test_create_model_with_valid_data() {
        $surveylink = new \block_surveylinks\surveylink_model([
            'surveyId' => '1324',
            'surveyName' => 'Test Survey',
            'surveySubjectID' => 'TEST123',
            'surveyUnitCode' => 'UNIT123',
            'surveyUrl' => 'https://www.example.com/survey/UNIT123',
            'startDate' => date('c', time() - 1000),
            'endDate' => date('c', time() + 1000),
            'status' => 'Open',
        ]);
        $this->assertInstanceOf(\block_surveylinks\surveylink_model::class, $surveylink);
        $this->assertEquals('1324', $surveylink->surveyid);
        $this->assertEquals('Test Survey', $surveylink->surveyname);
        $this->assertEquals('TEST123', $surveylink->surveysubjectid);
        $this->assertEquals('UNIT123', $surveylink->surveyunitcode);
        $this->assertEquals('https://www.example.com/survey/UNIT123', $surveylink->surveyurl);
        $this->assertEquals('Open', $surveylink->status);
    }

    /**
     * Test creating model with empty data.
     *
     * @covers \block_surveylinks\surveylink_model::create_model_with_empty_data
     */
    public function test_create_model_with_no_data() {
        $surveylink = new \block_surveylinks\surveylink_model([]);
        $this->assertInstanceOf(\block_surveylinks\surveylink_model::class, $surveylink);
        $this->assertNull($surveylink->surveyid);
        $this->assertNull($surveylink->surveyname);
    }

    /**
     * Test creating model with data not expected by model.
     *
     * @covers \block_surveylinks\surveylink_model::create_model_with_invalid_data
     */
    public function test_create_model_with_invalid_data() {
        $surveylink = new \block_surveylinks\surveylink_model([
            'id' => '123',
            'name' => 'Test Name',
        ]);
        $this->assertInstanceOf(\block_surveylinks\surveylink_model::class, $surveylink);
        $this->assertNull($surveylink->surveyid);
        $this->assertNull($surveylink->surveyname);
    }
}
