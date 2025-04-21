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

    public function test_flag()
    {
        $this->assertEquals('🇦🇿', Country::flag(Country::AZERBAIJAN));
        $this->assertEquals('🇦🇹', Country::flag(Country::AUSTRIA));
        $this->assertEquals('🇧🇪', Country::flag(Country::BELGIUM));
        $this->assertEquals('🇩🇰', Country::flag(Country::DENMARK));
        $this->assertEquals('🇪🇪', Country::flag(Country::ESTONIA));
        $this->assertEquals('🇫🇮', Country::flag(Country::FINLAND));
        $this->assertEquals('🇫🇷', Country::flag(Country::FRANCE));
        $this->assertEquals('🇩🇪', Country::flag(Country::GERMANY));
        $this->assertEquals('🇬🇪', Country::flag(Country::GEORGIA));
        $this->assertEquals('🇮🇸', Country::flag(Country::ICELAND));
        $this->assertEquals('🇮🇪', Country::flag(Country::IRELAND));
        $this->assertEquals('🇮🇱', Country::flag(Country::ISRAEL));
        $this->assertEquals('🇮🇹', Country::flag(Country::ITALY));
        $this->assertEquals('🇯🇵', Country::flag(Country::JAPAN));
        $this->assertEquals('🇰🇪', Country::flag(Country::KENYA));
        $this->assertEquals('🇲🇹', Country::flag(Country::MALTA));
        $this->assertEquals('🇳🇱', Country::flag(Country::NETHERLANDS));
        $this->assertEquals('🇳🇴', Country::flag(Country::NORWAY));
        $this->assertEquals('🇵🇱', Country::flag(Country::POLAND));
        $this->assertEquals('🇵🇹', Country::flag(Country::PORTUGAL));
        $this->assertEquals('🇷🇴', Country::flag(Country::ROMANIA));
        $this->assertEquals('🇷🇺', Country::flag(Country::RUSSIA));
        $this->assertEquals('🇪🇸', Country::flag(Country::SPAIN));
        $this->assertEquals('🇸🇪', Country::flag(Country::SWEDEN));
        $this->assertEquals('🇨🇭', Country::flag(Country::SWITZERLAND));
        $this->assertEquals('🇺🇦', Country::flag(Country::UKRAINE));
        $this->assertEquals('🇦🇪', Country::flag(Country::UNITED_ARAB_EMIRATES));
        $this->assertEquals('🇬🇧', Country::flag(Country::UNITED_KINGDOM));
        $this->assertEquals('🇺🇸', Country::flag(Country::UNITED_STATES));
        $this->assertEquals('🇿🇲', Country::flag(Country::ZAMBIA));
        $this->assertEquals('🇿🇼', Country::flag(Country::ZIMBABWE));
    }

    public function test_flag_unknown()
    {
        $this->assertNull(Country::flag('ZZ'));
        $this->assertNull(Country::flag('QQ'));
    }

    public function test_in_eu()
    {
        $this->assertTrue(Country::in_eu(Country::CZECHIA));
        $this->assertTrue(Country::in_eu(Country::CZECH_REPUBLIC));
        $this->assertTrue(Country::in_eu(Country::ITALY));
        $this->assertTrue(Country::in_eu(Country::NETHERLANDS));

        $this->assertFalse(Country::in_eu(Country::SWITZERLAND));
        $this->assertFalse(Country::in_eu(Country::UNITED_KINGDOM));
        $this->assertFalse(Country::in_eu(Country::UNITED_STATES));
    }
}
