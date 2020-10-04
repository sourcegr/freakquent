<?php

    declare(strict_types=1);


    namespace Sourcegr\Freakquent;


    use Sourcegr\QueryBuilder\DB;


    class Freakquent
    {
        private static $instance = null;
        /**
         * @var DB $DB
         */
        public static $DB;

        /**
         * @param DB $DB
         *
         * @return Freakquent|null
         */
        public static function init(DB $DB)
        {
            if (static::$instance) {
                return static::$instance;
            }

            $me = new static();

            static::$DB = $DB;
            static::$instance = $me;

            return $me;
        }
    }
