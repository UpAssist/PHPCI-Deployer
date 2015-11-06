<?php
namespace UpAssist\Deployer\Plugin;


use PHPCI\Builder;
use PHPCI\Model\Build;

/**
 * Class Deployer
 * @package UpAssist\Deployer\Plugin
 */
class Deployer implements \PHPCI\Plugin {

    /**
     * @var \PHPCI\Builder
     */
    protected $phpci;

    /**
     * @var \PHPCI\Model\Build
     */
    protected $build;

    protected $configuration = [];

    protected $currentBranch;

    /**
     * Deployer constructor.
     * @param Builder $phpci
     * @param Build $build
     * @param array $options
     */
    public function __construct(Builder $phpci, Build $build, array $options = array())
    {
        $this->phpci = $phpci;
        $this->build = $build;

        if(isset($options['deployer'])) {
            $this->configuration = $options['UpAssist\Deployer\Plugin\Deployer'];
        }

        $this->currentBranch = $this->build->getBranch();
    }

    public function execute()
    {
        // No configuration found
        if (empty($this->configuration)) {
            throw new \Exception('No deployer configuration found');
        }

        /** @var array $branchConfiguration */
        $branchConfiguration = $this->getConfigurationForCurrentBranch();

        // Current Branch has no configuration, silently ignore
        if (empty($branchConfiguration)) {
            return NULL;
        }

    }


    /**
     * @return array
     */
    private function getConfigurationForCurrentBranch()
    {
        return $this->configuration[$this->currentBranch];
    }

    /**
     * @param array $configuration
     * @throws \Exception
     */
    private function validateDeployerOptions($configuration = [])
    {
        if (empty($configuration)) {
            $configuration = $this->getConfigurationForCurrentBranch();
        }

        if (empty($configuration['user'])) {
            throw new \Exception('Deployer needs the user variable set');
        }
    }
}