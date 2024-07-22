<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2024
 *
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
    function setUp(): void
    {
    }

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

    public function test_get_writeonly()
    {
        $this->user = new User([
            'username' => 'brixton',
            'password' => sha1('brixton')
        ]);

        $this->assertEquals('***', $this->user->password);

        $this->user->_force_create = true;
        $this->user->save();

        $this->assertEquals('***', $this->user->password);
    }

    public function test_debug_info_writeonly()
    {
        $this->user = new User([
            'username' => 'brixton',
            'password' => sha1('brixton')
        ]);
        $this->user->_force_create = true;
        $this->user->save();

        ob_start();
        print_r($this->user);
        $printed = ob_get_clean();
        $this->assertNotNull($printed);
        $this->assertThat(
            $printed,
            $this->matchesRegularExpression(
                <<<EOT
                /User Object
                \(
                    \[id\] => \d+
                    \[username\] => brixton
                \)
                /
                EOT
            )
        );

        ob_start();
        var_dump($this->user);
        $printed = ob_get_clean();
        $this->assertNotNull($printed);
        $this->assertThat(
            $printed,
            $this->matchesRegularExpression(
                <<<EOT
                /object\(User\)#\d+ \(2\) \{
                  \["id"\]=>
                  int\(\d+\)
                  \["username"\]=>
                  string\(7\) "brixton"
                \}
                /
                EOT
            )
        );
    }

    public function test_actual_primary_key_column_set_on_save()
    {
        $this->user = new User([
            'username' => 'brixton'
        ]);
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
        $this->user = new User([
            'username' => 'croydon'
        ]);
        $this->user->_force_create = true;

        $this->assertNull($this->user->id, 'User id field is not null');
        $ret = $this->user->save();
        $this->assertTrue($ret, 'User was not saved');
        $this->assertNotNull($this->user->id, 'User id field is null');

        $this->account = new Account([
            'username' => 'croydon',
            'password' => 'croydon'
        ]);

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
