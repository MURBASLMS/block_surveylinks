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
 * Events triggered when HTTP request failed.
 *
 * @package    block_surveylinks
 * @author     Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_surveylinks\event;

defined('MOODLE_INTERNAL') || die();

 /**
  * Events triggered when HTTP request failed.
  *
  * @property-read array $other {
  *      Extra information about event.
  *      - string reason: description of why request failed.
  * }
  *
  * @package    local_preview
  * @author     Tom Dickman <tomdickman@catalyst-au.net>
  * @copyright  2020 Catalyst IT
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */
class http_request_failed extends \core\event\base {

    /**
     * Initialise the event data.
     */
    protected function init() {
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['crud'] = 'r';
        $this->context = \context_system::instance();
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event:httprequestfailed', 'block_surveylinks');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "Failed making an HTTP request: '{$this->other['reason']}'";
    }

    /**
     * Validates the custom data.
     *
     * @throws \coding_exception if missing required data.
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['reason'])) {
            throw new \coding_exception('The \'reason\' value must be set in other.');
        }
    }
}
