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

    public static function flag($country)
    {
        switch ($country) {
            case self::AUSTRIA:
                return '🇦🇹';
            case self::BELGIUM:
                return '🇧🇪';
            case self::DENMARK:
                return '🇩🇰';
            case self::FINLAND:
                return '🇫🇮';
            case self::FRANCE:
                return '🇫🇷';
            case self::GERMANY:
                return '🇩🇪';
            case self::IRELAND:
                return '🇮🇪';
            case self::ITALY:
                return '🇮🇹';
            case self::NETHERLANDS:
                return '🇳🇱';
            case self::NORWAY:
                return '🇳🇴';
            case self::PORTUGAL:
                return '🇵🇹';
            case self::SPAIN:
                return '🇪🇸';
            case self::SWEDEN:
                return '🇸🇪';
            case self::SWITZERLAND:
                return '🇨🇭';
            case self::UNITED_KINGDOM:
                return '🇬🇧';
        }
    }
}
