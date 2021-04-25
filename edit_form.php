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
 * Manage instance settings of the block.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_surveylinks_edit_form extends block_edit_form {

    /**
     * Add additional form elements to the block instance settings.
     *
     * @param object $mform
     */
    protected function specific_definition($mform) {
        // Header.
        $mform->addElement('header', 'config_header', get_string('blockconfig:header', 'block_surveylinks'));

        // Logo.
        $mform->addElement('filepicker', 'config_logo', get_string('blockconfig:logo', 'block_surveylinks'));

        // Link text.
        $mform->addElement('textarea', 'config_linktext', get_string('blockconfig:linktext', 'block_surveylinks'),
            ['rows' => 3, 'cols' => 41]);
        $mform->setType('config_linktext', PARAM_RAW);

        // Extra text.
        $mform->addElement('textarea', 'config_extratext', get_string('blockconfig:extratext', 'block_surveylinks'),
            ['rows' => 3, 'cols' => 41]);
        $mform->setType('config_extratext', PARAM_RAW);
    }

    /**
     * Return submitted data if properly submitted or returns NULL if validation fails or
     * if there is no submitted data.
     *
     * @return object submitted data; NULL if not valid or not submitted or cancelled
     */
    public function get_data() {
                // If file saved, move it into a file area accessible by all enrolled in course.
        $data = parent::get_data();
        if (!empty($data) && !empty($data->config_logo)) {
            if (!$this->save_logo_file($data->config_logo)) {
                // Logo file couldn't be saved so do not continue.
                return null;
            }
        }
        return $data;
    }

    /**
     * Save user draft file from form to plugin file area.
     *
     * @param string $itemid User draft file item id.
     * @return bool True on success.
     * @throws coding_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    private function save_logo_file(string $itemid): bool {
        global $USER, $COURSE;
        $fs = get_file_storage();
        $userdraftfiles = $fs->get_area_files(context_user::instance($USER->id)->id,
            'user', 'draft', $itemid,
            "itemid, filepath, filename", false);
        // We are only expecting a single file.
        $logofile = reset($userdraftfiles);
        // Check the file is an image and get file extension.
        if (!$logofile->is_valid_image()) {
            return false;
        }
        $fileextension = core_filetypes::get_file_extension($logofile->get_mimetype());

        $destfilerecord = [
            'contextid' => context_course::instance($COURSE->id)->id,
            'component' => 'block_surveylinks',
            'filearea' => 'logo',
            'itemid' => $COURSE->id,
            'filepath' => '/',
            'filename' => 'survey_logo.' . $fileextension,
            'timecreated' => $logofile->get_timecreated(),
            'timemodified' => time(),
        ];

        if (!$this->dest_file_exists($destfilerecord, $logofile)) {
            $fs->create_file_from_storedfile($destfilerecord, $logofile);
        }

        return true;
    }

    /**
     * Check if file already exists at destination.
     *
     * @param array $filerecord
     * @param stored_file $file
     * @return false
     * @throws coding_exception
     */
    private function dest_file_exists(array $filerecord, stored_file $srcfile) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($filerecord['contextid'], $filerecord['component'],
            $filerecord['filearea'], $filerecord['itemid'], "itemid, filepath, filename", false);
        foreach ($files as $file) {
            if ($fs->file_exists_by_hash($file->get_pathnamehash())) {
                return true;
            }
        }
        return false;
    }
}
