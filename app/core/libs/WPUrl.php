<?php

namespace boctulus\SW\core\libs;

use boctulus\SW\core\libs\Strings;

class WPUrl
{
    static function getSlug(int $post_id){
        return Strings::slug(get_post_field('post_title', $post_id));
    }

}

