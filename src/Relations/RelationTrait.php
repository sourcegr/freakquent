<?php

    declare(strict_types=1);

    namespace Sourcegr\Freakquent\Relations;


    trait RelationTrait
    {
        /**
         * @param string $relatedModel
         * @param string $localKey
         * @param string $foreignKey
         *
         * @return HasMany
         */
        public function hasMany(string $relatedModel, string $localKey, string $foreignKey)
        {
            return new HasMany($this, $relatedModel, $localKey, $foreignKey);
        }

        public function belongsTo(string $relatedModel, string $localKey, string $foreignKey)
        {
            return new BelongsTo($this, $relatedModel, $localKey, $foreignKey);
        }

//        public function relation($name)
//        {
//            $relation = $this->relations[$name] ?? null;
//            if (!$relation) {
//                return null;
//            }
//
//            /** @var BaseModel $className */
//            $className = $relation->className;
//            $foreignKey = $relation->foreignKey;
//            $myKey = $relation->myKey;
//            $type = $relation->type;
//
//            switch ($type) {
//                case 'hasMany':
//                    $myVal = $this->$myKey;
//                    return $className::where(
//                        function ($qb) use ($foreignKey, $myVal) {
//                            return $qb->where($foreignKey, $myVal);
//                        }
//                    );
//
//                case 'lalaal':
//                    break;
//            }
//        }
    }