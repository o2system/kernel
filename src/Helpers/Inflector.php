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
        $string = str_replace('_', ' ', underscore($string));

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
     * @param string $string Input string
     *
     * @return  string
     */
    function singular($string)
    {
        $result = strval($string);
        if ( ! is_pluralizable($result)) {
            return $result;
        }

        //Arranged in order.
        $singularRules = [
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
            '/(quiz)zes$/'                                                    => '\1',
            '/([^us])s$/'                                                     => '\1',
        ];
        foreach ($singularRules as $rule => $replacement) {
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
     * @param string $string Input string
     *
     * @return    string
     */
    function plural($string)
    {
        $result = strval($string);
        if ( ! is_pluralizable($result)) {
            return $result;
        }
        $pluralRules = [
            '/(quiz)$/'               => '\1zes',    // quizzes
            '/^(ox)$/'                => '\1\2en', // ox
            '/([m|l])ouse$/'          => '\1ice', // mouse, louse
            '/(matr|vert|ind)ix|ex$/' => '\1ices', // matrix, vertex, index
            '/(x|ch|ss|sh)$/'         => '\1es', // search, switch, fix, box, process, address
            '/([^aeiouy]|qu)y$/'      => '\1ies', // query, ability, agency
            '/(hive)$/'               => '\1s', // archive, hive
            '/(?:([^f])fe|([lr])f)$/' => '\1\2ves', // half, safe, wife
            '/sis$/'                  => 'ses', // basis, diagnosis
            '/([ti])um$/'             => '\1a', // datum, medium
            '/(p)erson$/'             => '\1eople', // person, salesperson
            '/(m)an$/'                => '\1en', // man, woman, spokesman
            '/(c)hild$/'              => '\1hildren', // child
            '/(buffal|tomat)o$/'      => '\1\2oes', // buffalo, tomato
            '/(bu|campu)s$/'          => '\1\2ses', // bus, campus
            '/(alias|status|virus)$/' => '\1es', // alias
            '/(octop)us$/'            => '\1i', // octopus
            '/(ax|cris|test)is$/'     => '\1es', // axis, crisis
            '/s$/'                    => 's', // no change (compatibility)
            '/$/'                     => 's',
        ];
        foreach ($pluralRules as $rule => $replacement) {
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
     * @param string $string
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
     * @param string $string Input string
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

if (! function_exists('pascalcase'))
{
    /**
     * pascalcase
     *
     * Takes multiple words separated by spaces or
     * underscores and converts them to Pascal case,
     * which is camel case with an uppercase first letter.
     *
     * @param  string $string Input string
     * @return string
     */
    function pascalcase(string $string): string
    {
        return ucfirst(camelcase($string));
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
     * @param string $string Input string
     *
     * @return    string
     */
    function underscore($string)
    {
        $string = trim($string);
        $string = str_replace(['/', '\\'], '-', snakecase($string));

        $string = strtolower(preg_replace(
            ['#[\\s-]+#', '#[^A-Za-z0-9\. -]+#', '/[\s]+/', '/-+/', '/_+/'],
            ['-', '', '_', '_', '_'],
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
     * @param string $string Input string
     *
     * @access  public
     * @return  string
     */
    function dash($string)
    {
        return str_replace('_', '-', underscore($string));
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('counted')) {
    /**
     * Counted
     *
     * Takes a number and a word to return the plural or not
     * E.g. 0 cats, 1 cat, 2 cats, ...
     *
     * @param integer $count  Number of items
     * @param string  $string Input string
     *
     * @return string
     */
    function counted(int $count, string $string): string
    {
        $result = "{$count} ";
        $result .= $count === 1 ? singular($string) : plural($string);

        return $result;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('humanize')) {
    /**
     * Humanize
     *
     * Takes multiple words separated by the separator,
     * camelizes and changes them to spaces
     *
     * @param string $string    Input string
     * @param string $separator Input separator
     *
     * @return string
     */
    function humanize(string $string, string $separator = '_'): string
    {
        $replacement = trim($string);
        $upperCased = ucwords
        (
            preg_replace('/[' . $separator . ']+/', ' ', $replacement)
        );

        return $upperCased;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_pluralizable')) {
    /**
     * Checks if the given word has a plural version.
     *
     * @param string $word Word to check
     *
     * @return boolean
     */
    function is_pluralizable(string $word): bool
    {
        $uncountables = in_array
        (
            strtolower($word), [
            'advice',
            'bravery',
            'butter',
            'chaos',
            'clarity',
            'coal',
            'courage',
            'cowardice',
            'curiosity',
            'education',
            'equipment',
            'evidence',
            'fish',
            'fun',
            'furniture',
            'greed',
            'help',
            'homework',
            'honesty',
            'information',
            'insurance',
            'jewelry',
            'knowledge',
            'livestock',
            'love',
            'luck',
            'marketing',
            'meta',
            'money',
            'mud',
            'news',
            'patriotism',
            'racism',
            'rice',
            'satisfaction',
            'scenery',
            'series',
            'sexism',
            'silence',
            'species',
            'spelling',
            'sugar',
            'water',
            'weather',
            'wisdom',
            'work',
            'sys'
        ]);

        return ! $uncountables;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('ordinal')) {
    /**
     * ordinal
     *
     * Returns the suffix that should be added to a
     * number to denote the position in an ordered
     * sequence such as 1st, 2nd, 3rd, 4th.
     *
     * @param integer $integer The integer to determine
     *                         the suffix
     *
     * @return string
     */
    function ordinal(int $integer): string
    {
        $suffixes = [
            'th',
            'st',
            'nd',
            'rd',
            'th',
            'th',
            'th',
            'th',
            'th',
            'th',
        ];

        return $integer % 100 >= 11 && $integer % 100 <= 13 ? 'th' : $suffixes[ $integer % 10 ];
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('ordinalize')) {
    /**
     * ordinalize
     *
     * Turns a number into an ordinal string used
     * to denote the position in an ordered sequence
     * such as 1st, 2nd, 3rd, 4th.
     *
     * @param integer $integer The integer to ordinalize
     *
     * @return string
     */
    function ordinalize(int $integer): string
    {
        return $integer . ordinal($integer);
    }
}