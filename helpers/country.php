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

/**
 *	@class Country
 *	@short Helper class to manipulate countries and associated metadata.
 */
abstract class Country
{
    const ALBANIA = 'AL';
    const ALGERIA = 'DZ';
    const ARGENTINA = 'AR';
    const ARMENIA = 'AM';
    const AUSTRALIA = 'AU';
    const AUSTRIA = 'AT';
    const AZERBAIJAN = 'AZ';

    const BAHRAIN = 'BH';
    const BANGLADESH = 'BD';
    const BELARUS = 'BY';
    const BELGIUM = 'BE';
    const BOLIVIA = 'BO';
    const BOSNIA_AND_HERZEGOVINA = 'BA';
    const BRAZIL = 'BR';
    const BRUNEI = 'BN';
    const BULGARIA = 'BG';

    const CAMBODIA = 'KH';
    const CANADA = 'CA';
    const CHILE = 'CL';
    const CHINA = 'CN';
    const COLOMBIA = 'CO';
    const COSTA_RICA = 'CR';
    const CROATIA = 'HR';
    const CUBA = 'CU';
    const CYPRUS = 'CY';
    const CZECH_REPUBLIC = 'CZ';

    const DENMARK = 'DK';
    const DOMINICAN_REPUBLIC = 'DO';

    const ECUADOR = 'EC';
    const EGYPT = 'EG';
    const EL_SALVADOR = 'SV';
    const ESTONIA = 'EE';

    const FINLAND = 'FI';
    const FRANCE = 'FR';

    const GEORGIA = 'GE';
    const GERMANY = 'DE';
    const GHANA = 'GH';
    const GREECE = 'GR';
    const GUATEMALA = 'GT';

    const HONDURAS = 'HN';
    const HONG_KONG = 'HK';
    const HUNGARY = 'HU';

    const ICELAND = 'IS';
    const INDIA = 'IN';
    const INDONESIA = 'ID';
    const IRAN = 'IR';
    const IRELAND = 'IE';
    const ISRAEL = 'IL';
    const ITALY = 'IT';
    const IVORY_COAST = 'CI';

    const JAPAN = 'JP';
    const JORDAN = 'JO';

    const KAZAKHSTAN = 'KH';
    const KENYA = 'KE';
    const KOSOVO = 'XK';
    const KUWAIT = 'KW';
    const KYRGYZSTAN = 'KG';

    const LATVIA = 'LV';
    const LEBANON = 'LB';
    const LIBYA = 'LY';
    const LITHUANIA = 'LT';
    const LUXEMBOURG = 'LU';

    const MACAU = 'MO';
    const MALAYSIA = 'MY';
    const MALTA = 'MT';
    const MAURITIUS = 'MU';
    const MEXICO = 'MX';
    const MOLDOVA = 'MD';
    const MONGOLIA = 'MN';
    const MONTENEGRO = 'ME';
    const MOROCCO = 'MA';

    const NETHERLANDS = 'NL';
    const NEW_ZEALAND = 'NZ';
    const NICARAGUA = 'NI';
    const NIGERIA = 'NG';
    const NORTH_KOREA = 'KP';
    const NORTH_MACEDONIA = 'MK';
    const NORWAY = 'NO';

    const PAKISTAN = 'PK';
    const PANAMA = 'PA';
    const PARAGUAY = 'PA';
    const PERU = 'PE';
    const PHILIPPINES = 'PH';
    const POLAND = 'PL';
    const PORTUGAL = 'PT';

    const ROMANIA = 'RO';
    const RUSSIA = 'RU';

    const SAUDI_ARABIA = 'SA';
    const SENEGAL = 'SN';
    const SERBIA = 'RS';
    const SINGAPORE = 'SG';
    const SLOVAKIA = 'SK';
    const SLOVENIA = 'SI';
    const SOUTH_AFRICA = 'ZA';
    const SOUTH_KOREA = 'KR';
    const SPAIN = 'ES';
    const SRI_LANKA = 'LK';
    const SWEDEN = 'SE';
    const SWITZERLAND = 'CH';
    const SYRIA = 'SY';

    const TAIWAN = 'TW';
    const TANZANIA = 'TZ';
    const THAILAND = 'TH';
    const TUNISIA = 'TN';
    const TURKEY = 'TR';

    const UKRAINE = 'UA';
    const UNITED_ARAB_EMIRATES = 'AE';
    const UNITED_KINGDOM = 'UK';
    const UNITED_STATES = 'US';
    const URUGUAY = 'UY';
    const UZBEKISTAN = 'UZ';

