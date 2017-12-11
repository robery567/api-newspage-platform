<?php
namespace Newspage\Api\Provider;

use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class LoggerServiceProvider implements ServiceProviderInterface {
    public function register(Container $pimple) {
        $pimple['logger'] = function () use ($pimple) {
            $log = new Logger('api.logger');
            $handler = new NullHandler();

            if ($pimple['env']->isLive()) {
                $handler = new SwiftMailerHandler($pimple['mailer'], function () use ($pimple) {
                    $message = \Swift_Message::newInstance()
                        ->setSubject('[critical] [api.bistriteanul.ro] Critical error encountered!')
                        ->setFrom(['logger@api.bistriteanul.ro' => 'Newspage API Logger'])
                                             ->setTo($pimple['logger.receivers'])
                        ->setBody('');

                    return $message;
                }, Logger::CRITICAL);
            }

            if ($pimple['env']->isProd()) {
                $handler = new SyslogHandler('api.bistriteanul.ro', LOG_USER, Logger::ERROR);
            }

            if ($pimple['env']->isDev()) {
                $handler = new RotatingFileHandler($pimple['logger.file'], Logger::DEBUG);
            }

            $log->pushHandler($handler);

            return $log;
        };
    }
}