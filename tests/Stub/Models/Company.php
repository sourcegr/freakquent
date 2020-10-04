<?php

    declare(strict_types=1);


    namespace Tests\Stub\Models;


    use Sourcegr\Freakquent\BaseModel;
    use Sourcegr\Freakquent\Relations\HasMany;
    use Sourcegr\Freakquent\Relations\RelationTrait;

    class Company extends BaseModel
    {
        use RelationTrait;

        protected static $table = 'companies';
        /**
         * @var HasMany[]
         */


        /**
         * @var int $id
         */
        public $id;
        public $name;

        /**
         * @return HasMany
         */
        public function contacts()
        {
            return $this->hasMany(Contact::class, 'id', 'company_id');
        }
    }