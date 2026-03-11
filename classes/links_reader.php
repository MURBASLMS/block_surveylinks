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

use block_surveylinks\external\survey_links\get;
use core_cache\cache;
use local_callista\model\course_unit;

/**
 * Links reader.
 *
 * @package    block_surveylinks
 * @copyright  2026 Murdoch University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class links_reader {

    /** @var cache $cache The cache instance. */
    protected $cache;
    /** @var explorance_api $api The API instance. */
    protected $api;
    /** @var ?array $unitcodes The unit codes. */
    protected $unitcodes;

    /**
     * Constructor.
     *
     * @param int $courseid
     * @param int $userid
     */
    public function __construct(protected int $courseid, protected int $userid) {
        $this->api = new explorance_api(new guzzle_client());
        $this->cache = cache::make('block_surveylinks', 'links');
    }

    /**
     * Filter the available surveys.
     *
     * @param array $links
     * @return array
     */
    protected function filter_available(array $links): array {
        return array_values(array_filter($links, function($link) {
            return get::is_survey_available($link) && get::survey_matches_course($link, $this->get_unit_codes());
        }));
    }

    /**
     * Get the cache key for a unit code.
     *
     * @param string $unitcode
     * @return string
     */
    protected function get_cache_key($unitcode): string {
        return 'links_' . $this->courseid . '_' . $this->userid . '_' . $unitcode;
    }

    /**
     * Get the links.
     *
     * Do not use this to check if the cache exists, this is only to be used
     * to retrieve the links after we've established that there should be some,
     * or that we know it's cached and there aren't any.
     *
     * @return array
     */
    public function get_links(): array {
        $alllinks = [];
        $useridnumber = null;

        foreach ($this->get_unit_codes() as $unitcode) {
            $cachekey = $this->get_cache_key($unitcode);
            $data = $this->cache->get($cachekey);

            // If we have links, add them to the list.
            if ($data !== false) {
                $links = array_values(array_map(function($link) {
                    return new surveylink_model($link);
                }, $data));
                $alllinks = array_merge($alllinks, $links);
                continue;
            }

            // If we don't have a user ID number, we can't have links.
            $useridnumber ??= $this->get_user_idnumber();
            if (empty($useridnumber)) {
                $this->cache->set($cachekey, []);
                continue;
            }

            // Fetch the links, and save them to the cache right away.
            $links = (array) $this->api->get_survey_links($useridnumber, $unitcode);
            $this->cache->set($cachekey, array_values(array_map(function($link) {
                return $link->to_raw();
            }, $links)));

            // Add to the list of all links.
            $alllinks = array_merge($alllinks, $links);
        }

        return $this->filter_available($alllinks);
    }

    /**
     * Get the course unit codes.
     *
     * This will place the unit codes in the cache, we don't really mind
     * if they persist a bit longer than an update to the course specs
     * as it's inoffensive.
     *
     * @return array
     */
    protected function get_unit_codes(): array {
        if (!isset($this->unitcodes)) {
            $cachekey = 'courseunits_' . $this->courseid;
            $data = $this->cache->get($cachekey);
            if ($data === false) {
                $data = array_map(function($courseunit) {
                    return $courseunit->get('unitcode');
                }, course_unit::get_records(['courseid' => $this->courseid]));
                $this->cache->set($cachekey, $data);
            }
            $this->unitcodes = (array) $data;
        }
        return $this->unitcodes;
    }

    /**
     * Get the user ID number.
     *
     * @return string
     */
    protected function get_user_idnumber(): ?string {
        global $DB;
        return $DB->get_field('user', 'idnumber', ['id' => $this->userid]) ?: null;
    }

    /**
     * Whether we have cached links, or we don't know.
     *
     * In this case, we know we want to show the block because we will
     * either show the links, or we will fill the cache.
     *
     * @return bool
     */
    public function has_links_or_unknown(): bool {
        foreach ($this->get_unit_codes() as $unitcode) {
            $cachekey = $this->get_cache_key($unitcode);
            $data = $this->cache->get($cachekey);
            if ($data !== false && !empty($data)) {
                return true; // Has links.
            } else if ($data === false) {
                return true; // Cached miss.
            }
        }
        return false;
    }
}
