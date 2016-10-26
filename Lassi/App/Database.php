<?php

namespace Lassi\App;

use Illuminate\Database\Capsule\Manager;
use \Lassi\App\Exception\NotFoundException;
use \Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Database
 *
 * @author Jabran Rafique <hello@jabran.me>
 * @license MIT License
 */
class Database
{
    /**
     * @var \Illuminate\Database\Capsule\Manager
     */
    protected $capsule;

    /**
     * @var Database
     */
    protected static $instance;

    /**
     * @return Database
     */
    public function __construct()
    {
        $this->makeEloquent(new Capsule());

        return $this;
    }

    /**
     * Get instance of current class
     *
     * @return $this
     */
    public static function getInstance()
    {
        if (!static::$instance instanceof Database) {
            static::$instance = new Database();
        }
        return static::$instance;
    }

    /**
     * Get instance of capsule
     *
     * @return Manager
     */
    public function capsule()
    {
        return $this->capsule;
    }

    /**
     * Setup eloquent database
     *
     * @param Manager $capsule
     * @throws NotFoundException
     *
     * @return $this
     */
    private function makeEloquent(Capsule $capsule)
    {
        // Throw exception if minimum requirements not met
        if (!getenv('db_driver') || !getenv('db_name')) {
            throw new NotFoundException('App configurations not found.');
        }

        // Get capsule instance
        $this->capsule = $capsule;

        // Cache db driver
        $db_driver = getenv('db_driver');

        // Setup connection defaults
        $configs = array(
            'driver' => $db_driver,
            'database' => getenv('db_name'),
            'prefix' => getenv('db_prefix'),
            'charset' => getenv('db_charset'),
            'collation' => getenv('db_collation'),
        );

        // Add extras depending on type of driver/connection
        if ($db_driver !== 'sqlite') {
            if (getenv('db_host')) {
                $configs['host'] = getenv('db_host');
            }
            if (getenv('db_username')) {
                $configs['username'] = getenv('db_username');
            }
            if (getenv('db_password')) {
                $configs['password'] = getenv('db_password');
            }
        }

        // Setup connection
        $this->capsule->addConnection($configs);

        // Set as global
        $this->capsule->setAsGlobal();

        // Boot eloquent
        $this->capsule->bootEloquent();
        return $this;
    }
}
