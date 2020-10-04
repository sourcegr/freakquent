<?php

    namespace Tests;

    use Sourcegr\QueryBuilder\DB;
    use Sourcegr\QueryBuilder\Grammars\MySQL;
    use Sourcegr\Freakquent\Freakquent;

    use Tests\Stub\Models\Company;

    use PHPUnit\Framework\TestCase;


    class realConnectionTest extends TestCase
    {

        private function init()
        {
            $grammar = new MySQL(['DB' => 'test']);
            $db = new DB($grammar);
            Freakquent::init($db);
        }

        private function createCompany() {
            $randomName = bin2hex(random_bytes(5));
            $contact = Company::fromData(['name' => $randomName]);
            $contact->save();

            return $contact->id;
        }

        private function cleanupDB() {
            Company::where(function($q) {
                return $q->where('id');
            })->delete();
        }

        public function testCreate()
        {
            $this->init();

            $randomName = bin2hex(random_bytes(5));
            $expectedClass = Company::class;

            $contact = Company::fromData(['name' => $randomName]);
            $this->assertInstanceOf($expectedClass, $contact, 'Class name does not match');
            $this->assertEquals(false, $contact->isExisting(), 'Existing flag should be false');
            $this->assertEquals(false, $contact->isDeleted(), 'deleted flag should be false');

            $contact->save();
            $this->assertEquals(true, $contact->isExisting(), 'Existing flag should be true');
            $this->assertEquals(false, $contact->isDeleted(), 'deleted flag should be false');

            $this->assertObjectHasAttribute('id', $contact, 'id attribute was not set');
            $this->assertObjectHasAttribute('name', $contact, 'name attribute does not match');

            // loook it up in the DB
            $id = $contact->id;
            $newCompany = Company::find($id);

            $this->assertInstanceOf($expectedClass, $newCompany, 'Class name does not match for retrieved result');
            $this->assertEquals(true, $newCompany->isExisting(), 'Existing flag should be true');
            $this->assertEquals(false, $newCompany->isDeleted(), 'deleted flag should be false');
            $this->assertEquals($randomName, $newCompany->name, 'name from DB is not the same as the one save');
            $this->assertObjectHasAttribute('id', $newCompany, 'id attribute was not set');
            $this->assertObjectHasAttribute('name', $newCompany, 'name attribute does not match');

            $this->cleanupDB();
        }

        public function testFindOne()
        {
            $this->init();
            $id = $this->createCompany();

            $contact = Company::find((int)$id);
            $expected = Company::class;

            $this->assertInstanceOf($expected, $contact, 'Class name should be '. $expected);
            $this->assertObjectHasAttribute('id', $contact, '"id" should exist');
            $this->assertObjectHasAttribute('name', $contact, '"name" should exist');

            $this->cleanupDB();
        }

        public function testUpdateOne()
        {
            $this->init();

            $randomName = bin2hex(random_bytes(5));
            $newRandomName = bin2hex(random_bytes(5));

            $contact = Company::fromData(['name' => $randomName]);
            $this->assertEquals(null, $contact->id, 'ID is not null before saving');

            $contact->save();
            $this->assertNotNull($contact->id, 'ID is null before saving');

            $id = $contact->id;
            $newCompany = Company::find($id);

            $this->assertEquals($randomName, $newCompany->name, 'name value after "find" does not match');

            $newCompany->name = $newRandomName;
            $newCompany->save();

            $reloadCompany = Company::find($newCompany->id);

            $this->assertEquals($newRandomName, $reloadCompany->name, 'name value does not match');

            $this->cleanupDB();
        }


        public function testDelete()
        {
            $this->init();

            $randomName = bin2hex(random_bytes(5));

            $contact = Company::fromData(['name' => $randomName]);
            $contact->save();

            $this->assertEquals(true, $contact->isExisting(), 'BEFORE DELETE: Existing flag should be true');
            $this->assertEquals(false, $contact->isDeleted(), 'BEFORE DELETE: deleted flag should be false');

            $id = $contact->id;
            $newCompany = Company::find($id);
            $newCompany->delete();
            $this->assertEquals(false, $newCompany->isExisting(), 'AFTER DELETE: Existing flag should be false');
            $this->assertEquals(true, $newCompany->isDeleted(), 'AFTER DELETE: deleted flag should be true');
        }
    }

