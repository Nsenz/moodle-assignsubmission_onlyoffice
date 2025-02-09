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
 * This file contains the class for backup of this submission plugin
 *
 * @package assignsubmission_onlyoffice
 * @copyright 2023 Ascensio System SIA <integration@onlyoffice.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use assignsubmission_onlyoffice\filemanager;

/**
 * Backup class for onlyoffice submission plugin extending backup subplugin base class
 */
class backup_assignsubmission_onlyoffice_subplugin extends backup_subplugin {

    /**
     * Returns the subplugin information to attach to submission element
     * @return backup_subplugin_element
     */
    protected function define_submission_subplugin_structure() {
        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('submission_onlyoffice',
                                                      null,
                                                      array('itemid', 'contextid'));

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        $subpluginelement->set_source_table('files',
                                            array('itemid' => backup::VAR_PARENTID));

        // The parent is the submission.
        $subpluginelement->annotate_files('assignsubmission_onlyoffice',
                                          filemanager::FILEAREA_ONLYOFFICE_SUBMISSION_FILE,
                                          'itemid');

        $subpluginelement->set_source_table('files',
                                            array('contextid' => backup::VAR_CONTEXTID));

        $subpluginelement->annotate_files('assignsubmission_onlyoffice',
                                          filemanager::FILEAREA_ONLYOFFICE_ASSIGN_TEMPLATE,
                                          null,
                                          $this->task->get_contextid());

        $subpluginelement->annotate_files('assignsubmission_onlyoffice',
                                          filemanager::FILEAREA_ONLYOFFICE_ASSIGN_INITIAL,
                                          null,
                                          $this->task->get_contextid());

        return $subplugin;
    }
}
