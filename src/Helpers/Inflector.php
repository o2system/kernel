<?php
/**
 * This file is part of the O2System Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */
// ------------------------------------------------------------------------

if ( ! function_exists('readable')) {
    /**
     * readable
     *
     * @param      $string
     * @param bool $capitalize
     *
     * @return mixed|string
     */
    function readable($string, $capitalize = false)
    {
        $string = trim($string);
        $string = str_replace(['-', '_'], ' ', $string);

        if ($capitalize == true) {
            return ucwords($string);
        }

        return $string;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('singular')) {
    /**
     * singular
     *
     * Takes a plural word and makes it singular
     *
     * @param   string $string Input string
     *
     * @return  string
     */
    function singular($string)
    {
        $result = strval($string);

        if ( ! is_countable($result)) {
            return $result;
        }

        $rules = [
            '/(matr)ices$/'                                                   => '\1ix',
            '/(vert|ind)ices$/'                                               => '\1ex',
            '/^(ox)en/'                                                       => '\1',
            '/(alias)es$/'                                                    => '\1',
            '/([octop|vir])i$/'                                               => '\1us',
            '/(cris|ax|test)es$/'                                             => '\1is',
            '/(shoe)s$/'                                                      => '\1',
            '/(o)es$/'                                                        => '\1',
            '/(bus|campus)es$/'                                               => '\1',
            '/([m|l])ice$/'                                                   => '\1ouse',
            '/(x|ch|ss|sh)es$/'                                               => '\1',
            '/(m)ovies$/'                                                     => '\1\2ovie',
            '/(s)eries$/'                                                     => '\1\2eries',
            '/([^aeiouy]|qu)ies$/'                                            => '\1y',
            '/([lr])ves$/'                                                    => '\1f',
            '/(tive)s$/'                                                      => '\1',
            '/(hive)s$/'                                                      => '\1',
            '/([^f])ves$/'                                                    => '\1fe',
            '/(^analy)ses$/'                                                  => '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/' => '\1\2sis',
            '/([ti])a$/'                                                      => '\1um',
            '/(p)eople$/'                                                     => '\1\2erson',
            '/(m)en$/'                                                        => '\1an',
            '/(s)tatuses$/'                                                   => '\1\2tatus',
            '/(c)hildren$/'                                                   => '\1\2hild',
            '/(n)ews$/'                                                       => '\1\2ews',
            '/([^us])s$/'                                                     => '\1',
        ];

        foreach ($rules as $rule => $replacement) {
            if (preg_match($rule, $result)) {
                $result = preg_replace($rule, $replacement, $result);
                break;
            }
        }

        return $result;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('plural')) {
    /**
     * plural
     *
     * Takes a singular word and makes it plural
     *
     * @param    string $string Input string
     *
     * @return    string
     */
    function plural($string)
    {
        $result = strval($string);

        if ( ! is_countable($result)) {
            return $result;
        }

        $rules = [
            '/^(ox)$/'                => '\1\2en',     // ox
            '/([m|l])ouse$/'          => '\1ice',      // mouse, louse
            '/(matr|vert|ind)ix|ex$/' => '\1ices',     // matrix, vertex, index
            '/(x|ch|ss|sh)$/'         => '\1es',       // search, switch, fix, box, process, address
            '/([^aeiouy]|qu)y$/'      => '\1ies',      // query, ability, agency
            '/(hive)$/'               => '\1s',        // archive, hive
            '/(?:([^f])fe|([lr])f)$/' => '\1\2ves',    // half, safe, wife
            '/sis$/'                  => 'ses',        // basis, diagnosis
            '/([ti])um$/'             => '\1a',        // datum, medium
            '/(p)erson$/'             => '\1eople',    // person, salesperson
            '/(m)an$/'                => '\1en',       // man, woman, spokesman
            '/(c)hild$/'              => '\1hildren',  // child
            '/(buffal|tomat)o$/'      => '\1\2oes',    // buffalo, tomato
            '/(bu|campu)s$/'          => '\1\2ses',    // bus, campus
            '/(alias|status|virus)$/' => '\1es',       // alias
            '/(octop)us$/'            => '\1i',        // octopus
            '/(ax|cris|test)is$/'     => '\1es',       // axis, crisis
            '/s$/'                    => 's',          // no change (compatibility)
            '/$/'                     => 's',
        ];

        foreach ($rules as $rule => $replacement) {
            if (preg_match($rule, $result)) {
                $result = preg_replace($rule, $replacement, $result);
                break;
            }
        }

        return $result;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('studlycase')) {
    /**
     * studlycase
     *
     * Convert a value to studly caps case (StudlyCapCase).
     *
     * @param  string $string
     *
     * @return string
     */
    function studlycase($string)
    {
        return ucfirst(camelcase($string));
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('camelcase')) {
    /**
     * camelcase
     *
     * Takes multiple words separated by spaces, underscores or dashes and camelizes them.
     *
     * @param    string $string Input string
     *
     * @return    string
     */
    function camelcase($string)
    {
        $string = trim($string);

        if (strtoupper($string) === $string) {
            return (string)$string;
        }

        return lcfirst(str_replace(' ', '', ucwords(preg_replace('/[\s_-]+/', ' ', $string))));
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('snakecase')) {
    /**
     * snakecase
     *
     * Convert camelCase into camel_case.
     *
     * @param $string
     *
     * @return string
     */
    function snakecase($string)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('underscore')) {
    /**
     * underscore
     *
     * Takes multiple words separated by spaces and underscores them
     *
     * @param    string $string Input string
     *
     * @return    string
     */
    function underscore($string)
    {
        $string = trim($string);
        $string = str_replace(['/', '\\'], '-', snakecase($string));

        $string = strtolower(preg_replace(
            ['#[\\s-]+#', '#[^A-Za-z0-9\. -]+#'],
            ['-', ''],
            $string
        ));

        return str_replace('-', '_', $string);
    }
}


// ------------------------------------------------------------------------

if ( ! function_exists('dash')) {
    /**
     * dash
     *
     * Takes multiple words separated by spaces and dashes them
     *
     * @param    string $string Input string
     *
     * @access  public
     * @return  string
     */
    function dash($string)
    {
        $string = trim($string);
        $string = str_replace(['/', '\\'], '-', snakecase($string));

        $string = strtolower(preg_replace(
            ['#[\\s_-]+#', '#[^A-Za-z0-9\. _-]+#'],
            ['_', ''],
            $string
        ));

        return str_replace('_', '-', $string);
    }
}


// ------------------------------------------------------------------------

if ( ! function_exists('is_countable')) {
    /**
     * is_countable
     *
     * Checks if the given word has a plural version.
     *
     * @param    string $string Word to check
     *
     * @access  public
     * @return  bool
     */
    function is_countable($string)
    {
        return ! in_array(
            strtolower($string),
            [
                'equipment',
                'information',
                'rice',
                'money',
                'species',
                'series',
                'fish',
                'meta',
            ]
        );
    }
}