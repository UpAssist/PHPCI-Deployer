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
    public function getCurrentRepositoryUri($directory = __DIR__)
    {
        exec("cd '$directory'; git remote -v", $lines);
        foreach ($lines as $line) {
            $gitInfo = explode(' ', preg_replace('/\s+/', ' ', $line));
            if ($gitInfo[0] === 'origin' && $gitInfo[2] === '(fetch)') {
                return $gitInfo[1];
            }
        }
    }
}