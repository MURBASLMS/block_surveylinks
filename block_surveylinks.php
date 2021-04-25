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
 * Block definition.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
require_once("$CFG->dirroot/blocks/moodleblock.class.php");

class block_surveylinks extends block_base {

    /** Relative URL of the default survey logo. */
    private const DEFAULT_LOGO_SRC = '/blocks/surveylinks/pix/MyFeedback.jpg';

    /**
     * Define the block.
     *
     * @throws coding_exception
     */
    public function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', 'block_surveylinks');
    }

    /**
     * Are you going to allow multiple instances of each block?
     * If yes, then it is assumed that the block WILL USE per-instance configuration
     * @return boolean
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Subclasses should override this and return true if the
     * subclass block has a settings.php file.
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Default return is false - header will be shown
     * @return boolean
     */
    public function hide_header() {
        return true;
    }

    /**
     * Set html attributes of the block.
     *
     * @return array|string[]
     */
    public function html_attributes() {
        // Hide the block until the async data has loaded unless editing is enabled.
        $attributes = [];
        if (!$this->page->blocks->edit_controls($this)) {
            $attributes['class'] = 'd-none';
        }
        return $attributes;
    }

    /**
     * Return the content object.
     *
     * @return stdObject
     */
    public function get_content() {
        global $OUTPUT;

        if (isset($this->content)) {
            return $this->content;
        }

        if (!$this->can_user_fetch_survey_links()) {
            return null;
        }

        $this->content = (object) [
            'text' => $OUTPUT->render_from_template('block_surveylinks/view', []),
            'footer' => '',
        ];

        return $this->content;
    }

    /**
     * Allows the block to load any JS it requires into the page.
     */
    public function get_required_javascript() {
        global $USER, $COURSE;

        if (!$this->can_user_fetch_survey_links()) {
            return null;
        }

        // Prepare data.
        $logosrc = $this->get_logo_src();
        $linktext = $this->get_link_text();
        $extratext = $this->get_extra_text();
        $params = [
            'userid' => $USER->id,
            'courseid' => $COURSE->id,
            'logosrc' => $logosrc,
            'linktext' => $linktext,
            'extratext' => $extratext,
        ];

        $this->page->requires->js_call_amd('block_surveylinks/view', 'init', $params);
    }

    /**
     * Check whether user can fetch and view survey links.
     *
     * @param stdClass|null $user Moodle user object.
     * @return bool
     * @throws coding_exception
     */
    private function can_user_fetch_survey_links(stdClass $user = null): bool {
        global $USER, $COURSE;

        if ($user === null) {
            $user = $USER;
        }

        // Check user has capability to view their surveys. By default, only students should see the block.
        if (!has_capability('block/surveylinks:viewmysurveylinks',
                context_course::instance($COURSE->id), $user)) {
            return false;
        }

        // We don't want sysadmins to see it either.
        if (is_siteadmin()) {
            return false;
        }

        // If block is not configured, don't render anything.
        if (!$this->is_plugin_setup()) {
            return false;
        }

        // If no course idnumber or user idnumber, no survey link can be found.
        if (empty($COURSE->idnumber) || empty($user->idnumber)) {
            return false;
        }

        return true;
    }

    /**
     * Check that required plugin settings are populated.
     *
     * @return bool
     *
     * @throws dml_exception
     */
    private function is_plugin_setup(): bool {
        $config = get_config('block_surveylinks');
        return !(empty($config->apibaseuri) || empty($config->apisecret));
    }

    /**
     * Get the src URL for the survey logo from the block config.
     *
     * @return string
     *
     * @throws coding_exception
     */
    public function get_logo_src(): string {
        global $COURSE;
        $default = (new moodle_url(self::DEFAULT_LOGO_SRC))->out();

        // Try and get the logo from the settings.
        $fs = get_file_storage();
        $files = $fs->get_area_files(context_course::instance($COURSE->id)->id, 'block_surveylinks',
                'logo', $COURSE->id, "itemid, filepath, filename", false);
        foreach ($files as $file) {
            if (strpos($file->get_filename(), 'survey_logo') !== false) {
                return moodle_url::make_file_url('/pluginfile.php', '/' . implode('/', [
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename(),
                ]));
            }
        }

        return $default;
    }

    /**
     * Get the link text from the block config.
     *
     * @return string
     */
    public function get_link_text(): string {
        $default = '';

        if (!empty($this->config->linktext)) {
            return $this->config->linktext;
        }

        return $default;
    }

    /**
     * Get the extra text from the block config.
     *
     * @return string
     */
    public function get_extra_text(): string {
        $default = '';

        if (!empty($this->config->extratext)) {
            return $this->config->extratext;
        }

        return $default;
    }


}
