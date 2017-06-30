<?php

namespace AP\Emoji;

/**
 * Class Emoji
 * @package AP\Emoji
 */
class Emoji
{
    /**
     * Hold array from emojis.json
     * @var
     */
    private static $emojis;

    /**
     * Emoji data-image providers
     * @var array
     */
    private static $providers = ['chart', 'apple', 'twitter', 'one', 'google', 'samsung', 'wind', 'gmail', 'sb', 'dcm', 'kddi'];

    /**
     * Get the named list of all emojis or by specific provider
     * Emoji::all()
     * OR
     * Emoji::all('google') returns all emojis provided by google as [NAME => emoji]
     * OR
     * Emoji::all('google', 20) returns first 20 emojis provided by google
     * OR
     * Emoji::all('google', 20, 20) returns 20 emojis, started from 20th
     * @return string
     */
    public static function all($parameter = null, $size = null, $from = 0)
    {
        static::init();

        $response = [];

        foreach(static::$emojis as $name => $value){
            if($parameter){
                $response[$name] = $value[$parameter];
            }else{
                $response[] = $name;
            }
        }

        if($size){
            $response = array_slice($response, $from, abs($size), true);
        }

        return $response;
    }

    /**
     * Get specific emoji
     * Emoji::get('cryingFace')
     * OR
     * Emoji::get(['cryingFace', 'flagForIndia'])
     * OR
     * Emoji::get(['cryingEyes' => 'code', 'flagForIndia' => ['code', 'samsung']])
     * @param $names
     * @return string|void
     */
    public static function get($names)
    {
        $response = [];

        if(!is_array($names)){
            return static::getEmoji($names);
        }

        foreach ($names as $key => $value){
            $key = is_numeric($key) ? $value : $key;
            $value = $value == $key ? 'chart' : $value;

            if($getEmoji = static::getEmoji($key, $value)){
                $response[$key] = $getEmoji;
            }
        }

        return json_encode($response);
    }

    /**
     * Fetching the required emojis with specific parameter from static::$emoji
     * returns execption if emoji not found
     * @param $name
     * @param string $parameters
     * @return string|void
     */
    protected static function getEmoji($name, $parameters = 'chart')
    {
        static::init();

        $emoji = static::convertNameToEmoji($name);

        if(!isset(static::$emojis[$emoji])) {
            return static::execption($emoji);
        }

        if(!$parameters){
            $parameters = 'chart';
        }

        if(is_array($parameters) && count($parameters) > 0){

            if(count($parameters) == 1){
                if(array_key_exists($parameters[0], static::$emojis[$emoji])){
                    return static::$emojis[$emoji][$parameters[0]];
                }
                return static::execption($emoji, $parameters[0]);
            }

            $response = [];
            foreach($parameters as $parameter){
                if(array_key_exists($parameter, static::$emojis[$emoji])){
                    $response[$parameter] = static::$emojis[$emoji][$parameter];
                }
                return static::execption($emoji, $parameter);
            }

            return json_encode($response);
        }

        return static::autoFound($emoji, $parameters);
    }

    /**
     * Emoji Name as a function
     * @param $methodName
     * @param $paramters
     * @return string|void
     */
    public static function __callStatic($methodName, $paramters)
    {
        return static::getEmoji($methodName, $paramters);
    }

    /**
     * Convert provided emoji name to default
     * @param $characterName
     * @return string
     */
    protected static function convertNameToEmoji($characterName)
    {
        $partialConstantName = static::convertToSnakeCase($characterName);

        return strtoupper($partialConstantName);
    }

    /**
     * Replace provided emoji name with default
     * @param $value
     * @return mixed|string
     */
    protected static function convertToSnakeCase($value)
    {
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/', '', $value);

            $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1'.'_', $value));
        }

        return $value;
    }

    /**
     * Initially load and hold emoji.json array to static::$emoji
     */
    protected static function init()
    {
        if(sizeof(static::$emojis) == 0){
            static::$emojis = json_decode(file_get_contents(__DIR__ . '/emojis.json'), true);
        }
    }

    /**
     * Automatically found and return another provider is required provider is null
     * @param $emoji
     * @param $parameters
     * @return mixed
     */
    protected static function autoFound($emoji, $parameters)
    {
        if(!static::$emojis[$emoji][$parameters]){
            foreach(static::$providers as $provider){
                if(static::$emojis[$emoji][$provider]){
                    $parameters = $provider;
                    break;
                }
            }
        }

        return static::$emojis[$emoji][$parameters];
    }

    /**
     * Show execption when emoji is not found
     * @param $emoji
     * @param bool $parameter
     * @throws Exception
     */
    protected static function execption($emoji, $parameter = false)
    {
        throw Exception::create($parameter ? $emoji . ' with ' . $parameter : $emoji);
    }
}