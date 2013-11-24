<?php

namespace monolyth\render;
use ErrorException;

/**
 * Render version of styles for older Internet Explorers (pre-9, which
 * don't support media queries).
 */
class Oldie_Css extends Css
{
    public function __toString()
    {
        if (!$this->files) {
            return '';
        }
        try {
            $out = parent::__toString();
            ob_start();
            $toarray = function($css) {
                $rules = [];
                preg_match_all("@([^}]+?){(.*?)}@ms", $css, $matches);
                foreach ($matches[1] as $i => $selector) {
                    if (!isset($rules[$selector])) {
                        $rules[$selector] = [];
                    }
                    foreach (explode(';', $matches[2][$i]) as $rule) {
                        $parts = explode(':', $rule);
                        if ($parts[0]{0} == '-'
                            && strpos($parts[0], 'ms-') !== 0
                        ) {
                            // Ignore any vendor-specific rule that isn't
                            // prefixed by ms- (MicroSoft).
                            continue;
                        }
                        try {
                            $rules[$selector][$parts[0]] = $parts[1];
                        } catch (ErrorException $e) {
                        }
                    }
                }
                return $rules;
            };
            $scripts = [];
            echo "<!--[if lt IE 9]>\n";
            preg_match_all('@href="(.*?)"@m', $out, $files);
            $project = $this->project->export();
            foreach ($files[1] as $file) {
                $src = substr($file, strrpos($file, '/css') + 5);
                if (substr($src, 0, 3) != 'mg.') {
                    continue;
                }
                $target = str_replace('mg.', 'ie.', $src);
                if (!file_exists("{$project['public']}/css/$target")) {
                    try {
                        $data = file_get_contents(
                            "{$project['public']}/css/$src"
                        );
                    } catch (ErrorException $e) {
                        continue;
                    }
                    $rules = $toarray($data);
                    foreach ($rules as $selector => $rule) {
                        if (strpos($selector, 'nth-child')
                            || strpos($selector, 'last-child')
                            || isset($rule['opacity'])
                        ) {
                            continue;
                        }
                        // Don't redo "simple" rules.
                        unset($rules[$selector]);
                    }
                    if (preg_match_all(
                        "/@media only screen.*?{(.*?})}/ms",
                        $data,
                        $matches
                    )) {
                        foreach ($toarray(implode(
                            '',
                            $matches[1]
                        )) as $selector => $rls) {
                            if (!isset($rules[$selector])) {
                                $rules[$selector] = [];
                            }
                            $rules[$selector] = $rls + $rules[$selector];
                        }
                    }
                    foreach ($rules as $selector => $rule) {
                        $newsel = $selector;
                        foreach (explode(',', $selector) as $sel) {
                            if (strpos($sel, ':nth-child') !== false) {
                                $class = '';
                                $new = preg_replace_callback(
                                    '@^(.*?):nth-child\((.*?)\)@',
                                    function($match) use(&$class) {
                                        $class = 'nth-child_'.preg_replace(
                                            "@\W@",
                                            '-',
                                            $match[2]
                                        );
                                        return sprintf(
                                            '%s.%s',
                                            $match[1],
                                            $class
                                        );
                                    },
                                    $sel
                                );
                                if (!isset($rules[$new])) {
                                    $rules[$new] = [];
                                }
                                $rules[$new] =& $rule;
                                $scripts[] = sprintf(
                                    "$('%s').addClass('%s');",
                                    $sel,
                                    $class
                                );
                                $newsel = str_replace($sel, $new, $newsel);
                            }
                        }
                        $new = [];
                        foreach ($rule as $prop => $val) {
                            switch ($prop) {
                                case 'opacity':
                                    $new['-ms-filter'] = sprintf(
                                        '"progid:'
                                        .'DXImageTransform.Microsoft.'
                                        .'Alpha(opacity=%d)"',
                                        $rule['opacity'] * 100
                                    );
                                    break;
                            }
                        }
                        if ($new) {
                            $rules[$selector] = $new;
                        } else {
                            unset($rules[$selector]);
                        }
                        if ($newsel != $selector) {
                            $rules[$newsel] = $rule;
                            unset($rules[$selector]);
                        }
                    }
                    foreach ($rules as $selector => $rule) {
                        if (!$rule) {
                            $rule = '';
                            continue;
                        }
                        try {
                            foreach ($rule as $prop => $val) {
                                $rule[$prop] = "$prop:$val";
                            }
                            $rule = sprintf(
                                '%s{%s}',
                                $selector,
                                implode(';', $rule)
                            );
                        } catch (ErrorException $e) {
                            $rule = "$selector{$rule}";
                        }
                        $rules[$selector] = $rule;
                    }
                    $result = implode('', $rules);
                    file_put_contents(
                        "{$project['public']}/css/$target",
                        $result
                    );
                }
                echo sprintf(
                    '<link rel="stylesheet" href="%s">'."\n",
                    $this->httpimg("/css/$target")
                );
            }
            $jsname = str_replace('mg.', 'ie.', $target);
            if ($scripts) {
                file_put_contents(
                    sprintf(
                        '%s/js/%s',
                        $project['public'],
                        $jsname
                    ),
                    sprintf(
                        'window.Monolyth.scripts.push(function(){%s})',
                        implode('', $scripts)
                    )
                );
            }
            if (file_exists(sprintf('%s/js/%s', $project['public'], $jsname))) {
                printf(
                    '<script src="%s"></script>',
                    $this->httpimg("/js/$jsname")
                );
            }
            echo "\n<![endif]-->\n";
        } catch (\Exception $e) {
            echo $e->getMessage().$e->getLine();
        }
        return "$out\n".ob_get_clean();
    }
}

