<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

/**
 * Doctrine_Template_Sortable_TestCase
 *
 * @package     Doctrine
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.2
 * @version     $Revision$
 */
class Doctrine_Template_Sortable_TestCase extends Doctrine_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function prepareTables()
    {
        $this->tables[] = "SortableItem";
        $this->tables[] = "SortableItem1";
        $this->tables[] = "SortableItem2";
        $this->tables[] = "SortableItem3";
        parent::prepareTables();
    }

    public function prepareData()
    { }

    public function testRecordsAreSorted()
    {
        $item1 = new SortableItem();
        $item1->save();
        $item2 = new SortableItem();
        $item2->save();

        $this->assertTrue($item1->position < $item2->position);
    }

    public function testGetPrevious()
    {
        $item1 = new SortableItem();
        $item1->save();
        $item2 = new SortableItem();
        $item2->save();

        $this->assertEqual($item1, $item2->getPrevious());
    }

    public function testGetNext()
    {
        $item1 = new SortableItem();
        $item1->save();
        $item2 = new SortableItem();
        $item2->save();

        $this->assertEqual($item1->getNext(), $item2);
    }

    public function testSwapListItems()
    {
        $item1 = new SortableItem();
        $item1->save();
        $item2 = new SortableItem();
        $item2->save();
        $item3 = new SortableItem();
        $item3->save();

        $this->assertTrue($item1->position < $item2->position);
        $this->assertTrue($item2->position < $item3->position);
        $item3->swapWith($item1);
        $this->assertTrue($item3->position < $item2->position);
        $this->assertTrue($item2->position < $item1->position);
    }

    public function testMoveUp()
    {
        $item1 = new SortableItem();
        $item1->save();
        $item2 = new SortableItem();
        $item2->save();
        $item3 = new SortableItem();
        $item3->save();

        $this->assertTrue($item1->position < $item2->position);
        $this->assertTrue($item2->position < $item3->position);
        $item3->moveUp();
        $this->assertTrue($item1->position < $item3->position);
        $this->assertTrue($item3->position < $item2->position);
    }

    public function testMoveUpFirstDoesNothing()
    {
        $item1 = new SortableItem();
        $item1->save();
        $item2 = new SortableItem();
        $item2->save();

        $this->assertTrue($item1->position < $item2->position);
        $item1->moveUp();
        $this->assertTrue($item1->position < $item2->position);
    }

    public function testMoveDown()
    {
        $item1 = new SortableItem();
        $item1->save();
        $item2 = new SortableItem();
        $item2->save();
        $item3 = new SortableItem();
        $item3->save();

        $this->assertTrue($item1->position < $item2->position);
        $this->assertTrue($item2->position < $item3->position);
        $item1->moveDown();
        $this->assertTrue($item2->position < $item1->position);
        $this->assertTrue($item1->position < $item3->position);
    }

    public function testMoveDownLastDoesNothing()
    {
        $item1 = new SortableItem();
        $item1->save();
        $item2 = new SortableItem();
        $item2->save();

        $this->assertTrue($item1->position < $item2->position);
        $item2->moveDown();
        $this->assertTrue($item1->position < $item2->position);
    }

    public function testMoveToTop()
    {
        parent::prepareTables();
        $item1 = new SortableItem();
        $item1->save();
        $item2 = new SortableItem();
        $item2->save();
        $item3 = new SortableItem();
        $item3->save();

        $this->assertTrue($item1->position < $item2->position);
        $this->assertTrue($item2->position < $item3->position);

        $item3->moveToTop();

        $item1->refresh();
        $item2->refresh();
        $item3->refresh();

        $this->assertTrue($item3->position < $item1->position);
        $this->assertTrue($item1->position < $item2->position);
    }

    public function testMoveToBottom()
    {
        parent::prepareTables();
        $item1 = new SortableItem();
        $item1->save();
        $item2 = new SortableItem();
        $item2->save();
        $item3 = new SortableItem();
        $item3->save();

        $this->assertTrue($item1->position < $item2->position);
        $this->assertTrue($item2->position < $item3->position);

        $item1->moveToBottom();

        $item1->refresh();
        $item2->refresh();
        $item3->refresh();

        $this->assertTrue($item2->position < $item3->position);
        $this->assertTrue($item3->position < $item1->position);
    }

    public function testGetNextWithManyLists()
    {
        $item1_1 = new SortableItem1();
        $item1_1->listId = 1;
        $item1_1->save();
        $item2_1 = new SortableItem1();
        $item2_1->listId = 2;
        $item2_1->save();
        $item1_2 = new SortableItem1();
        $item1_2->listId = 1;
        $item1_2->save();
        $item2_2 = new SortableItem1();
        $item2_2->listId = 2;
        $item2_2->save();
        $this->assertEqual($item1_1->getNext(), $item1_2);
    }

    public function testSwapWithManyLists()
    {
        $item1_1 = new SortableItem1();
        $item1_1->listId = 1;
        $item1_1->save();
        $item2_1 = new SortableItem1();
        $item2_1->listId = 2;
        $item2_1->save();
        $item1_2 = new SortableItem1();
        $item1_2->listId = 1;
        $item1_2->save();
        $item2_2 = new SortableItem1();
        $item2_2->listId = 2;
        $item2_2->save();
        $this->assertTrue($item1_1->position < $item1_2->position);
        $this->assertTrue($item2_1->position < $item2_2->position);
        $item1_1->swapWith($item1_2);
        $this->assertTrue($item1_2->position < $item1_1->position);
        $this->assertTrue($item2_1->position < $item2_2->position);
    }

    public function testCannotSwapItemsFromDifferentLists()
    {
        $item1_1 = new SortableItem1();
        $item1_1->listId = 1;
        $item1_1->save();
        $item2_1 = new SortableItem1();
        $item2_1->listId = 2;
        $item2_1->save();

        try {
            $item1_1->swapWith($item2_1);
            $this->fail();
        } catch (Doctrine_Record_Exception $e) {
            $this->pass();
        }
    }

    public function testManyListsByMoreColumns()
    {
        $item1_1 = new SortableItem2();
        $item1_1->listId1 = 1;
        $item1_1->listId2 = 1;
        $item1_1->save();
        $item2_1 = new SortableItem2();
        $item2_1->listId1 = 1;
        $item2_1->listId2 = 2;
        $item2_1->save();
        $item1_2 = new SortableItem2();
        $item1_2->listId1 = 1;
        $item1_2->listId2 = 1;
        $item1_2->save();
        $item2_2 = new SortableItem2();
        $item2_2->listId1 = 1;
        $item2_2->listId2 = 2;
        $item2_2->save();
        $this->assertTrue($item1_1->position < $item1_2->position);
        $this->assertTrue($item2_1->position < $item2_2->position);
        $item1_1->swapWith($item1_2);
        $this->assertTrue($item1_2->position < $item1_1->position);
        $this->assertTrue($item2_1->position < $item2_2->position);
    }

    public function testAlias()
    {
        $item1 = new SortableItem3();
        $item1->save();
        $item2 = new SortableItem3();
        $item2->save();
        $item2->moveUp();

        $this->assertTrue($item2->myPos < $item1->myPos);
    }

    public function testFindFirstAndLast()
    {
        parent::prepareTables();
        $item1 = new SortableItem();
        $item1->save();
        $item2 = new SortableItem();
        $item2->save();

        $this->assertEqual($item1->id, Doctrine::getTable('SortableItem')->findFirst()->id);
        $this->assertEqual($item2->id, Doctrine::getTable('SortableItem')->findLast()->id);
    }

    public function testFindFirstAndLastWithManyLists()
    {
        parent::prepareTables();
        $item1_1 = new SortableItem1();
        $item1_1->listId = 1;
        $item1_1->save();
        $item2_1 = new SortableItem1();
        $item2_1->listId = 2;
        $item2_1->save();
        $item1_2 = new SortableItem1();
        $item1_2->listId = 1;
        $item1_2->save();
        $item2_2 = new SortableItem1();
        $item2_2->listId = 2;
        $item2_2->save();

        $table = Doctrine::getTable('SortableItem1');

        $this->assertEqual($item1_1->id, $table->findFirst(array('listId' => 1))->id);
        $this->assertEqual($item1_2->id, $table->findLast(array('listId' => 1))->id);
        $this->assertEqual($item2_1->id, $table->findFirst(array('listId' => 2))->id);
        $this->assertEqual($item2_2->id, $table->findLast(array('listId' => 2))->id);
    }

    public function testFindFirstRequiresListId()
    {
        parent::prepareTables();
        $table = Doctrine::getTable('SortableItem2');
        try {
            $table->findFirst();
            $this->fail();
        } catch (Doctrine_Record_Exception $e) { }
        try {
            $table->findFirst('1');
            $this->fail();
        } catch (Doctrine_Record_Exception $e) { }
        try {
            $table->findFirst(array());
            $this->fail();
        } catch (Doctrine_Record_Exception $e) { }
        try {
            $table->findFirst(array('listId1' => 1));
            $this->fail();
        } catch (Doctrine_Record_Exception $e) { }
        try {
            $table->findFirst(array('listId1' => 1, 'listId2' => 1, 'listId3' => 1));
            $this->fail();
        } catch (Doctrine_Record_Exception $e) { }
    }

    public function testManyListsWithNullValues()
    {
        parent::prepareTables();
        $item1_1 = new SortableItem1();
        $item1_1->save();
        $item2_1 = new SortableItem1();
        $item2_1->listId = 2;
        $item2_1->save();
        $item1_2 = new SortableItem1();
        $item1_2->save();
        $item2_2 = new SortableItem1();
        $item2_2->listId = 2;
        $item2_2->save();

        $this->assertTrue($item1_1->position < $item1_2->position);
        $this->assertTrue($item2_1->position < $item2_2->position);
        $item1_2->moveUp();
        $item1_1->refresh();
        $item1_2->refresh();
        $item2_1->refresh();
        $item2_2->refresh();
        $this->assertTrue($item1_2->position < $item1_1->position);
        $this->assertTrue($item2_1->position < $item2_2->position);

        $table = Doctrine::getTable('SortableItem1');
        $this->assertEqual($item1_1->id, $table->findLast(array('listId' => null))->id);

        $item1_1->moveToTop();
        $item1_1->refresh();
        $item1_2->refresh();
        $item2_1->refresh();
        $item2_2->refresh();
        $this->assertTrue($item1_1->position < $item1_2->position);
        $this->assertTrue($item2_1->position < $item2_2->position);
    }
}

class SortableItem extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('my_item');
        $this->hasColumn('name', 'string', 50);
    }

    public function setUp()
    {
        parent::setUp();
        $this->actAs('Sortable');
    }
}

class SortableItem1 extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('my_item1');
        $this->hasColumn('name', 'string', 50);
        $this->hasColumn('listId', 'integer');
    }

    public function setUp()
    {
        parent::setUp();
        $this->actAs('Sortable', array('manyListsBy' => array('listId')));
    }
}

class SortableItem2 extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('my_item2');
        $this->hasColumn('name', 'string', 50);
        $this->hasColumn('listId1', 'integer');
        $this->hasColumn('listId2', 'integer');
    }

    public function setUp()
    {
        parent::setUp();
        $this->actAs('Sortable', array('manyListsBy' => array('listId1', 'listId2')));
    }
}

class SortableItem3 extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('my_item3');
        $this->hasColumn('name', 'string', 50);
    }

    public function setUp()
    {
        parent::setUp();
        $this->actAs('Sortable', array('name' => 'pos', 'alias' => 'myPos'));
    }
}
