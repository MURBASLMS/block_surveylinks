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
 * Test the explorance api.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_surveylinks\explorance_api;
use block_surveylinks\tests\mock_client;

defined('MOODLE_INTERNAL') || die();

class block_surveylinks_explorance_api_testcase extends advanced_testcase {
    
    /**
     * This method runs before every test.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    /**
     * Test getting a list of survelink models.
     */
    public function test_get_survey_links() {
        set_config('apibaseuri', 'https://www.example.com', 'block_surveylinks');
        set_config('apisecret', 'secret', 'block_surveylinks');
        $api = new explorance_api(new mock_client());
        $surveylinks = $api->get_survey_links('123', '123');
        $expected = [
            new \block_surveylinks\surveylink_model([
                "surveyUrl" => "https://www.example.com/survey?id=12345AB",
                "startDate" => "2021-04-13T14:00:00.0000000+00:00",
                "endDate" => "2021-07-02T16:59:00.0000000+00:00",
                "status" => "Open",
                "surveyId" => "12345AB",
                "surveyName" => "LMS Test Project",
                "surveySubjectId" => "123456",
                "surveyUnitCode" => "ABC123",
                "surveyCourseCode" => "A1234",
            ])
        ];
        $this->assertEquals($expected, $surveylinks);
    }

    /**
     * Test exception thrown if api base uri is not set.
     */
    public function test_invalid_base_uri_set() {
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(get_string('error:api:nobaseuri', 'block_surveylinks'));
        $api = new explorance_api(new mock_client());
    }

    /**
     * Test exception thrown if api secret is not set.
     */
    public function test_invalid_credentials_set() {
        set_config('apibaseuri', 'https://www.example.com', 'block_surveylinks');
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(get_string('error:api:credentials', 'block_surveylinks'));
        $api = new explorance_api(new mock_client());
    }

    /**
     * Test extracting a subset of data from multidimensional array.
     */
    public function test_search_multidimensional_array() {
        $data = [
            '1' => [
                '1-1' => [
                    '1-1-1' => 'Hello World!',
                    '1-1-2' => 'Deeper 1-1-2',
                ],
                '1-2' => [
                    '1-2-1' => 1337,
                    '1-2-2'  => new stdClass(),
                ]
            ],
            2 => [
                '2-1' => ['No key'],
            ],
            [
                '1-1' => 'Sibling 1-1 should not be found',
                '1-1-2' => 'Shallow 1-1-2',
            ]
        ];

        $this->assertEquals('Hello World!', explorance_api::get_subset_from_multidimensional_array($data, '1-1-1'));
        $this->assertEquals(1337, explorance_api::get_subset_from_multidimensional_array($data, '1-2-1'));
        $this->assertCount(2, explorance_api::get_subset_from_multidimensional_array($data, '1-1'));
        $this->assertCount(2, explorance_api::get_subset_from_multidimensional_array($data, '1-2'));
        $this->assertEquals($data['1']['1-2'], explorance_api::get_subset_from_multidimensional_array($data, '1-2'));
        // If there are multiple possible results, the most shallow should be returned.
        $this->assertEquals('Shallow 1-1-2', explorance_api::get_subset_from_multidimensional_array($data, '1-1-2'));
        // If there are multiple possible results at same depth, the first found should be returned.
        $this->assertEquals($data['1']['1-1'], explorance_api::get_subset_from_multidimensional_array($data, '1-1'));
        $this->assertNotEquals('Sibling 1-1 should not be found', explorance_api::get_subset_from_multidimensional_array($data, '1-1'));
        // Returned data should maintain type.
        $this->assertInstanceOf(stdClass::class, explorance_api::get_subset_from_multidimensional_array($data, '1-2-2'));
        $this->assertIsArray(explorance_api::get_subset_from_multidimensional_array($data, '1-2'));
        // Non-string keys should also be matched.
        $this->assertCount(1, explorance_api::get_subset_from_multidimensional_array($data, 2));
        $this->assertEquals($data[2], explorance_api::get_subset_from_multidimensional_array($data, 2));
        $this->assertCount(1, explorance_api::get_subset_from_multidimensional_array($data, '2-1'));
        $this->assertEquals($data[2]['2-1'], explorance_api::get_subset_from_multidimensional_array($data, '2-1'));
        // If key to find is not found, should return false.
        $this->assertFalse(explorance_api::get_subset_from_multidimensional_array($data, '3-1'));
    }
}
