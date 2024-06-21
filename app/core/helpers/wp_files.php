<?php

use boctulus\SW\core\libs\Env;

use boctulus\SW\core\Constants;
use boctulus\SW\core\libs\Files;
use boctulus\SW\core\libs\Strings;

/*
    @author Pablo Bozzolo < boctulus@gmail.com >
*/

function plugins_directory(){
    return Files::normalize(realpath(Constants::PLUGINS_PATH), '/');
}

function current_plugin_directory(){
    return Strings::lastSegment(Files::normalize(realpath(Constants::ROOT_PATH), '/'), '/');
}