    const VENEZUELA = 'VE';
    const VIETNAM = 'VN';

    const ZAMBIA = 'ZM';
    const ZIMBABWE = 'ZW';

    const COUNTRIES = [
        self::ALBANIA,
        self::ALGERIA,
        self::ARGENTINA,
        self::ARMENIA,
        self::AUSTRALIA,
        self::AUSTRIA,
        self::AZERBAIJAN,

        self::BAHRAIN,
        self::BANGLADESH,
        self::BELARUS,
        self::BELGIUM,
        self::BOLIVIA,
        self::BOSNIA_AND_HERZEGOVINA,
        self::BRAZIL,
        self::BRUNEI,
        self::BULGARIA,

        self::CAMBODIA,
        self::CANADA,
        self::CHILE,
        self::CHINA,
        self::COLOMBIA,
        self::COSTA_RICA,
        self::CROATIA,
        self::CUBA,
        self::CYPRUS,
        self::CZECH_REPUBLIC,

        self::DENMARK,
        self::DOMINICAN_REPUBLIC,

        self::ECUADOR,
        self::EGYPT,
        self::EL_SALVADOR,
        self::ESTONIA,

        self::FINLAND,
        self::FRANCE,

        self::GEORGIA,
        self::GERMANY,
        self::GHANA,
        self::GREECE,
        self::GUATEMALA,

        self::HONDURAS,
        self::HONG_KONG,
        self::HUNGARY,

        self::ICELAND,
        self::INDIA,
        self::INDONESIA,
        self::IRAN,
        self::IRELAND,
        self::ISRAEL,
        self::ITALY,
        self::IVORY_COAST,

        self::JAPAN,
        self::JORDAN,

        self::KAZAKHSTAN,
        self::KENYA,
        self::KOSOVO,
        self::KUWAIT,
        self::KYRGYZSTAN,

        self::LATVIA,
        self::LEBANON,
        self::LIBYA,
        self::LITHUANIA,
        self::LUXEMBOURG,

        self::MACAU,
        self::MALAYSIA,
        self::MALTA,
        self::MAURITIUS,
        self::MEXICO,
        self::MOLDOVA,
        self::MONGOLIA,
        self::MONTENEGRO,
        self::MOROCCO,

        self::NETHERLANDS,
        self::NEW_ZEALAND,
        self::NICARAGUA,
        self::NIGERIA,
        self::NORTH_KOREA,
        self::NORTH_MACEDONIA,
        self::NORWAY,

        self::PAKISTAN,
        self::PANAMA,
        self::PARAGUAY,
        self::PERU,
        self::PHILIPPINES,
        self::POLAND,
        self::PORTUGAL,

        self::ROMANIA,
        self::RUSSIA,

        self::SAUDI_ARABIA,
        self::SENEGAL,
        self::SERBIA,
        self::SINGAPORE,
        self::SLOVAKIA,
        self::SLOVENIA,
        self::SOUTH_AFRICA,
        self::SOUTH_KOREA,
        self::SPAIN,
        self::SRI_LANKA,
        self::SWEDEN,
        self::SWITZERLAND,
        self::SYRIA,

        self::TAIWAN,
        self::TANZANIA,
        self::THAILAND,
        self::TUNISIA,
        self::TURKEY,

        self::UKRAINE,
        self::UNITED_ARAB_EMIRATES,
        self::UNITED_KINGDOM,
        self::UNITED_STATES,
        self::URUGUAY,
        self::UZBEKISTAN,

        self::VENEZUELA,
        self::VIETNAM,

        self::ZAMBIA,
        self::ZIMBABWE
    ];

