<?php

/**
 * Media model for file-based storage.
 */
namespace monolyth;
use monolyth\adapter\sql\InsertNone_Exception;
use monolyth\adapter\sql\UpdateNone_Exception;
use monolyth\adapter\sql\DeleteNone_Exception;
use ErrorException;

class File_Media_Model extends core\Model implements User_Access
{
    public function create(array $file, $folder = null, $owner = null)
    {
        if (!isset($owner)) {
            $owner = $this->user->id();
        }
        if (!$owner) {
            return 'owner';
        }
        $md5 = md5(file_get_contents($file['tmp_name']));
        $mime = mime_content_type($file['tmp_name']);
        try {
            $this->adapter->insert(
                'monolyth_media',
                [
                    'filename' => $file['tmp_name'],
                    'originalname' => $file['name'],
                    'md5' => $md5,
                    'filesize' => $file['size'],
                    'owner' => $owner,
                    'mimetype' => $mime,
                    'folder' => $folder,
                ]
            );
            $id = $this->adapter->lastInsertId('monolyth_media_id_seq');
            $parts = str_split($id, 3);
            $name = array_pop($parts);
            if ($parts) {
                $dir = $this->config->uploadPath.'/'.implode('/', $parts);
            } else {
                $dir = $this->config->uploadPath;
            }
            try {
                mkdir($dir, 0777, true);
            } catch (ErrorException $e) {
            }
            $ext = substr($mime, strrpos($mime, '/') + 1);
            rename($file['tmp_name'], "$dir/$name.$ext");
            chmod("$dir/$name.$ext", 0644);
            $this->adapter->update(
                'monolyth_media',
                ['filename' => "$dir/$name.$ext"],
                compact('id')
            );
            $this->load($this->adapter->row(
                'monolyth_media',
                '*',
                compact('id')
            ));
            return null;
        } catch (InsertNone_Exception $e) {
            try {
                $this->adapter->update(
                    'monolyth_media',
                    compact('folder'),
                    compact('md5')
                );
            } catch (UpdateNone_Exception $e) {
            }
            $this->load($this->adapter->row(
                'monolyth_media',
                '*',
                compact('md5')
            ));
            return null;
        }
    }

    public function move($id, $folder)
    {
        try {
            $owner = $this->user->id();
            $this->adapter->update(
                'monolyth_media',
                compact('folder'),
                compact('id', 'owner')
            );
            return null;
        } catch (UpdateNone_Exception $e) {
            return 'nochange';
        }
    }

    public function delete()
    {
        try {
            $this->adapter->delete('monolyth_media', ['id' => $this['id']]);
        } catch (DeleteNone_Exception $e) {
        }
        try {
            unlink($this['filename']);
        } catch (ErrorException $e) {
        }
        try {
            $parts = explode(DIR_SEPARATOR, dirname($this['filename']));
            // Recursively remove directories when empty.
            while ($parts) {
                $d = Dir(implode(DIR_SEPARATOR, $parts));
                $files = false;
                while (false !== ($entry = $d->read())) {
                    if ($entry != '.' && $entry != '..') {
                        $files = true;
                        break;
                    }
                }
                if (!$files) {
                    rmdir(implode(DIR_SEPARATOR, $parts));
                } else {
                    break;
                }
                array_pop($parts);
            }
        } catch (ErrorException $e) {
        }
    }
}

