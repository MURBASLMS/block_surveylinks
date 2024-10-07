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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use moodle_url;
use Psr\Http\Message\ResponseInterface;

/**
 * Guzzle client for making HTTP requests.
 *
 * @package    block_surveylinks
 * @author     Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class guzzle_client implements http_client_interface {

    /**
     * @var Client the Guzzle client.
     */
    protected $client;

    /**
     * guzzle_client constructor.
     *
     * @param array $options optional default request options.
     *
     * @see \GuzzleHttp\RequestOptions for a list of available request options.
     */
    public function __construct(array $options = []) {
        $this->validate_options($options);
        $this->client = new Client($options);
    }

    /**
     * Validate request options.
     *
     * @param array $options request options to validate.
     *
     * @return bool always true if options valid.
     * @throws \coding_exception if a request option is invalid.
     */
    protected function validate_options(array $options) {
        $requestoptions = new \ReflectionClass('\GuzzleHttp\RequestOptions');
        $validoptions = $requestoptions->getConstants();
        // Add 'handler' option, this is valid option for adding handlers and middleware to client.
        $validoptions[] = 'handler';
        // Add 'base_uri' option, valid option for adding a base URI for HTTP requests.
        $validoptions[] = 'base_uri';

        foreach (array_keys($options) as $option) {
            if (!in_array($option, $validoptions)) {
                throw new \coding_exception("Invalid HTTP request option: '$option', see \GuzzleHttp\RequestOptions " .
                    "for a list of valid request options to pass to client");
            }
        }

        return true;
    }

    /**
     * Make an HTTP GET request and return response.
     *
     * @param string $uri the target URI to make request to.
     * @param array $params associative array of any query params to include in URI.
     * @param array $headers any custom headers to include in request.
     *
     * @return array Response data.
     *
     * @throws http_exception if the HTTP request failed.
     */
    public function get(string $uri, array $params = [], array $headers = []): array {
        $url = new moodle_url($uri, $params);
        $uri = $url->out(false);
        $request = new Request('GET', $uri, $headers);

        try {
            $response = $this->client->send($request);
        } catch (GuzzleException $exception) {
            throw new http_exception('error:http:get', 'block_surveylinks', '', $exception->getMessage());
        }

        return $this->extract_response_content($response);
    }

    /**
     * Extract the response content.
     *
     * @param ResponseInterface $response the response to get content from.
     *
     * @return array associative array of JSON decoded content.
     */
    protected function extract_response_content(ResponseInterface $response): array {
        $data = json_decode($response->getBody()->getContents(), true);
        $content = [];

        if (!empty($data)) {
            $content = $data;
        }

        return $content;
    }
}
