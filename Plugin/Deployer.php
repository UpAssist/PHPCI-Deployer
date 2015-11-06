<?php
namespace UpAssist\PHPCI\Deployer\Plugin;

use PHPCI\Builder;
use PHPCI\Model\Build;
use PHPCI\Plugin;
use UpAssist\PHPCI\Deployer\Utility\GitUtility;

/**
 * Class Deployer
 *
 * @category Class
 * @package UpAssist\PHPCI\Deployer
 * @author Henjo Hoeksma <henjo@upassist.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link http://www.upassist.com
 */
class Deployer implements Plugin
{

    /**
     * @var \PHPCI\Builder
     */
    protected $phpci;

    /**
     * @var \PHPCI\Model\Build
     */
    protected $build;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $currentBranch;

    /**
     * Deployer constructor.
     * @param Builder $phpci
     * @param Build $build
     * @param array $options
     */
    public function __construct(Builder $phpci, Build $build, $options = [])
    {
        $this->phpci = $phpci;
        $this->build = $build;

        if (!empty($options)) {
            $this->options = $options;
        }

        $this->currentBranch = $this->build->getBranch();

    }

    /**
     *
     * @return bool|null
     * @throws \Exception
     */
    public function execute()
    {
        // No configuration found
        if (empty($this->options)) {
            throw new \Exception('No deployer configuration found, are you sure you added configuration?');
        }

        /** @var array $branchConfiguration */
        $branchConfiguration = $this->_getConfigurationForCurrentBranch();

        // Current Branch does not exist in the configuration, silently ignore
        if ($branchConfiguration === null) {
            return null;
        }

        // Copy the deploy.php if set
        if (isset($this->options['deployFile'])) {
            if ($this->phpci->executeCommand(
                'cp ' . $this->options['deployFile'] . ' ' .
                $this->build->currentBuildPath . '/deploy.php')) {
                $this->phpci->log('Copied the deploy file successfully.');
            }
        }

        // Validate the yaml configuration and if correct: deploy
        if ($this->_validateDeployerOptions($branchConfiguration)) {
            // Lets deploy...
            $env = 'STAGE=' . $branchConfiguration['stage'] . ' ';
            $env .= 'SERVER=' . $branchConfiguration['server'] . ' ';
            $env .= 'USER=' . $branchConfiguration['user'] . ' ';
            $env .= 'DEPLOY_PATH=' . $branchConfiguration['deploy_path'] . ' ';
            $env .= 'BRANCH=' . $branchConfiguration['branch'] . ' ';
            $env .= 'REPOSITORY=' . $branchConfiguration['repository'] . ' ';
            $env .= 'SHARED_DIRS=' . implode(',', $branchConfiguration['shared_dirs']) . ' ';
            $env .= 'WRITABLE_DIRS=' . implode(',', $branchConfiguration['writable_dirs']) . ' ';
            $env .= 'BUILD=' . $this->build->getId() . ' ';
            try {
                $this->phpci->executeCommand('cd ' . $this->build->currentBuildPath);
                $command = $env . 'dep deploy ' . $branchConfiguration['stage'];
                if ($this->phpci->executeCommand($command)) {
                    $success = true;
                } else {
                    $this->phpci->logFailure('Something went wrong at the deployer part.' . PHP_EOL .
                        'The following command was used:' . PHP_EOL . $command);
                    $success = false;
                }
            } catch (\Exception $e) {
                $this->phpci->logFailure('Something went wrong at the deployer part.', $e);
                $success = false;
            }
        } else {
            $success = false;
        }

        return $success;
    }


    /**
     * @return array
     */
    private function _getConfigurationForCurrentBranch()
    {
        if (isset($this->options[$this->currentBranch])) {
            $branchConfiguration = $this->options[$this->currentBranch];

            // setup the Git Repo to use for deployment
            $gitUtility = new GitUtility();
            $branchConfiguration['repository'] = $gitUtility->getCurrentRepositoryUri($this->phpci->buildPath);

            // Set some defaults
            $branchConfiguration['branch'] = $this->currentBranch;
            // If stage is not set, we fall back to the branch name
            $branchConfiguration['stage'] = isset($branchConfiguration['stage']) ?
                $branchConfiguration['stage'] : $this->currentBranch;
            return $branchConfiguration;
        }

        return null;
    }

    /**
     * @param array $configuration
     * @return bool
     * @throws \Exception
     */
    private function _validateDeployerOptions($configuration = [])
    {
        if (empty($configuration)) {
            $configuration = $this->_getConfigurationForCurrentBranch();
        }

        if (empty($configuration['repository'])) {
            $this->phpci->logFailure('Deployer needs the repository variable set, this should be done automatically');
            return false;
        }

        if (empty($configuration['user'])) {
            $this->phpci->logFailure('Deployer needs the user variable set');
            return false;
        }

        if (empty($configuration['server'])) {
            $this->phpci->logFailure('Deployer needs the server variable set');
            return false;
        }

        if (empty($configuration['deploy_path'])) {
            $this->phpci->logFailure('Deployer needs the deploy_path variable set');
            return false;
        }

        if (isset($configuration['shared_dirs']) && !is_array($configuration['shared_dirs'])) {
            $this->phpci->logFailure('`shared_dirs` variable must be an array...');
            return false;
        }

        if (isset($configuration['writable_dirs']) && !is_array($configuration['writable_dirs'])) {
            $this->phpci->logFailure('`writable_dirs` variable must be an array...');
            return false;
        }

        $output = '';
        if (isset($this->options['deployFile'])) {
            $output .= '<b>Deploy file:</b> ' . $this->options['deployFile'] . PHP_EOL;
        }
        foreach ($configuration as $key => $value) {
            $output .= '<b>' . $key . ':</b> ';
            $output .= is_array($value) ? implode(PHP_EOL, $value) . PHP_EOL : $value . PHP_EOL;
        }
        $this->phpci->log('Using the following configuration:' . PHP_EOL . $output);

        return true;
    }
}