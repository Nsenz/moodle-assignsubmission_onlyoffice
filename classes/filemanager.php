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
 * This file contains the class for file management
 *
 * @package    assignsubmission_onlyoffice
 * @subpackage
 * @copyright   2023 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice;

use stored_file;

/**
 * Class wrapper for management of onlyoffice plugin files
 */
class filemanager {

    /**
     * Submission file area
     */
    const FILEAREA_ONLYOFFICE_SUBMISSION_FILE = 'assignsubmission_onlyoffice_file';

    /**
     * Template file area
     */
    const FILEAREA_ONLYOFFICE_ASSIGN_TEMPLATE = 'assignsubmission_onlyoffice_template';

    /**
     * Initial file area
     */
    const FILEAREA_ONLYOFFICE_ASSIGN_INITIAL = 'assignsubmission_onlyoffice_initial';

    /**
     * Draft file area
     */
    const FILEAREA_ONLYOFFICE_SUBMISSION_DRAFT = 'assignsubmission_onlyoffice_draft';

    /**
     * Plugin component name
     */
    const COMPONENT_NAME = 'assignsubmission_onlyoffice';

    /**
     * Get file from onlyoffice file area
     *
     * @param int $contextid context identifier.
     * @param string $itemid property of the file that is submissionid.
     *
     * @return stored_file
     */
    public static function get($contextid, $itemid) {
        return self::get_base($contextid, self::FILEAREA_ONLYOFFICE_SUBMISSION_FILE, $itemid);
    }

    /**
     * Create file to onlyoffice file area
     *
     * @param int $contextid context identifier.
     * @param string $itemid property of the file that is submissionid.
     * @param string $name file name.
     * @param string $ext file extension.
     * @param string $userid user identifier.
     *
     * @return stored_file
     */
    public static function create($contextid, $itemid, $name, $ext, $userid) {
        return self::create_base($contextid, $itemid, $name, $ext, self::FILEAREA_ONLYOFFICE_SUBMISSION_FILE, $userid);
    }

    /**
     * Create file to onlyoffice file area by initial file
     *
     * @param stored_file $initial initial file.
     * @param string $itemid property of the file that is submissionid.
     * @param string $name file name.
     * @param string $ext file extension.
     * @param string $userid user identifier.
     *
     * @return stored_file
     */
    public static function create_by_initial($initial, $itemid, $name, $ext, $userid) {
        $fs = get_file_storage();

        $fr = (object)[
            'filearea' => self::FILEAREA_ONLYOFFICE_SUBMISSION_FILE,
            'itemid' => $itemid,
            'filename' => $name . $itemid . '.' . $ext,
            'userid' => $userid,
            'timecreated' => time()
        ];

        $newfile = $fs->create_file_from_storedfile($fr, $initial);

        return $newfile;
    }

    /**
     * Get template file from onlyoffice file area
     *
     * @param int $contextid context identifier.
     *
     * @return stored_file
     */
    public static function get_template($contextid) {
        return self::get_base($contextid, self::FILEAREA_ONLYOFFICE_ASSIGN_TEMPLATE, 0);
    }

    /**
     * Create template file to onlyoffice file area
     *
     * @param int $contextid context identifier.
     * @param string $ext file extension.
     * @param string $userid user identifier.
     *
     * @return stored_file
     */
    public static function create_template($contextid, $ext, $userid) {
        return self::create_base($contextid, 0, '', $ext, self::FILEAREA_ONLYOFFICE_ASSIGN_TEMPLATE, $userid);
    }

    /**
     * Get initial file from onlyoffice file area
     *
     * @param int $contextid context identifier.
     *
     * @return stored_file
     */
    public static function get_initial($contextid) {
        return self::get_base($contextid, self::FILEAREA_ONLYOFFICE_ASSIGN_INITIAL, 0);
    }

    /**
     * Create initial file to onlyoffice file area
     *
     * @param int $contextid context identifier.
     * @param string $ext file extension.
     * @param string $userid user identifier.
     * @param string $url file url resource.
     *
     * @return stored_file
     */
    public static function create_initial($contextid, $ext, $userid, $url) {
        return self::create_by_url_base($contextid, 0, '', $ext, self::FILEAREA_ONLYOFFICE_ASSIGN_INITIAL, $userid, $url);
    }

