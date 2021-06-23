<?php

declare(strict_types = 1);
/**
 * This file is part of project hyperf-template.
 *
 * @author   wenber.yu@creative-life.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Kernel\Log;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\Codec\Json;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use function sprintf;

/**
 * Default logger for logging server start and requests.
 * PSR-3 logger implementation that logs to STDOUT, using a newline after each
 * message. Priority is ignored.
 */
class StdoutLogger implements StdoutLoggerInterface
{
    /**
     * @var \Hyperf\Contract\ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var OutputInterface
     */
    private OutputInterface $output;

    /**
     * @var array
     */
    private array $tags = [
    ];

    public function __construct(ConfigInterface $config, $output = null)
    {
        $this->config = $config;
        $this->output = $output ?: new ConsoleOutput();
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $config = $this->config->get(StdoutLoggerInterface::class, ['log_level' => []]);
        if (! in_array($level, $config['log_level'], true)) {
            return;
        }

        $message = $this->getMessage($message, $this->tags, $level);
        if (! empty($context)) {
            $contextLine = Json::encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $message     .= ' ' . $contextLine;
        }

        $this->output->writeln($message);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    protected function getMessage(string $message, array $tags, string $level = LogLevel::INFO): string
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
                $tag = 'error';

                break;
            case LogLevel::ERROR:
                $tag = 'fg=red';

                break;
            case LogLevel::WARNING:
            case LogLevel::NOTICE:
                $tag = 'comment';

                break;
            case LogLevel::INFO:
            default:
                $tag = 'info';
        }

        $template     = sprintf('<%s>[%s]</>', $tag, strtoupper($level));
        $implodedTags = '';
        foreach ($tags as $value) {
            $implodedTags .= (' [' . $value . ']');
        }

        return sprintf($template . $implodedTags . ' %s', $message);
    }
}
