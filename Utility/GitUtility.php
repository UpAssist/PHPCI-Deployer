<?php
namespace UpAssist\PHPCI\Deployer\Utility;

/**
 * Class GitUtility
 *
 * @category Class
 * @package UpAssist\PHPCI\Deployer
 * @author Henjo Hoeksma <henjo@upassist.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link http://www.upassist.com
 */
class GitUtility
{

    /**
     * @param string $directory
     * @return string
     */
    public function getCurrentRepositoryUri($directory = __DIR__)
    {
        exec("cd '$directory'; git remote -v", $lines);
        foreach ($lines as $line) {
            $gitInfo = explode(' ', preg_replace('/\s+/', ' ', $line));
            if ($gitInfo[0] === 'origin' && $gitInfo[2] === '(fetch)') {
                return $gitInfo[1];
            }
        }

        return null;
    }
}