    /**
     * Write stored file
     *
     * @param stored_file $file updateble file.
     * @param string $url file url resource.
     *
     * @return void
     */
    public static function write($file, $url) {
        $fs = get_file_storage();

        $fr = array(
            'contextid' => $file->get_contextid(),
            'component' => $file->get_component(),
            'filearea' => self::FILEAREA_ONLYOFFICE_SUBMISSION_DRAFT,
            'itemid' => $file->get_itemid(),
            'filename' => $file->get_filename() . '_temp',
            'filepath' => '/',
            'userid' => $file->get_userid(),
            'timecreated' => $file->get_timecreated()
        );

        $tmpfile = $fs->create_file_from_url($fr, $url);
        $file->replace_file_with($tmpfile);
        $file->set_timemodified(time());
        $tmpfile->delete();
    }

    /**
     * Delete file from onlyoffice file area
     *
     * @param int $contextid context identifier.
     * @param string $itemid property of the file that is submissionid.
     *
     * @return void
     */
    public static function delete($contextid, $itemid) {
        $fs = get_file_storage();

        $fs->delete_area_files($contextid, self::COMPONENT_NAME, self::FILEAREA_ONLYOFFICE_SUBMISSION_FILE, $itemid);
    }

    /**
     * Get base template path
     *
     * @param string $ext file extension.
     *
     * @return string
     */
    public static function get_template_path($ext) {
        global $USER;
        global $CFG;

        if ($ext === 'docxf') {
            $pathlocale = \mod_onlyofficeeditor\util::PATH_LOCALE[$USER->lang];
            if ($pathlocale === null) {
                $pathlocale = "en-US";
            }

            $templatepath = $CFG->dirroot . '/mod/assign/submission/onlyoffice/newdocs/' . $pathlocale . '/new.' . $ext;
            if (file_exists($templatepath)) {
                return $templatepath;
            }
        }

        return \mod_onlyofficeeditor\util::get_template_path($ext);
    }

    /**
     * Build unique document identifier
     *
     * @param stored_file $file updateble file.
     *
     * @return string
     */
    public static function generate_key($file) {
        $contextid = $file->get_contextid();
        $itemid = $file->get_itemid();
        $timemodified = $file->get_timemodified();

        return $contextid . $itemid . $timemodified;
    }

    /**
     * Base method for getting file
     *
     * @param int $contextid context identifier.
     * @param string $filearea file area.
     * @param string $itemid property of the file that is submissionid.
     *
     * @return stored_file
     */
    private static function get_base($contextid, $filearea, $itemid) {
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $contextid,
            self::COMPONENT_NAME,
            $filearea,
            $itemid, '', false, 0, 0, 1);

        $file = reset($files);
        if (!$file) {
            return null;
        }

        return $file;
    }

    /**
     * Base method for creating file by template
     *
     * @param int $contextid context identifier.
     * @param string $itemid property of the file that is submissionid.
     * @param string $name file name.
     * @param string $ext file extension.
     * @param string $filearea file area.
     * @param string $userid user identifier.
     *
     * @return stored_file
     */
    private static function create_base($contextid, $itemid, $name, $ext, $filearea, $userid) {
        $pathname = self::get_template_path($ext);

        $fs = get_file_storage();

        $newfile = $fs->create_file_from_pathname((object)[
            'contextid' => $contextid,
            'component' => self::COMPONENT_NAME,
            'filearea' => $filearea,
            'itemid' => $itemid,
            'userid' => $userid,
            'filepath' => '/',
            'filename' => $name . $itemid . '.' . $ext
        ], $pathname);

        return $newfile;
    }

    /**
     * Base method for creating file by url
     *
     * @param int $contextid context identifier.
     * @param string $itemid property of the file that is submissionid.
     * @param string $name file name.
     * @param string $ext file extension.
     * @param string $filearea file area.
     * @param string $userid user identifier.
     * @param string $url file url resource.
     *
     * @return stored_file
     */
    private static function create_by_url_base($contextid, $itemid, $name, $ext, $filearea, $userid, $url) {
        $fs = get_file_storage();

        $file = null;
        try {
            $file = $fs->create_file_from_url((object)[
                'contextid' => $contextid,
                'component' => self::COMPONENT_NAME,
                'filearea' => $filearea,
                'itemid' => $itemid,
                'userid' => $userid,
                'filepath' => '/',
                'filename' => $name . $itemid . '.' . $ext
            ], $url);
        } catch (\file_exception $e) {
            return null;
        }

        return $file;
    }
}
