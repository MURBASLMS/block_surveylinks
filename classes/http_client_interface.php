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

namespace block_surveylinks;

/**
 * Interface for a client used to make HTTP requests.
 *
 * @package    block_surveylinks
 * @author     Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface http_client_interface {

    /**
     * Make an HTTP GET request.
     *
     * @param string $uri the target URI to make request to.
     * @param array $params associative array of any query params to include in URI.
     * @param array $headers any custom headers to include in request.
     *
     * @return array Response data.
     *
     * @throws \moodle_exception if the HTTP request failed with no response.
     */
    public function get(string $uri, array $params = [], array $headers = []): array;
}
