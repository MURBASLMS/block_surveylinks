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
 *
 * @package   block_surveylinks
 * @copyright 2020 Tristan Mackay
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_surveylinks extends block_base
{
    public function init()
    {
        global $COURSE;
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', 'block_surveylinks');
        $this->courseid = $COURSE->id;
    }

    public function instance_allow_multiple()
    {
        return false;
    }

    public function has_config()
    {
        return true;
    }

    public function hide_header()
    {
        return true;
    }

    public function get_content()
    {
        global $CFG, $COURSE, $USER;
        if ($COURSE->idnumber !== null) {

            // TODO. Section here neeeded to call oAuth2 end point and get valid token.
            // Not Needed atm.


            // Hard coded studentid to test. Change to $USER->username or $USER->idnumber            
            $studentId = '12006497';

            // Add headers for API Call.
            // Will need to attach Bearer token here.
            $context = stream_context_create([
                "http" => [
                    "header" => "Ocp-Apim-Subscription-Key:" . get_config('block_surveylinks', 'surveyApiKey')
                ]
            ]);

            // Get URI and make Call to obtain any survey links.
            $apiURI = get_config('block_surveylinks', 'surveyApiUrl');
            $result = file_get_contents($apiURI . $studentId, false, $context);
            $surveys = json_decode($result);

            $this->content = new stdClass;
            
            // Loop the results looking for match to current course. 
            // TODO. add in check to not loop if no data is returned.
            foreach ($surveys->surveyTasks as $survey) {

                // Look for match with idnumber and make sure the survey is open.
                if ($survey->surveyUnitCode == $COURSE->idnumber && $survey->status == 'Open')
                    $this->content->text = '<div class="survey-links"><a class="block-btn" target="_blank" href="' .
                        $survey->surveyUrl .
                        '"><img class="img-responsive" src="https://static.murdoch.edu.au/lms/img/MyFeedback.jpg" /><p>Have your say</p><p>Unit and Teaching Surveys now open</p></a>' .
                        '<img alt="" />' .
                        '</div>';
            }
            return $this->content;
        }

        $this->content->text = 'No Moodle IdNumber';
        $this->content->footer = '';

        return $this->content;
    }
}
