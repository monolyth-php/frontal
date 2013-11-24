<?php

namespace monolyth\render;
header("Content-type: {$i['mime']}");
readfile($imagefile);

