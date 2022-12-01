<?php
/**
 * @format
 */

require_once __DIR__ . '/../../../config/db.conf.php';
require_once __DIR__ . '/../../utils.php';
require_once __DIR__ . '/../base_test.php';

class User extends ActiveRecord
{
    protected $table_name = 'user_profiles';
    protected static $primary_key_name = 'username';
    protected $primary_key = 'username';
    protected $foreign_key_name = 'username';
}

class Account extends ActiveRecord
{
    protected $table_name = 'user_accounts';
}

class UserTest extends UnitTest
{
    /**
     * @before
     */
    function setUp(): void
    {
    }

    /**
     * @after
     */
    function teardown(): void
    {
        if (isset($this->user)) {
            $this->user->delete();
            unset($this->user);
        }

        if (isset($this->account)) {
            $this->account->delete();
            unset($this->account);
        }
    }

    public function test_actual_primary_key_column_set_on_save()
    {
        $this->user = new User(array(
            'username' => 'brixton'
        ));
        $this->user->_force_create = true;

        $this->assertNull($this->user->id, 'User id field is not null');
        $ret = $this->user->save();
        $this->assertTrue($ret, 'User was not saved');
        $this->assertNotNull($this->user->id, 'User id field is null');

        $other = new User();
        $other->find_by_id('brixton');

        $this->assertEquals($other->id, $this->user->id);
        $this->assertEquals($other->username, $this->user->username);
        $this->assertNotEquals($other->username, $this->user->id);
        $this->assertNotEquals($other->id, $this->user->username);
    }

    public function test_actual_primary_key_column_has_one()
    {
        $this->user = new User(array(
            'username' => 'croydon'
        ));
        $this->user->_force_create = true;

        $this->assertNull($this->user->id, 'User id field is not null');
        $ret = $this->user->save();
        $this->assertTrue($ret, 'User was not saved');
        $this->assertNotNull($this->user->id, 'User id field is null');

        $this->account = new Account(array(
            'username' => 'croydon'
        ));

        $this->assertNull($this->account->id, 'Account id field is null');
        $ret = $this->account->save();
        $this->assertTrue($ret, 'Account was not saved');
        $this->assertNotNull($this->account->id, 'Account id field is not null');

        $this->user->has_one(Account::class);
        $this->assertNotNull($this->user->account, 'User account is null');
        $this->assertEquals(
            $this->user->account->user->username,
            $this->user->username,
            'Username does not match the username of the user associated to the account'
        );
    }
}
?>
