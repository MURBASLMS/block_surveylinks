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
 * Test the configuration of the block.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_surveylinks;

use context_user;
use context_course;
use moodle_url;
use stored_file;
use stdClass;
use block_base;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . "/blocklib.php");
require_once($CFG->dirroot . "/blocks/surveylinks/edit_form.php");
require_once($CFG->dirroot . "/blocks/surveylinks/block_surveylinks.php");

/**
 * Test the configuration of the block.
 */
class edit_form_test extends \advanced_testcase {

    /**
     * This method runs before every test.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test clearing logo file.
     *
     * @covers \block_surveylinks_edit_form::clear_logo_file
     */
    public function test_clear_logo_file() {
        global $COURSE, $PAGE;
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $COURSE = $course;
        $this->create_logo_file($course);
        // Check file exists.
        $file = $this->get_logo_file($course);
        $this->assertNotNull($file);
        $this->assertInstanceOf(stored_file::class, $file);
        // Clear file, and check it is removed.
        $blockedit = new \block_surveylinks_edit_form((new moodle_url('/course/view.php', ['id' => $course->id])),
            $this->get_mock_block_instance($course),
            $PAGE
        );
        $COURSE = $course; // Need to reset the course as the block labyrinth sets it at some point.
        $blockedit->clear_logo_file();
        $file = $this->get_logo_file($course);
        $this->assertNull($file);
    }

    /**
     * Test clearing logo file if none exists.
     *
     * @covers \block_surveylinks_edit_form::clear_logo_file
     */
    public function test_clear_logo_file_if_none_exists() {
        global $COURSE, $PAGE;
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $COURSE = $course;
        // Check file doesn't exist.
        $file = $this->get_logo_file($course);
        $this->assertNull($file);
        // Clear file, and check it is removed.
        $blockedit = new \block_surveylinks_edit_form((new moodle_url('/course/view.php', ['id' => $course->id])),
            $this->get_mock_block_instance($course),
            $PAGE
        );
        $COURSE = $course; // Need to reset the course as the block labyrinth sets it at some point.
        $blockedit->clear_logo_file();
        $file = $this->get_logo_file($course);
        $this->assertNull($file);
    }

    /**
     * Test clearing user draft file.
     *
     * @covers \block_surveylinks_edit_form::clear_user_draft_files
     */
    public function test_clear_user_draft_files() {
        global $COURSE, $PAGE;
        $course = $this->getDataGenerator()->create_course();
        $COURSE = $course;
        $user = \core_user::get_user(2);
        $this->setAdminUser();
        $this->create_user_draft_file($user, '123456');
        // Check file exists.
        $file = $this->get_user_draft_file($user, '123456');
        $this->assertNotNull($file);
        $this->assertInstanceOf(stored_file::class, $file);
        // Clear file, and check it is removed.
        $blockedit = new \block_surveylinks_edit_form((new moodle_url('/course/view.php', ['id' => $course->id])),
            $this->get_mock_block_instance($course),
            $PAGE
        );
        $COURSE = $course; // Need to reset the course as the block labyrinth sets it at some point.
        $blockedit->clear_user_draft_files('123456');
        $file = $this->get_user_draft_file($user, '123456');
        $this->assertNull($file);
    }

    /**
     * Test clearing logo file if none exists.
     *
     * @covers \block_surveylinks_edit_form::clear_user_draft_files
     */
    public function test_clear_user_draft_files_if_none_exist() {
        global $COURSE, $PAGE;
        $course = $this->getDataGenerator()->create_course();
        $COURSE = $course;
        $user = \core_user::get_user(2);
        $this->setAdminUser();
        // Check file doesn't exist.
        $file = $this->get_user_draft_file($user, '123456');
        $this->assertNull($file);
        // Clear file, and check it is removed.
        $blockedit = new \block_surveylinks_edit_form((new moodle_url('/course/view.php', ['id' => $course->id])),
            $this->get_mock_block_instance($course),
            $PAGE
        );
        $COURSE = $course; // Need to reset the course as the block labyrinth sets it at some point.
        $blockedit->clear_user_draft_files('123456');
        $file = $this->get_user_draft_file($user, '123456');
        $this->assertNull($file);
    }

