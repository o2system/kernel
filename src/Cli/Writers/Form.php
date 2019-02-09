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

namespace O2System\Kernel\Cli\Writers;

// ------------------------------------------------------------------------

/**
 * Class Form
 *
 * @package O2System\Kernel\Cli\Writers
 */
class Form
{
    /**
     * Form::text
     *
     * Create a cli text form input.
     *
     * @param string             $name
     * @param string|Format|Text $question
     * @param bool               $required
     */
    public function text($name, $question, $required = false)
    {
        if (is_string($question)) {
            output()->write(
                (new Format())
                    ->setString(language()->getLine($question))
                    ->setSpace(1)
            );
        } elseif ($question instanceof Format or $question instanceof Text) {
            output()->write($question);
        }

        $standardInput = input()->standard();

        if ($required === true and empty($standardInput)) {
            $requiredText = (new Format())
                ->setColor((new Color())->setBackground(Color::RED))
                ->setString(' ' . language()->getLine('REQUIRED') . ' ')
                ->setIndent(1)
                ->__toString();

            $this->text($name, str_replace($requiredText, '', $question) . $requiredText, $required);

            return;
        }

        $_POST[ $name ] = $standardInput;
    }

    // ------------------------------------------------------------------------

    /**
     * Form::confirm
     *
     * Create a cli confirmation form input.
     *
     * @param string             $name
     * @param string|Format|Text $question
     * @param bool               $required
     */
    public function confirm($name, $question, $required = false)
    {
        if (is_string($question)) {
            output()->write(
                (new Format())
                    ->setString(rtrim(language()->getLine($question), '?') . ' (Y/N) ?')
                    ->setSpace(1)
            );
        } elseif ($question instanceof Format or $question instanceof Text) {
            output()->write($question . '?');
        }

        $standardInput = strtoupper(input()->standard());

        if ($required === true and ! in_array($standardInput, ['Y', 'N'])) {
            $requiredText = (new Format())
                ->setColor((new Color())->setBackground(Color::RED))
                ->setString(' ' . language()->getLine('REQUIRED') . ' ')
                ->setIndent(1)
                ->__toString();

            $this->confirm($name, str_replace($requiredText, '', $question) . $requiredText, $required);

            return;
        }

        $_POST[ $name ] = (bool)($standardInput === 'Y');
    }

    // ------------------------------------------------------------------------

    /**
     * Form::choices
     *
     * Create a cli choices form input
     *
     * @param string             $name
     * @param string|Format|Text $question
     * @param array              $options
     * @param bool               $required
     */
    public function options($name, $question, array $options, $required = false)
    {
        output()->write(PHP_EOL);

        $choices = [];
        $i = 1;
        foreach ($options as $value => $label) {
            $choices[ $i ] = $value;
            output()->write(
                (new Format())
                    ->setString('(' . $i . ') ' . language()->getLine($label))
                    ->setNewLinesAfter(1)
            );
            $i++;
        }

        //output()->write( PHP_EOL );

        if (is_string($question)) {
            output()->write(
                (new Format())
                    ->setString(language()->getLine($question) . ':')
                    ->setSpace(1)
            );
        } elseif ($question instanceof Format or $question instanceof Text) {
            output()->write($question);
        }

        $requiredText = (new Format())
            ->setColor((new Color())->setBackground(Color::RED))
            ->setString(' ' . language()->getLine('REQUIRED') . ' ')
            ->setIndent(1)
            ->__toString();

        $invalidText = (new Format())
            ->setColor((new Color())->setBackground(Color::RED))
            ->setString(' ' . language()->getLine('INVALID') . ' ')
            ->setIndent(1)
            ->__toString();

        $question = str_replace([$requiredText, $invalidText], '', $question);

        $standardInput = input()->standard();

        if ($required === true and empty($standardInput)) {
            $this->options($name, $question . $requiredText, $options, $required);

            return;
        } elseif ( ! array_key_exists($standardInput, $choices)) {
            $this->options($name, $question . $invalidText, $options, $required);

            return;
        }

        $_POST[ $name ] = $choices[ $standardInput ];
    }
}