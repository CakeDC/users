<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Console\Traits;

trait CommonQuestionsTrait
{
    /**
     * Ask a yes/no question and proceed if yes
     *
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @param string $question question
     * @param string $default default answer
     * @param array $callableYes callable array to run if yes
     * @param array $callableNotInteractive callable array to run if not interactive shell
     * @return void
     */
    public static function yesNo($io, $question, $default, $callableYes, $callableNotInteractive = null)
    {
        // ask if the permissions should be changed
        if ($io->isInteractive()) {
            $validator = function ($arg) {
                if (in_array($arg, ['Y', 'y', 'N', 'n'])) {
                    return $arg;
                }
                throw new Exception('This is not a valid answer. Please choose Y or n.');
            };
            $setFolderPermissions = $io->askAndValidate(
                $question,
                $validator,
                10,
                $default
            );

            if (in_array($setFolderPermissions, ['Y', 'y']) && count($callableYes === 2)) {
                call_user_func_array($callableYes[0], $callableYes[1]);
            }
        } elseif (count($callableNotInteractive) === 2) {
            call_user_func_array($callableNotInteractive[0], $callableNotInteractive[1]);
        }
    }
}
