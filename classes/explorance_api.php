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

use block_surveylinks\event\http_request_failed;

/**
 * Manage the API calls to explorance API.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class explorance_api {

    /** Header used for sending Client Secret with API requests. */
    private const API_SUBMISSION_KEY = 'Ocp-Apim-Subscription-Key';

    /** Data key for array containing surveylinks.  */
    private const KEY_SURVEYLINKS = 'surveyTasks';

    /** @var http_client_interface $client HTTP Client. */
    private $client;

    /** @var string $baseuri API Base URI. */
    private $baseuri;

    /** @var array $headers Headers to be sent in API requests. */
    private $headers;

    /**
     * The explorance_api constructor.
     *
     * @param \block_surveylinks\http_client_interface $client The client to handle HTTP requests.
     */
    public function __construct(http_client_interface $client) {
        $this->client = $client;

        $config = get_config('block_surveylinks');

        if (empty($config->apibaseuri)) {
            throw new \moodle_exception('error:api:nobaseuri', 'block_surveylinks');
        } else {
            $this->baseuri  = $config->apibaseuri;
        }

        if (empty($config->apisecret)) {
            throw new \moodle_exception('error:api:credentials', 'block_surveylinks');
        } else {
            $this->headers = [
                self::API_SUBMISSION_KEY => $config->apisecret,
            ];
        }
    }

    /**
     * Get the survey links from the API.
     *
     * @param string $userid Explorance user ID.
     * @param string $unitcode Explorance unit code.
     * @return array [surveylink_model]
     *
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_survey_links(string $userid, string $unitcode): array {
        $params = [
            'surveylinks' => $userid,
            'unit' => $unitcode,
        ];

        $data = $this->call($params);
        $data = self::get_subset_from_multidimensional_array($data, self::KEY_SURVEYLINKS);
        if ($data === false) {
            $data = [];
        }
        return $this->create_data_models($data, surveylink_model::class);
    }

    /**
     * Make an API call.
     *
     * @param array $params [url_parameter => value] The parameters to use in API call.
     *
     * @return array validated response content models.
     * @throws \moodle_exception if there was an issue with API call response data.
     */
    protected function call(array $params = []): array {
        $responsedata = [];
        $uri = $this->get_uri($params);

        try {
            // Because API uses url parameters, they are already added to the uri so we don't need to pass them again.
            $responsedata = $this->client->get($uri, [], $this->headers);
        } catch (http_exception $exception) {
            // There was an issue getting the data from the Mix API. Log the details.
            http_request_failed::create([
                'other' => [
                    'reason' => $exception->getMessage(),
                ],
            ])->trigger();
        }

        return $responsedata;
    }

    /**
     * Get API request response data parsed into appropriate models.
     *
     * @param array $data the raw data to get models for.
     * @param string $modelclassname The class name of the model matching the data.
     *
     * @return array $models data converted to models for handling.
     * @throws \coding_exception if there is no model for endpoint.
     */
    public function create_data_models(array $data, string $modelclassname) {
        $models = [];

        if (!class_exists($modelclassname)) {
            throw new \coding_exception("There is no class found for model with class name: $modelclassname");
        }

        foreach ($data as $datum) {
            $models[] = new $modelclassname($datum);
        }

        return $models;
    }

    /**
     * Get the URI to make a request via a specific endpoint.
     *
     * @param array $params [param_urls => $value] The API url parameters to make request with.
     *
     * @return string the URI.
     */
    protected function get_uri(array $params): string {
        $paramstring = '';
        foreach ($params as $key => $value) {
            $paramstring .= '/' . ltrim($key, '/') . '/' . ltrim($value, '/');
        }
        return rtrim($this->baseuri, '/') . '/' . $paramstring;
    }

    /**
     * Search for a subset of data in a multidimensional array.
     *
     * @param array $data the multidimensional array to get subset from.
     * @param string $tofind the subset data key to find in multidimensional data array.
     *
     * @return mixed the value of found subset of array, boolean false if no value found.
     */
    public static function get_subset_from_multidimensional_array(array $data, string $tofind) {
        // Shallow search first before recursion, to get the most shallow match if multiple.
        foreach ($data as $key => $value) {
            if ((string) $key === $tofind) {
                return $value;
            }
        }

        // Next level down search before recursion, to avoid going deeper on first element and missing shallow
        // results in siblings.
        foreach ($data as $value) {
            if (is_array($value) && array_key_exists($tofind, $value)) {
                return $value[$tofind];
            }
        }

        // Deeper recursive search if nothing found in shallow search.
        foreach ($data as $value) {
            if (is_array($value)) {
                $recursiveresult = self::get_subset_from_multidimensional_array($value, $tofind);
                if (!empty($recursiveresult)) {
                    return $recursiveresult;
                }
            }
        }

        return false;
    }
}
