<?php
/**
 *  Project EmeRails - Codename Ocarina
 *
 *  Copyright (c) 2008, 2017 Claudio Procida
 *  http://www.emeraldion.it
 *
 * @format
 */

require_once __DIR__ . '/../include/common.inc.php';

use Emeraldion\EmeRails\Db;

/**
 *  @class Relationship
 *  @short Models a relationship between two model classes
 *  @details TBD.
 */
class Relationship
{
    const ONE_TO_ONE = 'one_to_one';

    const ONE_TO_MANY = 'one_to_many';

    const MANY_TO_MANY = 'many_to_many';

    public static function one_to_one($classname, $other_classname)
    {
        return new self($classname, $other_classname, self::ONE_TO_ONE);
    }

    public static function one_to_many($classname, $other_classname)
    {
        return new self($classname, $other_classname, self::ONE_TO_MANY);
    }

    public static function many_to_many($classname, $other_classname)
    {
        return new self($classname, $other_classname, self::MANY_TO_MANY);
    }

    private function __construct($classname, $other_classname, $cardinality = self::ONE_TO_ONE)
    {
        $this->classname = $classname;
        $this->other_classname = $other_classname;
        $this->cardinality = $cardinality;
    }

    public function get_table_name()
    {
        if (!$this->cardinality == self::MANY_TO_MANY) {
            return null;
        }

        if (!isset($this->table_name)) {
            switch ($this->cardinality) {
                case self::MANY_TO_MANY:
                    $parts = explode('\\', $this->classname);
                    $classname = $parts[count($parts) - 1];

                    $other_parts = explode('\\', $this->other_classname);
                    $other_classname = $other_parts[count($other_parts) - 1];

                    $table_names = array(
                        class_name_to_table_name($classname),
                        class_name_to_table_name($other_classname)
                    );
                    sort($table_names);

                    $this->table_name = implode('_', $table_names);
                    break;
                default:
                    $this->table_name = null;
                    break;
            }
        }
        return $this->table_name;
    }

    public function between($member, $other_member)
    {
        $classes = array($this->classname, $this->other_classname);

        if (!in_array(get_class($member), $classes)) {
            throw new Exception(
                sprintf(
                    "Argument 1 expected of class '%s' or '%s', but got '%s' instead.",
                    $this->classname,
                    $this->other_classname,
                    get_class($member)
                )
            );
        }

        if (!in_array(get_class($other_member), $classes)) {
            throw new Exception(
                sprintf(
                    "Argument 2 expected of class '%s' or '%s', but got '%s' instead.",
                    $this->classname,
                    $this->other_classname,
                    get_class($other_member)
                )
            );
        }

        return new RelationshipInstance($member, $other_member, $this);
    }

    protected function _introspect()
    {
        // DESCRIBE table_name and introspect extra columns (relationship attributes)
    }
}

class RelationshipInstance
{
    public function __construct($member, $other_member, $relationship)
    {
        $this->member = $member;
        $this->other_member = $other_member;
        $this->relationship = $relationship;
    }

