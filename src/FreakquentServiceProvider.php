<?php


    namespace Sourcegr\Freakquent;


    use Sourcegr\Base\Interfaces\App;
    use Sourcegr\Base\ServiceProvider;
    use Sourcegr\Freakquent\Exceptions\InvalidArgumentException;

    class FreakquentServiceProvider extends ServiceProvider
    {
        /**
         * @var App null
         */
        public $app = null;

        public function init()
        {
            $instance = new static($this->app);
            $databaseConfig = $this->app->conf('database');
            $dbParams = $this->app->loadConfig('database');
            $config = $dbParams[$databaseConfig] ?? null;

            if (!$config) {
                throw new InvalidArgumentException('please set connection settings for '. $databaseConfig.' in /config/database.php');
            }

            $db = Freakquent::init($databaseConfig, $config);

            return function() use ($db) {
                return $db;
            };
            //            $grammar = new MySQL([]);
//            $DB = new DB($grammar);
//            Freakquent::init($DB);
            //            $this->config = $this->app->conf('database');
//            $this->app
        }
    }