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

use context_system;
use context_course;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../block_surveylinks.php');

/**
 * Test the block definition.
 *
 * @package    block_surveylinks
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class block_surveylinks_test extends \advanced_testcase {

    /**
     * Roles for testing.
     * @var array
     */
    protected $roles;

    /**
     * This method runs before every test.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test content is created when user can view survey links.
     *
     * @covers \block_surveylinks::get_content
     */
    public function test_get_content_when_expecting_survey_data(): void {
        global $COURSE;
        set_config('apibaseuri', 'https://example.com/api/', 'block_surveylinks');
        set_config('apisecret', 'abc123', 'block_surveylinks');
        $user = $this->getDataGenerator()->create_user(['idnumber' => '1234']);
        $course = $this->getDataGenerator()->create_course(['idnumber' => 'qwerty']);
        $this->setUser($user);
        $COURSE = $course;
        $this->assign_capability('block/surveylinks:viewmysurveylinks');

        $block = new \block_surveylinks();
        $content = $block->get_content();
        $this->assertNotEmpty($content->text);
    }

    /**
     * Test content is not created when user cannot view survey links.
     *
     * @covers \block_surveylinks::get_content
     */
    public function test_get_content_when_missing_capability(): void {
        global $COURSE;
        set_config('apibaseuri', 'https://example.com/api/', 'block_surveylinks');
        set_config('apisecret', 'abc123', 'block_surveylinks');
        $user = $this->getDataGenerator()->create_user(['idnumber' => '1234']);
        $course = $this->getDataGenerator()->create_course(['idnumber' => 'qwerty']);
        $this->setUser($user);
        $COURSE = $course;

        $block = new \block_surveylinks();
        $content = $block->get_content();
        $this->assertNull($content);
    }

    /**
     * Test content is not created when user cannot view survey links.
     *
     * @covers \block_surveylinks::get_content
     */
    public function test_get_content_when_site_admin(): void {
        global $COURSE;
        set_config('apibaseuri', 'https://example.com/api/', 'block_surveylinks');
        set_config('apisecret', 'abc123', 'block_surveylinks');
        $course = $this->getDataGenerator()->create_course(['idnumber' => 'qwerty']);
        $this->setAdminUser();
        $COURSE = $course;
        $this->assign_capability('block/surveylinks:viewmysurveylinks');

        $block = new \block_surveylinks();
        $content = $block->get_content();
        $this->assertNull($content);
    }

    /**
     * Test content is not created when user cannot view survey links.
     *
     * @covers \block_surveylinks::get_content
     */
    public function test_get_content_when_missing_plugin_settings(): void {
        global $COURSE;
        $user = $this->getDataGenerator()->create_user(['idnumber' => '1234']);
        $course = $this->getDataGenerator()->create_course(['idnumber' => 'qwerty']);
        $this->setUser($user);
        $COURSE = $course;
        $this->assign_capability('block/surveylinks:viewmysurveylinks');

        $block = new \block_surveylinks();
        $content = $block->get_content();
        $this->assertNull($content);
    }

    /**
     * Test content is not created when user cannot view survey links.
     *
     * @covers \block_surveylinks::get_content
     */
    public function test_get_content_when_missing_courseidnumber(): void {
        global $COURSE;
        set_config('apibaseuri', 'https://example.com/api/', 'block_surveylinks');
        set_config('apisecret', 'abc123', 'block_surveylinks');
        $user = $this->getDataGenerator()->create_user(['idnumber' => '1234']);
        $course = $this->getDataGenerator()->create_course();
        $this->setUser($user);
        $COURSE = $course;
        $this->assign_capability('block/surveylinks:viewmysurveylinks');

        $block = new \block_surveylinks();
        $content = $block->get_content();
        $this->assertNull($content);
    }

    /**
     * Test get default logo src.
     *
     * @covers \block_surveylinks::get_logo_src
     */
    public function test_get_get_logo_src_default(): void {
        $block = new \block_surveylinks();
        $url = $block->get_logo_src();
        $this->assertEquals((new moodle_url('/blocks/surveylinks/pix/MyFeedback.jpg'))->out(), $url);
    }

    /**
     * Test get logo src stored in plugin filearea.
     *
     * @covers \block_surveylinks::get_logo_src
     */
    public function test_get_logo_src_config(): void {
        global $COURSE;
        $course = $this->getDataGenerator()->create_course();
        $COURSE = $course;
        $fs = get_file_storage();
        $filerecord = [
            'contextid' => context_course::instance($course->id)->id,
            'component' => 'block_surveylinks',
            'filearea' => 'logo',
            'itemid' => $course->id,
            'filepath' => '/',
            'filename' => 'survey_logo.jpg',
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        $file = $fs->create_file_from_string($filerecord, 'test');

        $block = new \block_surveylinks();
        $block->config = (object) [
            'logosrc' => '1234',
        ];
        $url = $block->get_logo_src();
        $this->assertEquals(moodle_url::make_file_url('/pluginfile.php', '/' . implode('/', [
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(),
        ])), $url);
    }

    /**
     * Test get default link text.
     *
     * @covers \block_surveylinks::get_link_text
     */
    public function test_get_link_text_default(): void {
        $block = new \block_surveylinks();
        $text = $block->get_link_text();
        $this->assertEquals('', $text);
    }

    /**
     * Test get link defined in config.
     *
     * @covers \block_surveylinks::get_link_text
     */
    public function test_get_link_text_config(): void {
        $block = new \block_surveylinks();
        $block->config = (object) [
            'linktext' => 'helloworld',
        ];
        $text = $block->get_link_text();
        $this->assertEquals('helloworld', $text);
    }

    /**
     * Test get default extra text.
     *
     * @covers \block_surveylinks::get_extra_text
     */
    public function test_get_extra_text_default(): void {
        $block = new \block_surveylinks();
        $text = $block->get_extra_text();
        $this->assertEquals('', $text);
    }

    /**
     * Test get extra defined in config.
     *
     * @covers \block_surveylinks::get_extra_text
     */
    public function test_get_extra_text_config(): void {
        $block = new \block_surveylinks();
        $block->config = (object) [
            'extratext' => 'helloworld',
        ];
        $text = $block->get_extra_text();
        $this->assertEquals('helloworld', $text);
    }

    /**
     * Helper method to get role id.
     *
     * @param null $context
     * @return mixed
     */
    protected function get_roleid($context = null) {
        global $USER;
        if ($context === null) {
            $context = context_system::instance();
        }
        if (is_object($context)) {
            $context = $context->id;
        }
        if (empty($this->roles)) {
            $this->roles = [];
        }
        if (empty($this->roles[$USER->id])) {
            $this->roles[$USER->id] = [];
        }
        if (empty($this->roles[$USER->id][$context])) {
            $this->roles[$USER->id][$context] = create_role(
                'Role for ' . $USER->id . ' in ' . $context,
                'role' . $USER->id . '-' . $context,
                '-'
            );
            role_assign($this->roles[$USER->id][$context], $USER->id, $context);
        }
        return $this->roles[$USER->id][$context];
    }

    /**
     * Helper method to assign a system capability.
     *
     * @param $capability
     * @param int $permission
     * @param null $contextid
     */
    protected function assign_capability($capability, $permission = CAP_ALLOW, $contextid = null): void {
        if ($contextid === null) {
            $contextid = context_system::instance();
        }
        if (is_object($contextid)) {
            $contextid = $contextid->id;
        }
        assign_capability($capability, $permission, $this->get_roleid($contextid), $contextid, true);
        accesslib_clear_all_caches_for_unit_testing();
    }
}
