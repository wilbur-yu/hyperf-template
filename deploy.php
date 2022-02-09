<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'your-project-name');

set('deploy_path', 'your-deploy-path');

// Project repository
set('repository', 'git@gitee.com:creative-life/burton-api-server.git');

// 保留版本数量
set('keep_releases', 5);

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
set('shared_files', [
    '.env',
]);

set('shared_dirs', [
    'runtime/logs',
    'storage/cert',
    'public',
]);

// Writable dirs by web server
set('writable_dirs', [
    'runtime',
]);
set('log_files', 'runtime/logs/*.log');

// Hosts
localhost()
    ->stage('test')
    ->set('branch', 'develop') // 最新开发分支部署到测试机
    ->set('http_user', 'www')  // 这个与 nginx 里的配置一致
    ->set('deploy_path', '{{deploy_path}}');
localhost()
    ->stage('production')
    ->set('branch', 'master') // 最新生产分支部署到正式
    ->set('http_user', 'www')  // 这个与 nginx 里的配置一致
    ->set('deploy_path', '{{deploy_path}}');

desc('Execute supervisorctl stop');
task('supervisor:stop', function () {
    run('sudo supervisorctl stop {{application}}');
});
desc('Execute supervisorctl start');
task('supervisor:start', function () {
    run('sudo supervisorctl start {{application}}');
});
desc('Execute supervisorctl reload');
task('supervisor:reload', function () {
    run('sudo supervisorctl reload');
});
desc('Execute supervisorctl restart');
task('supervisor:restart', function () {
    run('sudo supervisorctl restart {{application}}');
});
desc('Execute hyperf migrate');
task('hyperf:migrate', function () {
    run('{{bin/php}} {{release_path}}/bin/hyperf.php migrate');
});
desc('Execute chown project owner for www');
task('chown:owner', function () {
    run('sudo /usr/bin/chown -R www:www {{deploy_path}}');
});
desc('Execute opcache:clear');
task('hyperf:opcache:clear', function () {
    run('{{bin/php}} {{release_path}}/bin/hyperf.php opcache:clear');
});
desc('Execute opcache:config');
task('hyperf:opcache:config', function () {
    run('{{bin/php}} {{release_path}}/bin/hyperf.php opcache:config');
});
desc('Execute opcache:status');
task('hyperf:opcache:status', function () {
    run('{{bin/php}} {{release_path}}/bin/hyperf.php opcache:status');
});
desc('Execute opcache:compile');
task('hyperf:opcache:compile', function () {
    run('{{bin/php}} {{release_path}}/bin/hyperf.php opcache:compile --force=true');
});
desc('Execute opcache:reset');
task('hyperf:opcache:reset', function () {
    task('hyperf:opcache:clear');
    task('hyperf:opcache:compile');
});
desc('Execute Before the start of the preparation');
task('hyperf:before-start', function () {
    run('{{bin/composer}} before-start -d {{release_path}}');
});
desc('Execute Systemd Manager');
task('systemd:restart', function () {
    run('/bin/systemctl restart {{application}}');
});

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success',
]);

before('deploy:symlink', 'hyperf:migrate');
before('deploy:symlink', 'hyperf:before-start');
before('deploy:symlink', 'hyperf:opcache:reset');
before('deploy:symlink', 'chown:owner');
after('deploy:symlink', 'systemd:restart');

after('deploy:failed', 'deploy:unlock');
