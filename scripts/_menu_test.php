<?php

use boctulus\SW\core\libs\Menus;

if (is_cli()){
    return;
}


Menus::tree('', 'Super Menu', null, null, null, function() {
    echo 'TOP LEVEL';
}, [
    [
        'Sub 1',
        function()
        {
            dd('L2-1');
        }
    ],
    [
        'Sub 2',
        function()
        {
            dd('L2-2');
        }
    ]
]);
