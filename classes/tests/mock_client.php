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

namespace block_surveylinks\tests;

/**
 * Mock HTTP client for unit tests.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mock_client implements \block_surveylinks\http_client_interface {

    /**
     * Return a fixture response regardless of input.
     *
     * @param string $uri
     * @param array $params
     * @param array $headers
     * @return \string[][]
     */
    public function get(string $uri, array $params = [], array $headers = []): array {
        return [
            'surveyTasks' => [
                [
                    "surveyUrl" => "https://www.example.com/survey?id=12345AB",
                    "startDate" => "2021-04-13T14:00:00.0000000+00:00",
                    "endDate" => "2021-07-02T16:59:00.0000000+00:00",
                    "status" => "Open",
                    "surveyId" => "12345AB",
                    "surveyName" => "LMS Test Project",
                    "surveySubjectId" => "123456",
                    "surveyUnitCode" => "ABC123",
                    "surveyCourseCode" => "A1234",
                ],
            ],
        ];
    }
}
