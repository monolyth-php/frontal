<?php

namespace monolyth\render;
use monolyth\core\External;
use Exception;

class Css extends External
{
    use Css_Parser;

    public function __toString()
    {
        $out = '';
        try {
            foreach ($this->files as $collection) {
                foreach ($this->extractExternal($collection) as $file) {
                    $out .= sprintf(
                        '<link rel="stylesheet" href="%s">'."\n",
                        $file
                    );
                }
                if ($collection) {
                    $out .= sprintf(
                        '<link rel="stylesheet" href="%s">'."\n",
                        $this->httpimg($this->assemble($collection))
                    );
                }
            }
        } catch (Exception $e) {
            $out = $e->getMessage();
        }
        return $out;
    }
}

