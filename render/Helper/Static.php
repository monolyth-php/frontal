<?php

namespace monolyth\render;
use monolyth\Project_Access;

trait Static_Helper
{
    use Project_Access {
        Project_Access::project as staticProject;
    }

    public function assemble(array $files)
    {
        // Build a unified unique identifier for this file.
        // The 'UUID' consists of the total filesize of the files,
        // as well as an MD5 of the concatenated filenames.
        $project = self::staticProject()->export();
        $size = 0;
        clearstatcache();
        foreach ($files as $file) {
            $required = true;
            if (substr($file, -1) == '?') {
                $file = substr($file, 0, -1);
                $required = false;
            }
            if (!isset($ext)) {
                $ext = substr($file, strrpos($file, '.') + 1);
            }
            if (substr($file, strrpos($file, '.') + 1) != $ext) {
                throw new ExtensionMismatch_Exception($ext);
            }
            try {
                $size += strlen(file_get_contents($file, true));
            } catch (ErrorException $e) {
                if ($required) {
                    throw new FileNotFound_Exception($file);
                }
            }
        }
        if (!$size) {
            throw new EmptyStaticFile_Exception($file);
        }
        $basename = md5(implode('', $files));
        if ($project['secure']) {
            $basename = "s$basename";
        }
        $target = "{$project['public']}/$ext/mg.$basename.$size.$ext";
        $sync = false;
        if (!file_exists($target)
            || ($project['test'] && isset($_GET['debug']))
        ) {
            $sync = true;
            $dspec = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'a']];
            $outs = [proc_open(
                $project->cat,
                $dspec,
                $pipes
            )];
            foreach ($files as $file) {
                $required = true;
                if (substr($file, -1) == '?') {
                    $file = substr($file, 0, -1);
                    $required = false;
                }
                try {
                    $data = file_get_contents($file, true);
                } catch (ErrorException $e) {
                    if ($required) {
                        throw new FileNotFound_Exception($file);
                    }
                }
                if (method_exists($this, 'parse')) {
                    $data = $this->parse($file, $data);
                }
                foreach (str_split($data, 1024) as $line) {
                    fwrite($pipes[0], $line);
                }
            }
            fclose($pipes[0]);
            if ($project['test']
                && isset($_GET['debug'])
                && $_GET['debug'] == 'nocompact'
            ) {
                file_put_contents($target, $pipes[1]);
            } else {
                $i = 0;
                $commands = [sprintf($project->compacter, $ext), 'write'];
                if ($ext == 'css') {
                    $vars = str_replace(
                        '%s',
                        '%%s',
                        isset($project->variables) ?
                            $project->variables :
                            'output/%s/variables.php'
                    );
                    array_unshift(
                        $commands,
                        sprintf(
                            "php %s %s/bin/variables $ext $vars",
                            sprintf("-d include_path='%s'", get_include_path()),
                            realpath(__DIR__.'/../../')
                        )
                    );
                }
                foreach ($commands as $command) {
                    list($tmp, $pipes) = call_user_func(
                        function($w) use($command, $dspec, $target, $i) {
                            if ($command == 'write') {
                                file_put_contents($target, $w);
                                fclose($w);
                                return [null, null];
                            } else {
                                $out = proc_open($command, $dspec, $pipes);
                                $data = stream_get_contents($w);
                                foreach (str_split($data, 1024) as $line) {
                                    fwrite($pipes[0], $line);
                                }
                                fclose($w);
                                fclose($pipes[0]);
                                return [$out, $pipes];
                            }
                        },
                        $pipes[1]
                    );
                    if (isset($tmp)) {
                        $outs[$command] = $tmp;
                    }
                }
            }
            foreach ($outs as $resource) {
                $exit = proc_close($resource);
                if ($exit == -1) {
                    throw new \Exception("assemblePublicFiles");
                }
            }
        }
        // Auto-clean:
        chdir("{$project['public']}/$ext");
        if ($files = glob("{mg,ie}.$basename.*.$ext", GLOB_BRACE)) {
            foreach ($files as $file) {
                if ($file == "mg.$basename.$size.$ext"
                    || $file == "ie.$basename.$size.$ext"
                    || $file == "ie.$basename.$size.js"
                ) {
                    continue;
                }
                unlink($file);
            }
        }
        return "/$ext/mg.$basename.$size.$ext";
    }

    public function httpimg($file, $site = null, $secure = null)
    {
        $file = "$file";
        $project = self::staticProject()->export();
        if (!isset($secure)) {
            $secure = $project['secure'];
        }
        // If not set: just passthru.
        if (!$secure
            && !($project->staticServers && $project->staticDomain)
        ) {
            return $file;
        }
        if ($secure
            && !($project->staticSecureServers && $project->staticSecureDomain)
        ) {
            return $file;
        }
        // Remove any prepended domains, which can easily happen if we're on a
        // multi-domain site (e.g. images are in example.com, current code in
        // uk.example.com - it should just be stripped).
        $file = preg_replace("@https?://([a-z0-9\.-]+)?/@", '/', $file);
        // Calculate a unique "hash" number for this file. This is done by
        // adding up all ordinal values from all characters. The resulting
        // number is modded by the number of static servers, with the result
        // acting as an index for the server pool.
        $i = 0;
        for ($j = 0; $j < strlen($file); $j++) {
            $i += ord($file{$j});
        }
        
        $nr = $i % count($secure ?
            $project->staticSecureServers :
            $project->staticServers
        );
        $domain = $secure ?
            $project->staticSecureDomain :
            $project->staticDomain;
        return sprintf(
            '%s://%s.%s%s%s',
            $secure ? $project['protocols'] : $project['protocol'],
            $secure ?
                $project->staticSecureServers[$nr] :
                $project->staticServers[$nr],
            $domain,
            $secure ?
                '' :
                '/'.(isset($site) ? $site : $project['site']),
            $file
        );
    }

    public function extractExternal(&$files)
    {
        $external = [];
        foreach ($files as $key => $file) {
            if (substr($file, 0, 7) == 'http://'
                || substr($file, 0, 8) == 'https://'
            ) {
                $external[] = $file;
                unset($files[$key]);
            }
        }
        return $external;
    }
}

