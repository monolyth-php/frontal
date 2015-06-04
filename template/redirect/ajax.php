<?php

/**
 * The redirect page template for ajax calls.
 * Returns a json object of the form {"redirect":"http://your-new-location"}.
 * The calling script is responsible for handling this.
 *
 * @package monolyth
 */

namespace monolyth;
header("Content-type: application/json", true);
echo json_encode(['redirect' => $url]);

