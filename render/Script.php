<?php

namespace monolyth\render;
use monolyth\core\External;
use ErrorException;

class Script extends External
{
    public function __toString()
    {
        try {
            $out = '';
            foreach ($this->files as $collection) {
                foreach ($this->extractExternal($collection) as $file) {
                    $out .= sprintf(
                        '<script src="%s"></script>'."\n",
                        $file
                    );
                }
                if ($collection) {
                    $out .= sprintf(
                        '<script src="%s"></script>'."\n",
                        $this->httpimg($this->assemble($collection))
                    );
                }
            }
            return $out;
        } catch (ErrorException $e) {
            return $e->getMessage()."\n".$e->getFile()."\n".$e->getLine();
        }
    }
}

