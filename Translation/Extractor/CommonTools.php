<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jiri
 * Date: 9/3/2014
 * Time: 12:47 PM
 */

namespace JMS\TranslationBundle\Translation\Extractor;


class CommonTools
{
    /**
     * determine if the string starts with another string
     *
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }
} 