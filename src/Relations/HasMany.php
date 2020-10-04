<?php

    declare(strict_types=1);


    namespace Sourcegr\Freakquent\Relations;

    use Sourcegr\Freakquent\BaseModel;
    use Sourcegr\QueryBuilder\QueryBuilder;

    class HasMany
    {
        private $instance = null;
        private $relatedModel = null;
        private $localKey = null;
        private $foreignKey = null;

        /**
         * @var QueryBuilder $QB;
         */
        private $QB;


        /**
         * Relation constructor.
         *
         * @param        $instance
         * @param string $relatedModel
         * @param string $localKey
         * @param string $foreignKey
         */
        public function __construct($instance, string $relatedModel, string $localKey, string $foreignKey)
        {
            $this->instance = $instance;
            $this->relatedModel = $relatedModel;
            $this->localKey = $localKey;
            $this->foreignKey = $foreignKey;
            $this->QB = $relatedModel::getDB()->where($foreignKey, '=', $instance->$localKey);
        }

        /**
         * @param callable $callable
         * @param string   $fields
         *
         * @return mixed
         */
        public function where(callable $callable)
        {
            $this->QB->where($callable);
            return $this;
        }

        /**
         * @return mixed
         */
        public function delete() {
            return $this->QB->delete();
        }

        /**
         * @param null $fields
         *
         * @return mixed
         */
        public function get($fields = null) {
            $rm = $this->relatedModel;
            return $rm::collect($this->QB->select($fields));
        }

        public function save($instance) {
            if (!($instance instanceof $this->relatedModel)) {
                throw new \Exception('YOU CAN ONLY SAVE instances of '. $this->relatedModel);
            }

            $fk = $this->foreignKey;
            $in = $this->instance;
            $lk = $this->localKey;

            $instance->$fk = $in->$lk;
            var_dump($instance);
            $instance->save();
        }

    }