    public function save()
    {
        $conn = Db::get_connection();

        $member_pk = $this->member->get_primary_key();
        $member_fk = $this->member->get_foreign_key_name();

        $other_member_pk = $this->other_member->get_primary_key();
        $other_member_fk = $this->other_member->get_foreign_key_name();

        $ret = false;

        switch ($this->relationship->cardinality) {
            case Relationship::ONE_TO_ONE:
            case Relationship::ONE_TO_MANY:
                if ($this->member->has_column($other_member_fk)) {
                    $child = $this->member;
                    $child_pk = $member_pk;

                    $parent = $this->other_member;
                    $parent_pk = $other_member_pk;
                    $parent_fk = $other_member_fk;
                } elseif ($this->other_member->has_column($member_fk)) {
                    $child = $this->other_member;
                    $child_pk = $other_member_pk;

                    $parent = $this->member;
                    $parent_pk = $member_pk;
                    $parent_fk = $member_fk;
                } else {
                    throw new Exception(
                        sprintf(
                            "Cannot find a column '%s' in table '%s' or a column '%s' in table '%s'.",
                            $member_fk,
                            $this->other_member->get_table_name(),
                            $other_member_fk,
                            $this->member->get_table_name()
                        )
                    );
                }
                $conn->prepare(
                    "UPDATE `{1}` SET `{2}` = '{3}' WHERE `{4}` = '{5}'",
                    $child->get_table_name(),
                    $parent_fk,
                    $parent->$parent_pk,
                    $child_pk,
                    $child->$child_pk
                );
                $conn->exec();

                // Update the model to avoid a reload from DB
                $child->$parent_fk = $parent->$parent_pk;

                $ret = true;
                break;

            case Relationship::MANY_TO_MANY:
                $conn->prepare(
                    "INSERT INTO `{1}` (`{2}`, `{3}`) VALUES ('{4}', '{5}')",
                    $this->relationship->get_table_name(),
                    $member_fk,
                    $other_member_fk,
                    $this->member->$member_pk,
                    $this->other_member->$other_member_pk
                );
                $conn->exec();

                // Update models to avoid a reload from DB
                $member_collection = singularize($this->member->get_table_name());
                $other_member_collection = singularize($this->other_member->get_table_name());
                if (is_array($this->member->$other_member_collection)) {
                    if (
                        !array_key_exists(
                            $this->other_member->$other_member_pk,
                            $this->member->$other_member_collection
                        )
                    ) {
                        $this->member->$other_member_collection[$this->other_member->$other_member_pk] =
                            $this->other_member;
                    }
                } else {
                    $this->member->$other_member_collection = array(
                        $this->other_member->$other_member_pk => $this->other_member
                    );
                }
                if (is_array($this->other_member->$member_collection)) {
                    if (!array_key_exists($this->member->$member_pk, $this->other_member->$member_collection)) {
                        $this->other_member->$member_collection[$this->member->$member_pk] = $this->member;
                    }
                } else {
                    $this->other_member->$member_collection = array(
                        $this->member->$member_pk => $this->member
                    );
                }
                $ret = true;
                break;
        }

        Db::close_connection($conn);

        return $ret;
    }

    public function delete()
    {
        $conn = Db::get_connection();

        $member_pk = $this->member->get_primary_key();
        $member_fk = $this->member->get_foreign_key_name();

        $other_member_pk = $this->other_member->get_primary_key();
        $other_member_fk = $this->other_member->get_foreign_key_name();

        $ret = false;

        switch ($this->relationship->cardinality) {
            case Relationship::ONE_TO_ONE:
            case Relationship::ONE_TO_MANY:
                if ($this->member->has_column($other_member_fk)) {
                    $child = $this->member;
                    $child_pk = $member_pk;

                    $parent = $this->other_member;
                    $parent_fk = $other_member_fk;
                } elseif ($this->other_member->has_column($member_fk)) {
                    $child = $this->other_member;
                    $child_pk = $other_member_pk;

                    $parent = $this->member;
                    $parent_fk = $member_fk;
                } else {
                    throw new Exception(
                        sprint(
                            "Cannot find a column '%s' in table '%s' or a column '%s' in table '%s'.",
                            $member_fk,
                            $this->other_member->get_table_name(),
                            $other_member_fk,
                            $this->member->get_table_name()
                        )
                    );
                }
                $conn->prepare(
                    "UPDATE `{1}` SET `{2}` = NULL WHERE `{3}` = '{4}'",
                    $child->get_table_name(),
                    $parent_fk,
                    $child_pk,
                    $child->$child_pk
                );
                $conn->exec();
                break;

            case Relationship::MANY_TO_MANY:
                $conn->prepare(
                    "DELETE FROM `{1}` WHERE `{2}` = '{3}' AND `{4}` = '{5}'",
                    $this->relationship->get_table_name(),
                    $member_fk,
                    $this->member->$member_pk,
                    $other_member_fk,
                    $this->other_member->$other_member_pk
                );
                $conn->exec();

                $ret = true;
                break;
        }

        Db::close_connection($conn);

        return $ret;
    }
}
?>
