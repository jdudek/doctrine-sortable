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
 * Doctrine_Template_Sortable
 *
 * @package     Doctrine
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.2
 * @version     $Revision$
 */
class Doctrine_Template_Sortable extends Doctrine_Template
{
    protected $_options = array(
        'name' => 'position',
        'alias' => '',
        'manyListsBy' => array(),
    );

    public function setTableDefinition()
    {
        $name = $this->_options['name'];
        if ($this->_options['alias']) {
            $name .= ' as ' . $this->_options['alias'];
        }
        $this->hasColumn($name, 'integer');
        $this->addListener(new Doctrine_Template_Listener_Sortable($this->_options));
    }

    public function getPrevious()
    {
        return $this->getPreviousOrNext('<', 'DESC');
    }

    public function getNext()
    {
        return $this->getPreviousOrNext('>', 'ASC');
    }

    public function swapWith(Doctrine_Record $record2)
    {
        $record1 = $this->getInvoker();
        $name = $this->getName();

        foreach ($this->_options['manyListsBy'] as $col) {
            if ($record1->$col != $record2->$col) {
                throw new Doctrine_Record_Exception('Cannot swap items from different lists.');
            }
        }

        $conn = $this->getTable()->getConnection();
        $conn->beginTransaction();

        $pos1 = $record1->$name;
        $pos2 = $record2->$name;
        $record1->$name = $pos2;
        $record2->$name = $pos1;
        $record1->save();
        $record2->save();

        $conn->commit();
    }

    public function moveUp()
    {
        $prev = $this->getInvoker()->getPrevious();
        if ($prev) {
            $this->getInvoker()->swapWith($prev);
        }
    }

    public function moveDown()
    {
        $next = $this->getInvoker()->getNext();
        if ($next) {
            $this->getInvoker()->swapWith($next);
        }
    }

    public function moveToTop()
    {
        $this->moveToTopOrBottom('<', 'DESC');
    }

    public function moveToBottom()
    {
        $this->moveToTopOrBottom('>', 'ASC');
    }

    public function findFirstTableProxy($whichList = array())
    {
        return $this->findFirstOrLast($whichList, 'ASC');
    }

    public function findLastTableProxy($whichList = array())
    {
        return $this->findFirstOrLast($whichList, 'DESC');
    }

    private function getPreviousOrNext($rel, $ord)
    {
        $name = $this->getName();
        $q = $this->getInvoker()->getTable()->createQuery()
            ->addWhere("$name $rel ?", $this->getInvoker()->$name)
            ->orderBy("$name $ord");
        foreach ($this->_options['manyListsBy'] as $col) {
            if (is_null($this->getInvoker()->$col)) {
                $q->addWhere($col . ' IS NULL');
            } else {
                $q->addWhere($col . ' = ?', $this->getInvoker()->$col);
            }
        }
        return $q->fetchOne();
    }

    private function moveToTopOrBottom($rel, $ord)
    {
        $conn = $this->getTable()->getConnection();
        $conn->beginTransaction();

        $name = $this->getName();

        $q = $this->_table->createQuery()
            ->addWhere("$name $rel ?", $this->getInvoker()->$name)
            ->orderBy("$name $ord");
        foreach ($this->_options['manyListsBy'] as $col) {
            if (is_null($this->getInvoker()->$col)) {
                $q->addWhere($col . ' IS NULL');
            } else {
                $q->addWhere($col . ' = ?', $this->getInvoker()->$col);
            }
        }
        foreach ($q->execute() as $item) {
            $this->getInvoker()->swapWith($item);
        }
        $conn->commit();
    }

    private function findFirstOrLast($whichList, $order)
    {
        $name = $this->getName();
        $q = $this->getInvoker()->getTable()->createQuery()->orderBy("$name $order");
        if (!is_array($whichList) ||
            array_diff($this->_options['manyListsBy'], array_keys($whichList)) ||
            array_diff(array_keys($whichList), $this->_options['manyListsBy'])
        ) {
            throw new Doctrine_Record_Exception('Improper list identifier.');
        }
        foreach ($whichList as $col => $val) {
            if (is_null($val)) {
                $q->addWhere("$col IS NULL");
            } else {
                $q->addWhere("$col = ?", $val);
            }
        }
        return $q->fetchOne();
    }

    private function getName()
    {
        return $this->getInvoker()->getTable()->getFieldName($this->_options['name']);
    }
}