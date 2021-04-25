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
 * Test the get_survey_links external function.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_surveylinks_get_survey_links_testcase extends advanced_testcase {

    /**
     * Run before every test.
     */
    protected function setUp() {
        $this->resetAfterTest();
    }

    /**
     * Check get if user does not have a idnumber.
     */
    public function test_get_surveylinks_user_not_found() {
        $course = $this->getDataGenerator()->create_course(['idnumber' => 'course123']);
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(get_string('error:ws:usernotfound', 'block_surveylinks'));
        \block_surveylinks\external\survey_links\get::get(1234, $course->id);
    }

    /**
     * Check get if course does not have a idnumber.
     */
    public function test_get_surveylinks_course_not_found() {
        $user = $this->getDataGenerator()->create_user(['idnumber' => 'user123']);
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(get_string('error:ws:coursenotfound', 'block_surveylinks'));
        \block_surveylinks\external\survey_links\get::get($user->id, 1234);
    }

    /**
     * Test survey is available.
     */
    public function test_survey_is_available() {
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
        $result = \block_surveylinks\external\survey_links\get::is_survey_available($surveylink);
        $this->assertTrue($result);
    }

    /**
     * Test that survey is not available.
     *
     * @dataProvider closed_survey_provider
     */
    public function test_survey_is_not_available(\block_surveylinks\surveylink_model $surveylink) {
        $result = \block_surveylinks\external\survey_links\get::is_survey_available($surveylink);
        $this->assertFalse($result);
    }

    /**
     * Test unit code from survey matches Moodle course.
     */
    public function test_survey_matches_course() {
        $course = $this->getDataGenerator()->create_course(['idnumber' => 'UNIT123']);
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
        $result = \block_surveylinks\external\survey_links\get::survey_matches_course($surveylink, $course->idnumber);
        $this->assertTrue($result);
    }

    /**
     * Test unit code from survey does not match Moodle course.
     */
    public function test_survey_does_not_match_course() {
        $course = $this->getDataGenerator()->create_course(['idnumber' => 'UNIT456']);
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
        $result = \block_surveylinks\external\survey_links\get::survey_matches_course($surveylink, $course->id);
        $this->assertFalse($result);
    }

    /**
     * Data provider for closed surveys.
     *
     * @return array [surveylink_model]
     */
    public function closed_survey_provider(): array {
        return [
            'Future survey' => [new \block_surveylinks\surveylink_model([
                'surveyId' => '1324',
                'surveyName' => 'Test Survey',
                'surveySubjectID' => 'TEST123',
                'surveyUnitCode' => 'UNIT123',
                'surveyUrl' => 'https://www.example.com/survey/UNIT123',
                'startDate' => date('c', time() + 1000),
                'endDate' => date('c', time() + 2000),
                'status' => 'Open',
            ])],
            'Past survey' => [new \block_surveylinks\surveylink_model([
                'surveyId' => '1324',
                'surveyName' => 'Test Survey',
                'surveySubjectID' => 'TEST123',
                'surveyUnitCode' => 'UNIT123',
                'surveyUrl' => 'https://www.example.com/survey/UNIT123',
                'startDate' => date('c', time() - 2000),
                'endDate' => date('c', time() - 1000),
                'status' => 'Open',
            ])],
            'Closed survey' => [new \block_surveylinks\surveylink_model([
                'surveyId' => '1324',
                'surveyName' => 'Test Survey',
                'surveySubjectID' => 'TEST123',
                'surveyUnitCode' => 'UNIT123',
                'surveyUrl' => 'https://www.example.com/survey/UNIT123',
                'startDate' => date('c', time() - 1000),
                'endDate' => date('c', time() + 1000),
                'status' => 'Closed',
            ])],
        ];
    }
}
