<?php

namespace monolyth\render;
use ErrorException;

class Array_Script extends Script
{
    public function __toString()
    {
        try {
            $out = [];
            foreach ($this->files as $collection) {
                foreach ($this->extractExternal($collection) as $file) {
                    $out[] = "'$file'";
                }
                if ($collection) {
                    $out[] = sprintf(
                        "'%s'",
                        $this->httpimg($this->assemble($collection))
                    );
                }
            }
            return implode(',', $out);
        } catch (ErrorException $e) {
            return $e->getMessage()."\n".$e->getFile()."\n".$e->getLine();
        }
    }

    public function getArrayCopy()
    {
        $out = [];
        foreach ($this->files as $collection) {
            $out[] = $this->httpimg($this->assemble($collection));
        }
        return $out;
    }
}

