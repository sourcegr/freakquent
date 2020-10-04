<?php

    declare(strict_types=1);


    namespace Sourcegr\Freakquent;


    use ArrayObject;

    class Collection extends ArrayObject
    {
        private $owner;

        /**
         * @throws \Exception
         */
        private function ensureHasOwner()
        {
            if (!$this->owner) {
                throw new \Exception("NO OWNER SET");
            }
        }


        /**
         * @param string $owner
         * @param array  $input
         * @param int    $flags
         * @param string $iterator_class
         *
         * @return Collection
         */
        public static function from(string $owner, $input = [], $flags = 0, $iterator_class = "ArrayIterator")
        {
            $collection = new static($input, $flags, $iterator_class);
            $collection->owner = $owner;

            return $collection;
        }

        /**
         * @param $key
         *
         * @return array array with the key values of each member
         */
        public function gather($key)
        {
            $gathered = [];
            $items = $this->getArrayCopy();
            foreach ($items as $item) {
                $gathered[] = ((array)$item)[$key] ?? null;
            }
            return $gathered;
        }


        /**
         * @param $key
         *
         * @return array Collection with keys the value of each member
         */
        public function keyBy($key)
        {
            $gathered = [];
            $items = $this->getArrayCopy();
            foreach ($items as $item) {
                $localKey = ((array)$item)[$key] ?? null;
                $gathered[$localKey] = $item;
            }
            return $gathered;
        }


        /**
         * @return bool
         * @throws \Exception
         */
        public function delete()
        {
            $this->ensureHasOwner();

            $items = $this->getArrayCopy();

            if (count($items)) {
                $this->owner::runQuery(
                    function ($q, $idColumn) use ($items) {
                        $ids = [];
                        foreach ($items as $item) {
                            $ids[] = $item->$idColumn;
                            $item->setDeleted(true);
                            $item->setExisting(false);
                        }
                        $q->whereIn($idColumn, $ids)->delete();
                    }
                );
                return true;
            }
            return false;
        }

        /**
         * @param $specs
         *
         * @throws \Exception
         */
        public function update($specs)
        {
            $this->ensureHasOwner();

            $items = $this->getArrayCopy();
            $this->owner::runQuery(
                function ($q, $idColumn) use ($items, $specs) {
                    $ids = [];
                    foreach ($items as $item) {
                        $ids[] = $item->$idColumn;
                    }
                    $q->whereIn($idColumn, $ids)->update($specs);
                }
            );
        }
    }