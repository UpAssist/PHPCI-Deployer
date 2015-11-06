# PHPCI-Deployer
Plugin to use deployer (http://deployer.org/) with PHPCI

## Configuration (PHPCI)

```yaml

complete:
  # Class reference
  \UpAssist\PHPCI\Deployer\Plugin\Deployer:
    # Optional: add a deployFile parameter to point to a specific deploy.php file.
    # It will be copied to the root of your build folder so deployment can run.
    deployFile: 'My/DeploymentScripts/deploy_specific_for_x_type_of_projects.php'
    
    # Branch 'master'
    master:
      # Stage is optional: else the branchname will be used
      stage: test
      # Required
      server: test.domain.com
      # Required
      user: username
      # Required, no trailing slash
      deploy_path: /var/www/vhosts/test
      # Optional, array
      shared_dirs:
        - 'uploads'
      # Optional, array
      writable_dirs:
        - 'uploads'
    # Branch 'live'
    live:
      # Stage is optional: else the branchname will be used
      stage: production
      # Required
      server: domain.com
      # Required
      user: username
      # Required, no trailing slash
      deploy_path: /var/www/vhosts/production
      # Optional, array
      shared_dirs:
        - 'uploads'
      # Optional, array
      writable_dirs:
        - 'uploads'
```
If a branch is not defined, it will silently ignore the builds from that branch. 

## Deployer file example

```php
server(getenv('STAGE'), getenv('SERVER'))
    ->user(getenv('USER'))
    ->env('deploy_path', getenv('DEPLOY_PATH'))
    ->env('branch', getenv('BRANCH'))
    ->env('local_release_path', __DIR__ . '/tmp/release/' . getenv('BUILD'))
    ->env('rsync_src', __DIR__ . '/tmp/release/' . getenv('BUILD'))
    ->env('rsync_dest', '{{release_path}}')
    ->stage(getenv('STAGE'))
    ->identityFile('~/.ssh/id_rsa.pub', '~/.ssh/id_rsa', '');
```

### Variables that are sent as environment variables to the deploy.php

* STAGE
* SERVER
* USER
* DEPLOY_PATH
* BRANCH
* BUILD (the build id)
* REPOSITORY
* SHARED_DIRS
* WRITABLE_DIRS

