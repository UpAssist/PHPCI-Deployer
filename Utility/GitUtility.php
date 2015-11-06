<?php
namespace UpAssist\Deployer\Utility;

/**
 * Class GitUtility
 * @package UpAssist\Deployer\Utility
 */
class GitUtility
{

    /**
     * @param string $directory
     * @return string
     */
    public function getActiveBranch($directory = __DIR__)
    {
        exec("cd '$directory'; git branch", $lines);

        $branch = '';
        foreach ($lines as $line) {
            if (strpos($line, '*') === 0) {
                $branch = ltrim($line, '* ');
                break;
            }
        }

        return $branch;
    }


    /**
     * @param string $directory
     * @return string
     */
    public function getRepositoryUri($directory = __DIR__)
    {
        exec("cd '$directory'; git remote -v", $lines);
        foreach ($lines as $line) {
            $gitInfo = explode(' ', $line);
            if ($gitInfo[0] === 'origin' && $gitInfo[2] === '(fetch)') {
                return $gitInfo[1];
            }
        }
    }
}