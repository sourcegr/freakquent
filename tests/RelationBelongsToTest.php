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


    class RelationBelongsToTest extends TestCase
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
            $comp1 = Company::fromData(['name' => 'Company 1'])->save();
            $comp2 = Company::fromData(['name' => 'Company 2'])->save();

            $contact1 = Contact::fromData(
                [
                    'name' => 'person 1 OF comp1',
                    'company_id' => $comp1->id,
                ]
            )->save();

            Contact::fromData(
                [
                    'name' => 'person 2 OF comp1',
                    'company_id' => $comp1->id
                ]
            )->save();

            $contact2 = Contact::fromData(
                [
                    'name' => 'person 1 (no comp)',
                    'company_id' => 0
                ]
            )->save();

            return [$contact1, $contact2, $comp2];
        }


        public function testCount()
        {
            $this->init();
            [$contact] = $this->setUpRelations();
            $company = $contact->company()->get();

            $this->assertNotNull($company, "company should not be null");
            $this->assertEquals($contact->company_id, $company->id, "Keys should be the same");
        }

        public function testHasNoRelation()
        {
            $this->init();
            [$contact1, $contact2, $comp2] = $this->setUpRelations();
            $company = $contact2->company()->get();

            $this->assertNull($company, "company should not be null");
        }

        public function testAttachModel()
        {
            $this->init();
            [$contact] = $this->setUpRelations();
            $oldCompany = $contact->company()->get();
            $oldId = $oldCompany->id;

            $enwCompany = new Company(['name' => 'NEWCOMPANY']);
            $enwCompany->save();
            $newId = $enwCompany->id;

            $relatedCompany = $contact->company()->get();
            // set old status
            $this->assertEquals($relatedCompany->id, $oldId);
            $this->assertNotEquals($relatedCompany->id, $newId);


            $contact->company()->set($enwCompany);
            // set new status
            $relatedCompany = $contact->company()->get();
            $this->assertNotEquals($relatedCompany->id, $oldId);
            $this->assertEquals($relatedCompany->id, $newId);
        }


        public function testThrowsOnWrongModel()
        {
            $this->init();
            [$contact] = $this->setUpRelations();

            $c = new Contact(['name' => 'NEWCONTACT']);
            $c->save();

            $this->expectException(\Exception::class, 'Adding this should throw');
            $contact->company()->set($c);
        }


        public function testDeleteRelatedModel()
        {
            $this->init();
            [$contact] = $this->setUpRelations();
            $company = $contact->company()->get();
            $this->assertNotNull($company, 'Related should NOT be NULL');

            $contact->company()->delete();
            $company = $contact->company()->get();
            $this->assertNull($company, 'Related should be NULL');
        }
    }

