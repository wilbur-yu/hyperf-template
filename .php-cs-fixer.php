<?php

declare(strict_types=1);
/**
 * This file is part of project hyperf-template.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
$header = <<<'EOF'
This file is part of project hyperf-template.

@author   wenber.yu@creative-life.club
@link     https://github.com/wilbur-yu/hyperf-template

@link     https://www.hyperf.io
@document https://hyperf.wiki
@contact  group@hyperf.io
@license  https://github.com/hyperf/hyperf/blob/master/LICENSE
EOF;

return (new PhpCsFixer\Config())->setRiskyAllowed(true)
    ->setRules(['@PSR12' => true,])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('public')
            ->exclude('runtime')
            ->exclude('vendor')
            ->in(__DIR__)
    )
    ->setUsingCache(false);
