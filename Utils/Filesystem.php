<?php

/**
 * Some file system related utilities.
 *
 * @package monolyth
 * @subpackage utils
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2012
 */

namespace monolyth\utils;
use ErrorException;

/** File system utilities. */
trait Filesystem
{
    /**
     * Recursively return directory contents.
     *
     * @param string $dir The starting directory.
     * @param int $levels Number of levels to go deep. -1 means unlimited.
     * @param string $match Only return files matching this regex.
     * @return array Array of directory contents.
     */
    public static function getFiles($dir, $levels = -1, $match = '.*')
    {
        try {
            $d = Dir($dir);
            $entries = [];
            while (false !== ($entry = $d->read())) {
                if ($entry{0} == '.') {
                    continue;
                }
                if (is_dir("$dir/$entry")) {
                    if ($levels > 0 || $levels == -1) {
                        $entries["$dir/$entry"] = self::getfiles(
                            "$dir/$entry",
                            $levels == -1 ? -1 : $levels - 1,
                            $match
                        );
                    }
                } elseif (preg_match("@^$match$@", $entry)) {
                    $entries["$dir/$entry"] = $entry;
                }
            }
            ksort($entries);
            return $entries;
        } catch (ErrorException $e) {
            return [];
        }
    }

    /**
     * Return all directories within the specified path.
     * Optional parameter $match makes sure only directories with
     * a name matching its regex are returned.
     *
     * @param string $dir The path to query.
     * @param string $match A regular expression paths should match.
     * @return array An array of directories (unsorted).
     */
    public static function getDirectories($dir, $match = '.*')
    {
        try {
            $d = Dir($dir);
            $entries = [];
            while (false !== ($entry = $d->read())) {
                if (
                    $entry{0} == '.' ||
                    !is_dir("$dir/$entry") ||
                    !preg_match("@^$match$@", $entry)
                ) {
                    continue;
                }
                $entries[] = $entry;
            }
            return $entries;
        } catch (ErrorException $e) {
            return [];
        }
    }
}

