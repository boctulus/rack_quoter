<?php

namespace boctulus\SW\core\interfaces;

interface ICommand {
    function handle($args);
}