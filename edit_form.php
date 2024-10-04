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

global $CFG;

require_once($CFG->dirroot . "/blocks/edit_form.php");

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

        // Reset default button.
        $mform->addElement('submit', 'config_resetdefault', get_string('blockconfig:resetdefault', 'block_surveylinks'));
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

        // If form is empty, do nothing.
        if (empty($data)) {
            return $data;
        }

        // Reset defaults button was clicked, so remove all block level config.
        if (!empty($data->config_resetdefault)) {
            // Clear logo files.
            $this->clear_logo_file();
            if (!empty($data->config_logo)) {
                $this->clear_user_draft_files($data->config_logo);
                $data->config_logo = '';
            }
            // Clear text.
            $data->config_linktext = $this->block->get_link_text_default();
            $data->config_extratext = $this->block->get_extra_text_default();
            return $data;
        }

        // Try and save the uploaded logo.
        if (!empty($data->config_logo)) {
            if (!$this->save_logo_file($data->config_logo)) {
                // Logo file couldn't be saved so do not continue.
                return null;
            }
            // Clear user draft file as it does not necessarily correlate to the block logo image.
            $this->clear_user_draft_files($data->config_logo);
            $data->config_logo = '';
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

        // If no image is uploaded, don't do anything, but allow form to submit successfully.
        if (empty($userdraftfiles)) {
            return true;
        }

        // We are only expecting a single file.
        $logofile = reset($userdraftfiles);
        // Check the file is an image and get file extension.
        if (!$logofile->is_valid_image()) {
            return false;
        }
        $fileextension = core_filetypes::get_file_extension($logofile->get_mimetype());

        $contextid = context_course::instance($COURSE->id)->id;
        $destfilerecord = [
            'contextid' => $contextid,
            'component' => 'block_surveylinks',
            'filearea' => 'logo',
            'itemid' => $COURSE->id,
            'filepath' => '/',
            'filename' => 'survey_logo.' . $fileextension,
            'timecreated' => $logofile->get_timecreated(),
            'timemodified' => time(),
        ];

        // Clear any existing logo images and create the new one.
        $fs->delete_area_files($contextid, 'block_surveylinks', 'logo', $COURSE->id);
        $fs->create_file_from_storedfile($destfilerecord, $logofile);

        return true;
    }

    /**
     * Clear all user draft files with a specific itemid.
     *
     * @param string $itemid User draft file itemid.
     * @return bool
     */
    public function clear_user_draft_files(string $itemid): bool {
        global $USER;
        $fs = get_file_storage();
        $contextid = context_user::instance($USER->id)->id;
        return $fs->delete_area_files($contextid, 'user', 'draft', $itemid);
    }

    /**
     * Clear any logo file saved in this context.
     *
     * @return bool True on success.
     */
    public function clear_logo_file(): bool {
        global $COURSE;
        $fs = get_file_storage();
        $contextid = context_course::instance($COURSE->id)->id;
        return $fs->delete_area_files($contextid, 'block_surveylinks', 'logo', $COURSE->id);
    }
}
