Freakquent
==========
An Eloquent-like, QueryBuilder-like, Doctrine-like, IDE-friently, no-bullshit ORM for PHP.


Setup
==
```php
<?php

use Sourcegr\QueryBuilder\DB;
use Sourcegr\Freakquent\Freakquent;
use Sourcegr\QueryBuilder\Grammars\MySQL;


$grammar = new MySQL([
    'DB' => 'dbname',
    'USER' => 'user',
    'PASS' => 'passwordd',
    'HOST' => '127.0.0.1'
]);

$DB = new DB($grammar);
Freakquent::init($DB);
```

Example Contacts Model
-
```php
<?php

namespace Models;


use Sourcegr\Freakquent\BaseModel;
use Sourcegr\Freakquent\Relations\RelationTrait;

class Contact extends BaseModel
{
    use RelationTrait;

    protected static $table = 'contacts';

    public $id;
    public $name;
    public $company_id;


    public function company() {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}
```


Example Company Model
-
```php
<?php

    namespace Models;


    use Sourcegr\Freakquent\BaseModel;
    use Sourcegr\Freakquent\Relations\RelationTrait;

    class Company extends BaseModel
    {
        use RelationTrait;

        // set up model's database tablename
        protected static $table = 'companies';

        // set up model's database columns
        public $id;
        public $name;

        // set up a relation. A Company has many contacts
        public function contacts()
        {
            return $this->hasMany(Contact::class, 'id', 'company_id');
        }
    }
```


How to Use it
--
Find a model by id
`Contact::find(1);`  

Find and delete it
`Contact::find(1).delete();`

Find and update it
```php
$c = Contact::find(1);
$c->name = 'New name';
$c->save();
```