    /**
     * Create a user draft file.
     *
     * @param stdClass $user
     * @param string $itemid
     */
    protected function create_user_draft_file(stdClass $user, string $itemid) {
        global $CFG;
        $fs = get_file_storage();
        $filerecord = $this->get_user_draft_file_record($user, $itemid);
        $fs->create_file_from_pathname($filerecord, $CFG->dirroot . "/blocks/surveylinks/tests/fixtures/test.jpg");
    }

    /**
     * Get a user draft file.
     *
     * @param stdClass $user
     * @param string $itemid
     * @return stored_file|null
     */
    protected function get_user_draft_file(stdClass $user, string $itemid): ?stored_file {
        $fs = get_file_storage();
        $filerecord = $this->get_user_draft_file_record($user, $itemid);
        $file = $fs->get_file(
            $filerecord['contextid'],
            $filerecord['component'],
            $filerecord['filearea'],
            $filerecord['itemid'],
            $filerecord['filepath'],
            $filerecord['filename']
        );
        if ($file === false) {
            return null;
        } else {
            return $file;
        }
    }

    /**
     * Get a filerecord for a user draft file.
     *
     * @param stdClass $user
     * @param string $itemid
     * @return array
     */
    protected function get_user_draft_file_record(stdClass $user, string $itemid): array {
        $now = time();
        return [
            'contextid' => context_user::instance($user->id)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $itemid,
            'filepath' => '/',
            'filename' => 'survey_logo.jpg',
            'timecreated' => $now,
            'timemodified' => $now,
        ];
    }

    /**
     * Create a logo file for the block in a course context.
     *
     * @param stdClass $course
     */
    protected function create_logo_file(stdClass $course) {
        global $CFG;
        $fs = get_file_storage();
        $filerecord = $this->get_logo_file_record($course);
        $fs->create_file_from_pathname($filerecord, $CFG->dirroot . "/blocks/surveylinks/tests/fixtures/test.jpg");
    }

    /**
     * Get a logo file.
     *
     * @param stdClass $course
     * @return stored_file|null
     */
    protected function get_logo_file(stdClass $course): ?stored_file {
        $fs = get_file_storage();
        $filerecord = $this->get_logo_file_record($course);
        $file = $fs->get_file(
            $filerecord['contextid'],
            $filerecord['component'],
            $filerecord['filearea'],
            $filerecord['itemid'],
            $filerecord['filepath'],
            $filerecord['filename']
        );
        if ($file === false) {
            return null;
        } else {
            return $file;
        }
    }

    /**
     * Get a filerecord for a logo file.
     *
     * @param stdClass $course
     * @return array
     */
    protected function get_logo_file_record(stdClass $course): array {
        $now = time();
        return [
            'contextid' => context_course::instance($course->id)->id,
            'component' => 'block_surveylinks',
            'filearea' => 'logo',
            'itemid' => $course->id,
            'filepath' => '/',
            'filename' => 'survey_logo.jpg',
            'timecreated' => $now,
            'timemodified' => $now,
        ];
    }

    /**
     * Get a mock instance of a surveylinks block with minimum data.
     *
     * @param stdClass $course
     * @return block_base
     */
    protected function get_mock_block_instance(stdClass $course): block_base {
        $instance = (object) [
            'id' => '123',
            'blockname' => 'surveylinks',
            'parentcontextid' => context_course::instance($course->id)->id,
            'requiredbytheme' => '1',
            'pagetypepattern' => '',
            'defaultregion' => 'Right',
            'region' => 'Right',
            'defaultweight' => '1',
            'weight' => '1',
            'configdata' => null,
            'timecreated' => 0,
            'timemodified' => 0,
        ];
        $block = new \block_surveylinks();
        $block->instance = $instance;
        $block->context = context_course::instance($course->id);
        return $block;
    }
}
