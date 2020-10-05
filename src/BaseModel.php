<?php

    declare(strict_types=1);

    namespace Sourcegr\Freakquent;

    use ReflectionClass;
    use ReflectionProperty;
    use Sourcegr\Freakquent\Exceptions\InvalidArgumentException;
    use stdClass;

    /**
     * Class BaseModel
     *
     * @package Sourcegr\Freakquent
     */
    class BaseModel
    {
        private static $localAllFields = [];
        private static $localMassAssignableFields = [];
        private static $localHiddenFields = [];
        private static $localSerializableFields = [];

        protected static $primaryKey = 'id';
        protected static $table = null;
        protected static $connectionName = null;

        protected $existing = false;
        protected $deleted = false;

        /**
         * @param bool $existing
         */
        public function setExisting(bool $existing): void
        {
            $this->existing = $existing;
        }

        /**
         * @param bool $deleted
         */
        public function setDeleted(bool $deleted): void
        {
            $this->deleted = $deleted;
        }



        /**
         * BaseModel constructor.
         *
         * @param null $data
         *
         * @throws InvalidArgumentException
         * @throws \Exception
         */
        public function __construct($data = null)
        {
            if (!static::$table) {
                throw new \Exception("TABLE SHOULD BE SET FOR THE MODEL");
            }

            self::getAllFields();
            self::getMassAssignableFields();
            self::getSerializableFields();



            if ($data) {
                $this->_fromData($data);
            }
        }

        /**
         * @return bool
         */
        public function isExisting(): bool
        {
            return $this->existing;
        }

        /**
         * @return bool
         */
        public function isDeleted(): bool
        {
            return $this->deleted;
        }


        /**
         * @param int    $id ID to find in th DB
         *
         * @param string $fields
         *
         * @return static|null
         * @throws InvalidArgumentException
         */
        public static function find(int $id, $fields = '*')
        {
            if (!$id) {
                return null;
            }

            $result = static::getDB()->where(static::$primaryKey, $id)->select($fields);

            $instance = new static();
            $instance->_fromDB($result[0]);
            return $instance;
        }

        /**
         * @param string $fields
         *
         * @return Collection
         * @throws InvalidArgumentException
         */
        public static function all( $fields = '*') {
            return static::collect(static::getDB()->select($fields));
        }


        /**
         * @param callable $callable
         *
         * @param          $fields
         *
         * @return Collection
         * @throws InvalidArgumentException
         */
        public static function where(callable $callable, $fields = '*')
        {
            $qb = static::getDB();
            return static::collect($callable($qb)->select($fields));
        }

        /**
         * @param callable $callable
         */
        public static function runQuery(callable $callable)
        {
            $qb = static::getDB();
            $callable($qb, static::$primaryKey);
        }


        /**
         * @return $this
         * @throws \Sourcegr\QueryBuilder\Exceptions\UpdateErrorException
         */
        public function save()
        {
            $idCol = static::$primaryKey;
            if ($this->existing) {
                $attrs = [];

                foreach (self::getAllFields() as $field) {
                    $attrs[$field] = $this->$field;
                }

                unset($attrs[$idCol]);

                static::getDB()->where($idCol, $this->$idCol)->update($attrs);
                return $this;
            } else {
                $attrs = [];

                foreach (self::getAllFields() as $field) {
                    $attrs[$field] = $this->$field;
                }
                unset($attrs[$idCol]);

                $this->$idCol = static::getDB()->insert($attrs);
                $this->existing = true;
                return $this;
            }
        }


        /**
         * @return bool true on success, false on failure
         */
        public function delete()
        {
            if ($this->existing) {
                $idCol = static::$primaryKey;
                $result = static::getDB()->where($idCol, $this->$idCol)->delete() ? true : false;
                if ($result) {
                    $this->existing = false;
                    $this->deleted = true;
                }
            } else {
                return false;
            }
        }


        /**
         * @return \Sourcegr\QueryBuilder\QueryBuilder
         */
        public static function getDB()
        {
            $caller = get_called_class();
            return Freakquent::getConnection($caller::$connectionName)->Table($caller::$table);
        }

        /**
         * @return array
         */
        public function toArray()
        {
            $result = [];
            foreach (self::getSerializableFields() as $field) {
                $result[$field] = $this->$field ?? null;
            }
            return $result;
        }

        /**
         * @return stdClass
         */
        public function toObject()
        {
            $result = new stdClass();
            foreach (self::getSerializableFields() as $field) {
                $result->$field = $this->$field ?? null;
            }
            return $result;
        }


        /**
         * @param $input
         *
         * @return Collection
         * @throws InvalidArgumentException
         */
        public static function collect($input)
        {
            $all = [];
            foreach ($input as $set) {
                $instance = new static();
                $instance->_fromDB($set);
                $all[] = $instance;
            }
            return Collection::from(static::class, $all);
        }


        /**
         * @param array $data
         *
         * @return static
         * @throws InvalidArgumentException
         */
        public static function fromData(array $data)
        {
            return new static($data);
        }


        /**
         * @param array $data
         *
         * @return $this
         * @throws InvalidArgumentException
         */
        private function _fromData(array $data)
        {
            if (!is_array($data)) {
                throw new InvalidArgumentException('model->fromData requires an array');
            }

            foreach (self::getMassAssignableFields() as $field) {
                $dataValue = array_key_exists($field, $data) ? $data[$field] : null;
                $fieldValue = $dataValue ?? $this->$field;
                $this->$field = $fieldValue;
            }

            return $this;
        }

        /**
         * @param array $data
         *
         * @return $this
         * @throws InvalidArgumentException
         */
        private function _fromDB(array $data)
        {
            if (!is_array($data)) {
                throw new InvalidArgumentException('model->fromData requires an array');
            }

            foreach (self::getAllFields() as $field) {
                if ($f = ($data[$field] ?? null)) {
                    $this->$field = $f;
                } else {
                    unset($this->$field);
                }
            }
            $this->existing = true;
            return $this;
        }





        /**
         * @return array
         */
        public static function getMassAssignableFields()
        {
            $exists = self::$localMassAssignableFields[static::class] ?? null;
            if ($exists) {
                return $exists;
            }

            $exists = static::$massAssignableFields ?? null;
            if ($exists) {
                self::$localMassAssignableFields[static::class] = $exists;
                return $exists;
            }

            $tmp = self::$localAllFields[static::class];

//            if (($key = array_search(static::$primaryKey, $tmp)) !== false) {
//                // primary key should never be mass assignable
//                unset($tmp[$key]);
//            }
            self::$localMassAssignableFields[static::class] = array_values($tmp);
            return self::$localMassAssignableFields[static::class];
        }


        /**
         * @return array
         */
        public static function getAllFields()
        {
            $exists = self::$localAllFields[static::class] ?? null;
            if ($exists) {
                return $exists;
            }

            $exists = static::$allFields ?? null;
            if ($exists) {
                self::$localAllFields[static::class] = $exists;
                return $exists;
            }

            $fields = [];
            $modelReflection = new ReflectionClass(static::class);
            $all = $modelReflection->getProperties(ReflectionProperty::IS_PUBLIC);

            foreach ($all as $prop) {
                $fields[] = $prop->name;
            }
            self::$localAllFields[static::class] = $fields;
            return $fields;
        }

        public static function getlHiddenFields() {
            $exists = self::$localHiddenFields[static::class] ?? null;
            if ($exists) {
                return $exists;
            }

            $exists = static::$hiddenFields ?? null;
            if ($exists) {
                self::$localHiddenFields[static::class] = $exists;
                return $exists;
            }
            self::$localHiddenFields[static::class] = [];
            return [];
        }

        public static function getSerializableFields()
        {
            $exists = self::$localSerializableFields[static::class] ?? null;
            if ($exists) {
                return $exists;
            }

            self::$localSerializableFields[static::class] = array_diff(
                self::getAllFields(static::class),
                self::getlHiddenFields(static::class)
            );
            return self::$localSerializableFields[static::class];
        }

    }