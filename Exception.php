<?php

namespace AP\Emoji;

use Exception as Base;

/**
 * Class Exception
 * @package AP\Emoji
 */
class Exception extends Base
{
    /**
     * Show execption
     * @param $character
     * @return Exception
     */
    public static function create($character)
    {
        return new static("Character `{$character}` does not exist");
    }
}