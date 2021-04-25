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
 * JS code for the view of the surveylinks block.
 *
 * @module     block_surveylinks/view
 * @package    block_surveylinks
 * @copyright  2020 Andrew Madden <andrewmadden@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Notification from 'core/notification';
import Templates from 'core/templates';
import {getSurveyLinks} from './explorance_api';

const SELECTORS = {
    'BLOCK_SURVEYLINKS': "[data-block='surveylinks']",
    'BLOCK_CONTENT_CONTAINER': '.block-content',
};

/**
 * Populate surveylinks data in surveylinks block.
 *
 * @param {Array} surveylinks Array of surveylink models.
 */
async function populateSurveyLinks(surveylinks) {
    const surveylinkBlock = document.querySelector(SELECTORS.BLOCK_SURVEYLINKS);
    const contentContainer = surveylinkBlock.querySelector(SELECTORS.BLOCK_CONTENT_CONTAINER);

    // We are only expecting one surveylink object from an array.
    if (!Array.isArray(surveylinks) || surveylinks.length !== 1) {
        return;
    }

    try {
        const args = {"surveylink": surveylinks.pop()};
        contentContainer.innerHTML = await Templates.render('block_surveylinks/surveylink', args);
        surveylinkBlock.classList.toggle('d-none', false);
    } catch (exception) {
        Notification.exception(exception);
    }
}

/**
 * Initialize the JS for the block.
 *
 * @param {int} userid User id.
 * @param {int} courseid Course id.
 * @param {String} logosrc Src URL for logo.
 * @param {String} linktext Text to display in link.
 * @param {String} extratext Text to display under link.
 */
async function init(userid, courseid, logosrc, linktext, extratext) {

    // Get data.
    const promises = [
        getSurveyLinks(userid, courseid),
    ];

    const [surveylinks] = await Promise.all(promises);

    // Add display data to links.
    surveylinks.map((link) => {
        link.logosrc = logosrc;
        link.linktext = linktext;
        link.extratext = extratext;
        return link;
    });

    // Populate page content.
    populateSurveyLinks(surveylinks);
}

export {init};
