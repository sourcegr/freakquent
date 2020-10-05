<?php

    declare(strict_types=1);

    namespace Tests;

    use Sourcegr\Freakquent\Collection;


    use PHPUnit\Framework\TestCase;
    use Sourcegr\Freakquent\Freakquent;
    use Sourcegr\QueryBuilder\DB;
    use Sourcegr\QueryBuilder\Grammars\MySQL;
    use Sourcegr\QueryBuilder\QueryBuilder;
    use Tests\Stub\Models\Company;


    class CollectionTest extends TestCase
    {
        private function init()
        {
            $db = Freakquent::init('mysql', ['GRAMMAR' => 'MySQL']);
        }

        private function createCompany()
        {
            $randomName = bin2hex(random_bytes(5));
            $contact = Company::fromData(['name' => $randomName]);
            $contact->save();

            return $contact->id;
        }

        private function cleanupDB()
        {
            Company::where(
                function ($q) {
                    return $q->where('id');
                }
            )->delete();
        }

        public function testCreation()
        {
            $array = [1, 2, 3];
            $a = new Collection($array);

            $this->assertEquals($array, $a->getArrayCopy(), 'Arrays should be the same');
        }

        public function testKeyBy()
        {
            $array = [
                ['id' => 100, 'name' => 'one'],
                ['id' => 200, 'name' => 'two'],
                ['id' => 300, 'name' => 'three'],
            ];
            $expected = [
                100 => ['id' => 100, 'name' => 'one'],
                200 => ['id' => 200, 'name' => 'two'],
                300 => ['id' => 300, 'name' => 'three'],
            ];

            $a = new Collection($array);
            $actual = $a->keyBy('id');

            $this->assertEquals($expected, $actual, 'KeyBy failed');
        }

        public function testGather()
        {
            $array = [
                ['id' => 100, 'name' => 'one'],
                ['id' => 200, 'name' => 'two'],
                ['id' => 300, 'name' => 'three'],
            ];

            $expected = [100, 200, 300];

            $a = new Collection($array);
            $actual = $a->gather('id');

            $this->assertEquals($expected, $actual, 'KeyBy failed');
        }

        public function testMassDelete()
        {
            $this->init();
            $this->cleanupDB();

            $ids = [];
            // add 5 companies
            $ids[] = $this->createCompany();
            $ids[] = $this->createCompany();
            $ids[] = $this->createCompany();
            $ids[] = $this->createCompany();
            $lastId = $this->createCompany();


            $all = Company::where(
                function (QueryBuilder $qb) use ($ids) {
                    return $qb->where('id');
                }
            );
            $this->assertEquals(5, count($all), "Count should be 5");

            // delete the last one
            $all = Company::where(
                function (QueryBuilder $qb) use ($lastId) {
                    return $qb->where('id', $lastId);
                }
            );
            $all->delete();

            $all = Company::where(
                function (QueryBuilder $qb) use ($ids) {
                    return $qb->where('id');
                }
            );
            $this->assertEquals(4, count($all), "Count should be 4");

            // cleanup
            $this->cleanupDB();
        }

        public function testMassUpdate()
        {
            $this->init();
            $ids = [];
            // add 5 companies
            $ids[] = $this->createCompany();
            $ids[] = $this->createCompany();
            $ids[] = $this->createCompany();
            $this->createCompany();
            $this->createCompany();

            $nameUpdate = ['name' => 'X'];
            // update only 3
            Company::where(
                function (QueryBuilder $qb) use ($ids){
                    return $qb->whereIn('id', $ids);
                }
            )->update($nameUpdate);

            $all = Company::where(
                function (QueryBuilder $qb) use ($nameUpdate) {
                    return $qb->where($nameUpdate);
                }
            );
            $this->assertEquals(3, count($all), 'Updated count should be (3)');

            $namedArray = $all->gather('name');
            $expected = ['X', 'X', 'X'];

            $this->assertEquals($expected, $namedArray, "Gathered array should be ['X', 'X', 'X']");

            $this->cleanupDB();
        }
    }

