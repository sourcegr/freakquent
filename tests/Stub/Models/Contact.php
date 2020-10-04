<?php


namespace Tests\Stub\Models;


use Sourcegr\Freakquent\BaseModel;
use Sourcegr\Freakquent\Relations\RelationTrait;

class Contact extends BaseModel
{
    use RelationTrait;

    protected static $table = 'contacts';

    /**
     * @var int $id
     */
    public $id;
    public $name;
    public $company_id;


    public function company() {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

}