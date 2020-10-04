<?php

    namespace Tests;

    use Sourcegr\QueryBuilder\DB;
    use Sourcegr\Freakquent\Freakquent;

    use Sourcegr\QueryBuilder\Grammars\MySQL;
    use Sourcegr\QueryBuilder\QueryBuilder;
    use Tests\Stub\Grammar;
    use Tests\Stub\Models\Company;
    use Tests\Stub\Models\Contact;

    use PHPUnit\Framework\TestCase;


    class RelationsTest extends TestCase
    {

        private function init()
        {
            $grammar = new MySQL([]);
            $DB = new DB($grammar);
            Freakquent::init($DB);
            $this->cleanupDB();
        }

        private function cleanupDB()
        {
            Contact::all()->delete();
            Company::all()->delete();
        }


        private function setUpRelations()
        {
            $related = Company::fromData(['name' => 'Company 1'])->save();
            Company::fromData(['name' => 'Company 2'])->save();

            $a = Contact::fromData(
                [
                    'name' => 'person 1 OF comp1',
                    'company_id' => $related->id,
                ]
            )->save();

            Contact::fromData(
                [
                    'name' => 'person 2 OF comp1',
                    'company_id' => $related->id,
                ]
            )->save();

            Contact::fromData(
                [
                    'name' => 'person 1 (no comp)',
                    'company_id' => 0
                ]
            )->save();

            return $related;
        }


        public function testCount()
        {
            $this->init();
            $parent = $this->setUpRelations();
            $contacts = $parent->contacts()->get();

            $this->assertEquals(2, count($contacts));
        }

        public function testAttachModel()
        {
            $this->init();
            $parent = $this->setUpRelations();

            $k = new Contact(['name' => 'NEWCONTACT']);
            $parent->contacts()->save($k);

            $contacts = $parent->contacts()->get();
            $this->assertEquals(3, count($contacts));
        }

        public function testWhere()
        {
            $this->init();
            $parent = $this->setUpRelations();

            $contacts = $parent->contacts()->get();
            $this->assertEquals(2, count($contacts));

            $contacts = $parent->contacts()->where(
                function ($QB) {
                    $QB->where(' name ', 'LIKE', 'person 1%');
                }
            )->get();

            $this->assertEquals(1, count($contacts), 'Wrong count');
        }

        public function testThrowsOnWrongMoodel()
        {
            $this->init();
            $parent = $this->setUpRelations();

            $c = new Company(['name' => 'NEWCONTACT']);
            $this->expectException(\Exception::class, 'Adding this should throw');
            $parent->contacts()->save($c);
        }


        public function testDeleteRelatedModels()
        {
            $this->init();
            $parent = $this->setUpRelations();

            $all = Contact::all();
            $this->assertEquals(3, count($all), 'There should be 3 entries');


            $parent->contacts()->delete();

            $all = Contact::all();
            $this->assertEquals(1, count($all), 'There should be 1 left');
        }
    }