    const FLAG_AZERBAIJAN = '🇦🇿';
    const FLAG_AUSTRIA = '🇦🇹';
    const FLAG_BELGIUM = '🇧🇪';
    const FLAG_CHINA = '🇨🇳';
    const FLAG_DENMARK = '🇩🇰';
    const FLAG_ESTONIA = '🇪🇪';
    const FLAG_FINLAND = '🇫🇮';
    const FLAG_FRANCE = '🇫🇷';
    const FLAG_GERMANY = '🇩🇪';
    const FLAG_GEORGIA = '🇬🇪';
    const FLAG_HONG_KONG = '🇭🇰';
    const FLAG_ICELAND = '🇮🇸';
    const FLAG_IRELAND = '🇮🇪';
    const FLAG_ISRAEL = '🇮🇱';
    const FLAG_ITALY = '🇮🇹';
    const FLAG_JAPAN = '🇯🇵';
    const FLAG_KAZAKHSTAN = '🇰🇿';
    const FLAG_KENYA = '🇰🇪';
    const FLAG_MALTA = '🇲🇹';
    const FLAG_MOROCCO = '🇲🇦';
    const FLAG_NETHERLANDS = '🇳🇱';
    const FLAG_NORWAY = '🇳🇴';
    const FLAG_POLAND = '🇵🇱';
    const FLAG_PORTUGAL = '🇵🇹';
    const FLAG_ROMANIA = '🇷🇴';
    const FLAG_RUSSIA = '🇷🇺';
    const FLAG_SINGAPORE = '🇸🇬';
    const FLAG_SOUTH_KOREA = '🇰🇷';
    const FLAG_SPAIN = '🇪🇸';
    const FLAG_SWEDEN = '🇸🇪';
    const FLAG_SWITZERLAND = '🇨🇭';
    const FLAG_TAIWAN = '🇹🇼';
    const FLAG_UKRAINE = '🇺🇦';
    const FLAG_UNITED_ARAB_EMIRATES = '🇦🇪';
    const FLAG_UNITED_KINGDOM = '🇬🇧';
    const FLAG_UNITED_STATES = '🇺🇸';
    const FLAG_ZAMBIA = '🇿🇲';
    const FLAG_ZIMBABWE = '🇿🇼';

    const FLAGS = [
        self::AZERBAIJAN => self::FLAG_AZERBAIJAN,
        self::AUSTRIA => self::FLAG_AUSTRIA,
        self::BELGIUM => self::FLAG_BELGIUM,
        self::CHINA => self::FLAG_CHINA,
        self::DENMARK => self::FLAG_DENMARK,
        self::ESTONIA => self::FLAG_ESTONIA,
        self::FINLAND => self::FLAG_FINLAND,
        self::FRANCE => self::FLAG_FRANCE,
        self::GERMANY => self::FLAG_GERMANY,
        self::GEORGIA => self::FLAG_GEORGIA,
        self::HONG_KONG => self::FLAG_HONG_KONG,
        self::ICELAND => self::FLAG_ICELAND,
        self::IRELAND => self::FLAG_IRELAND,
        self::ISRAEL => self::FLAG_ISRAEL,
        self::ITALY => self::FLAG_ITALY,
        self::JAPAN => self::FLAG_JAPAN,
        self::KAZAKHSTAN => self::FLAG_KAZAKHSTAN,
        self::KENYA => self::FLAG_KENYA,
        self::MALTA => self::FLAG_MALTA,
        self::MOROCCO => self::FLAG_MOROCCO,
        self::NETHERLANDS => self::FLAG_NETHERLANDS,
        self::NORWAY => self::FLAG_NORWAY,
        self::POLAND => self::FLAG_POLAND,
        self::PORTUGAL => self::FLAG_PORTUGAL,
        self::ROMANIA => self::FLAG_ROMANIA,
        self::RUSSIA => self::FLAG_RUSSIA,
        self::SINGAPORE => self::FLAG_SINGAPORE,
        self::SOUTH_KOREA => self::FLAG_SOUTH_KOREA,
        self::SPAIN => self::FLAG_SPAIN,
        self::SWEDEN => self::FLAG_SWEDEN,
        self::SWITZERLAND => self::FLAG_SWITZERLAND,
        self::TAIWAN => self::FLAG_TAIWAN,
        self::UKRAINE => self::FLAG_UKRAINE,
        self::UNITED_ARAB_EMIRATES => self::FLAG_UNITED_ARAB_EMIRATES,
        self::UNITED_KINGDOM => self::FLAG_UNITED_KINGDOM,
        self::UNITED_STATES => self::FLAG_UNITED_STATES,
        self::ZAMBIA => self::FLAG_ZAMBIA,
        self::ZIMBABWE => self::FLAG_ZIMBABWE
    ];

    public static function for(string $country_code): ?string
    {
        $cc = mb_strtoupper($country_code);
        if (in_array($cc, self::COUNTRIES)) {
            return $cc;
        }
        return null;
    }

    public static function flag(string $country): ?string
    {
        $cc = mb_strtoupper($country);
        if (array_key_exists($cc, self::FLAGS)) {
            return self::FLAGS[$cc];
        }
        return null;
    }
}
