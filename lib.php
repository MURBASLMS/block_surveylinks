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
 * Main plugin function lib. Use for overriding core functions.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Handle sending a file from the plugin.
 *
 * @param stdClass $course course object
 * @param stdClass $birecord block instance record
 * @param context $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $sendfileoptions additional options affecting the file serving
 */
function block_surveylinks_pluginfile($course, $birecord, $context, $filearea, $args, $forcedownload, $sendfileoptions) {
    if ($context->get_course_context(false) || $context->contextlevel === CONTEXT_SYSTEM) {
        // If block is in course context, then check if user has capability to access course.
        require_course_login($course);

        $itemid = (int)array_shift($args);
        $relativepath = implode('/', $args);
        $fullpath = "/{$context->id}/block_surveylinks/$filearea/$itemid/$relativepath";
        $fs = get_file_storage();
        $file = $fs->get_file_by_hash(sha1($fullpath));

        if (empty($file) || $file->is_directory()) {
            send_file_not_found();
        }

        send_stored_file($file, null, 0, $forcedownload, $sendfileoptions);
    }
}
