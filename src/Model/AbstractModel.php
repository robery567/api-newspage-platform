<?php
/**
 * @package api
 * @author Petru Szemereczki <petru.office92@gmail.com>
 * @since 1.0
 */

namespace Newspage\Api\Model;


use Doctrine\DBAL\Connection;
use Silex\Application;

abstract class AbstractModel
{
    /** @var Connection */
    protected $db;

    public function __construct(Application $api)
    {
        $this->db = $api['db'];
    }
}