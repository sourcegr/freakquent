<?php

    declare(strict_types=1);


    namespace Sourcegr\Freakquent;


    use Sourcegr\Freakquent\Exceptions\InvalidArgumentException;
    use Sourcegr\QueryBuilder\DB;


    class Freakquent
    {
        private static $instance = null;
        private static $connections = [];

        /**
         * @var Capsule $capsule
         */
        public $capsule = null;

        /**
         * @param Capsule $capsule
         *
         * @return Freakquent|null
         */
        public static function init(string $name, $config)
        {
            $grammar = '\\Sourcegr\\QueryBuilder\\Grammars\\' . ucfirst($config['GRAMMAR']);
            if (class_exists($grammar)) {
                $db = new DB(new $grammar($config));
            } else {
                $db = new DB([]);
            }


            static::$instance = static::$instance ?? $db;
            static::$connections[$name] = $db;

            return $db;
        }

        public static function getConnection($name = null)
        {
            if (!$name) {
                return static::$instance;
            }

            $connection = static::$connections[$name] ?? null;

            if (!$connection) {
                throw new InvalidArgumentException('Could not get named connection '. $name);
            }

            return $connection;
        }
    }
