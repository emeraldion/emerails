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

require_once __DIR__ . '/../base_test.php';

use Emeraldion\EmeRails\Helpers\Country;

/**
 *	@class CountryTest
 *	@short Test case for Country helper object.
 */
class CountryUnitTest extends UnitTest
{
    /**
     *	@fn test_for
     *	@short Test method for for().
     */
    public function test_for()
    {
        $this->assertEquals(Country::AUSTRIA, Country::for('at'));
        $this->assertEquals(Country::AUSTRIA, Country::for('AT'));
        $this->assertEquals(Country::AUSTRIA, Country::for('At'));

        $this->assertEquals(Country::ITALY, Country::for('it'));
        $this->assertEquals(Country::ITALY, Country::for('IT'));
        $this->assertEquals(Country::ITALY, Country::for('It'));

        $this->assertEquals(Country::SOUTH_AFRICA, Country::for('za'));
        $this->assertEquals(Country::SOUTH_AFRICA, Country::for('ZA'));
        $this->assertEquals(Country::SOUTH_AFRICA, Country::for('Za'));
    }

    public function test_for_unknown()
    {
        $this->assertNull(Country::for('zz'));
        $this->assertNull(Country::for('ZZ'));
        $this->assertNull(Country::for('Zz'));
    }
}
