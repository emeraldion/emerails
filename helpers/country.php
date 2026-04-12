<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2026
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
    const ANDORRA = 'AD';
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

    const CAPE_VERDE = 'CV';
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
    const ETHIOPIA = 'ET';

    const FINLAND = 'FI';
    const FRANCE = 'FR';

    const GEORGIA = 'GE';
    const GERMANY = 'DE';
    const GHANA = 'GH';
    const GIBRALTAR = 'GI';
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

    const KAZAKHSTAN = 'KZ';
    const KENYA = 'KE';
    const KOSOVO = 'XK';
    const KUWAIT = 'KW';
    const KYRGYZSTAN = 'KG';

    const LATVIA = 'LV';
    const LEBANON = 'LB';
    const LIBYA = 'LY';
    const LIECHTENSTEIN = 'LI';
    const LITHUANIA = 'LT';
    const LUXEMBOURG = 'LU';

    const MACAU = 'MO';
    const MALAYSIA = 'MY';
    const MALTA = 'MT';
    const MAURITIUS = 'MU';
    const MEXICO = 'MX';
    const MOLDOVA = 'MD';
    const MONACO = 'MC';
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
    const PARAGUAY = 'PY';
    const PERU = 'PE';
    const PHILIPPINES = 'PH';
    const POLAND = 'PL';
    const PORTUGAL = 'PT';

    const ROMANIA = 'RO';
    const RUSSIA = 'RU';

    const SAN_MARINO = 'SM';
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

    const VATICAN_CITY = 'VA';
    const VENEZUELA = 'VE';
    const VIETNAM = 'VN';

    const ZAMBIA = 'ZM';
    const ZIMBABWE = 'ZW';

    // Aliases

    const CZECHIA = self::CZECH_REPUBLIC;

    const ALL_COUNTRIES = [
        self::ALBANIA,
        self::ALGERIA,
        self::ANDORRA,
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

        self::CAPE_VERDE,
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
        self::ETHIOPIA,

        self::FINLAND,
        self::FRANCE,

        self::GEORGIA,
        self::GERMANY,
        self::GIBRALTAR,
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
        self::LIECHTENSTEIN,
        self::LITHUANIA,
        self::LUXEMBOURG,

        self::MACAU,
        self::MALAYSIA,
        self::MALTA,
        self::MAURITIUS,
        self::MEXICO,
        self::MOLDOVA,
        self::MONACO,
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

        self::SAN_MARINO,
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

        self::VATICAN_CITY,
        self::VENEZUELA,
        self::VIETNAM,

        self::ZAMBIA,
        self::ZIMBABWE
    ];

    // Source: https://european-union.europa.eu/principles-countries-history/eu-countries_en
    const EU_COUNTRIES = [
        self::AUSTRIA,
        self::BELGIUM,
        self::BULGARIA,
        self::CROATIA,
        self::CYPRUS,
        self::CZECHIA,
        self::DENMARK,
        self::ESTONIA,
        self::FINLAND,
        self::FRANCE,
        self::GERMANY,
        self::GREECE,
        self::HUNGARY,
        self::IRELAND,
        self::ITALY,
        self::LATVIA,
        self::LITHUANIA,
        self::LUXEMBOURG,
        self::MALTA,
        self::NETHERLANDS,
        self::POLAND,
        self::PORTUGAL,
        self::ROMANIA,
        self::SLOVAKIA,
        self::SLOVENIA,
        self::SPAIN,
        self::SWEDEN
    ];

    const FLAG_ALBANIA = '🇦🇱';
    const FLAG_ALGERIA = '🇩🇿';
    const FLAG_ANDORRA = '🇦🇩';
    const FLAG_ARGENTINA = '🇦🇷';
    const FLAG_ARMENIA = '🇦🇲';
    const FLAG_AUSTRALIA = '🇦🇺';
    const FLAG_AUSTRIA = '🇦🇹';
    const FLAG_AZERBAIJAN = '🇦🇿';

    const FLAG_BAHRAIN = '🇧🇭';
    const FLAG_BANGLADESH = '🇧🇩';
    const FLAG_BELARUS = '🇧🇾';
    const FLAG_BELGIUM = '🇧🇪';
    const FLAG_BOLIVIA = '🇧🇴';
    const FLAG_BOSNIA_AND_HERZEGOVINA = '🇧🇦';
    const FLAG_BRAZIL = '🇧🇷';
    const FLAG_BRUNEI = '🇧🇳';
    const FLAG_BULGARIA = '🇧🇬';

    const FLAG_CAPE_VERDE = '🇨🇻';
    const FLAG_CAMBODIA = '🇰🇭';
    const FLAG_CANADA = '🇨🇦';
    const FLAG_CHILE = '🇨🇱';
    const FLAG_CHINA = '🇨🇳';
    const FLAG_COLOMBIA = '🇨🇴';
    const FLAG_COSTA_RICA = '🇨🇷';
    const FLAG_CROATIA = '🇭🇷';
    const FLAG_CUBA = '🇨🇺';
    const FLAG_CYPRUS = '🇨🇾';
    const FLAG_CZECH_REPUBLIC = '🇨🇿';

    const FLAG_DENMARK = '🇩🇰';
    const FLAG_DOMINICAN_REPUBLIC = '🇩🇴';

    const FLAG_ECUADOR = '🇪🇨';
    const FLAG_EGYPT = '🇪🇬';
    const FLAG_EL_SALVADOR = '🇸🇻';
    const FLAG_ESTONIA = '🇪🇪';
    const FLAG_ETHIOPIA = '🇪🇹';

    const FLAG_FINLAND = '🇫🇮';
    const FLAG_FRANCE = '🇫🇷';

    const FLAG_GEORGIA = '🇬🇪';
    const FLAG_GERMANY = '🇩🇪';
    const FLAG_GIBRALTAR = '🇬🇮';
    const FLAG_GHANA = '🇬🇭';
    const FLAG_GREECE = '🇬🇷';
    const FLAG_GUATEMALA = '🇬🇹';

    const FLAG_HONDURAS = '🇭🇳';
    const FLAG_HONG_KONG = '🇭🇰';
    const FLAG_HUNGARY = '🇭🇺';

    const FLAG_ICELAND = '🇮🇸';
    const FLAG_INDIA = '🇮🇳';
    const FLAG_INDONESIA = '🇮🇩';
    const FLAG_IRAN = '🇮🇷';
    const FLAG_IRELAND = '🇮🇪';
    const FLAG_ISRAEL = '🇮🇱';
    const FLAG_ITALY = '🇮🇹';
    const FLAG_IVORY_COAST = '🇨🇮';

    const FLAG_JAPAN = '🇯🇵';
    const FLAG_JORDAN = '🇯🇴';

    const FLAG_KAZAKHSTAN = '🇰🇿';
    const FLAG_KENYA = '🇰🇪';
    const FLAG_KOSOVO = '🇽🇰';
    const FLAG_KUWAIT = '🇰🇼';
    const FLAG_KYRGYZSTAN = '🇰🇬';

    const FLAG_LATVIA = '🇱🇻';
    const FLAG_LEBANON = '🇱🇧';
    const FLAG_LIBYA = '🇱🇾';
    const FLAG_LIECHTENSTEIN = '🇱🇮';
    const FLAG_LITHUANIA = '🇱🇹';
    const FLAG_LUXEMBOURG = '🇱🇺';

    const FLAG_MACAU = '🇲🇴';
    const FLAG_MALAYSIA = '🇲🇾';
    const FLAG_MALTA = '🇲🇹';
    const FLAG_MAURITIUS = '🇲🇺';
    const FLAG_MEXICO = '🇲🇽';
    const FLAG_MOLDOVA = '🇲🇩';
    const FLAG_MONACO = '🇲🇨';
    const FLAG_MONGOLIA = '🇲🇳';
    const FLAG_MONTENEGRO = '🇲🇪';
    const FLAG_MOROCCO = '🇲🇦';

    const FLAG_NETHERLANDS = '🇳🇱';
    const FLAG_NEW_ZEALAND = '🇳🇿';
    const FLAG_NICARAGUA = '🇳🇮';
    const FLAG_NIGERIA = '🇳🇬';
    const FLAG_NORTH_KOREA = '🇰🇵';
    const FLAG_NORTH_MACEDONIA = '🇲🇰';
    const FLAG_NORWAY = '🇳🇴';

    const FLAG_PAKISTAN = '🇵🇰';
    const FLAG_PANAMA = '🇵🇦';
    const FLAG_PARAGUAY = '🇵🇾';
    const FLAG_PERU = '🇵🇪';
    const FLAG_PHILIPPINES = '🇵🇭';
    const FLAG_POLAND = '🇵🇱';
    const FLAG_PORTUGAL = '🇵🇹';

    const FLAG_ROMANIA = '🇷🇴';
    const FLAG_RUSSIA = '🇷🇺';

    const FLAG_SAN_MARINO = '🇸🇲';
    const FLAG_SAUDI_ARABIA = '🇸🇦';
    const FLAG_SENEGAL = '🇸🇳';
    const FLAG_SERBIA = '🇷🇸';
    const FLAG_SINGAPORE = '🇸🇬';
    const FLAG_SLOVAKIA = '🇸🇰';
    const FLAG_SLOVENIA = '🇸🇮';
    const FLAG_SOUTH_AFRICA = '🇿🇦';
    const FLAG_SOUTH_KOREA = '🇰🇷';
    const FLAG_SPAIN = '🇪🇸';
    const FLAG_SRI_LANKA = '🇱🇰';
    const FLAG_SWEDEN = '🇸🇪';
    const FLAG_SWITZERLAND = '🇨🇭';
    const FLAG_SYRIA = '🇸🇾';

    const FLAG_TAIWAN = '🇹🇼';
    const FLAG_TANZANIA = '🇹🇿';
    const FLAG_THAILAND = '🇹🇭';
    const FLAG_TUNISIA = '🇹🇳';
    const FLAG_TURKEY = '🇹🇷';

    const FLAG_UKRAINE = '🇺🇦';
    const FLAG_UNITED_ARAB_EMIRATES = '🇦🇪';
    const FLAG_UNITED_KINGDOM = '🇬🇧';
    const FLAG_UNITED_STATES = '🇺🇸';
    const FLAG_URUGUAY = '🇺🇾';
    const FLAG_UZBEKISTAN = '🇺🇿';

    const FLAG_VATICAN_CITY = '🇻🇦';
    const FLAG_VENEZUELA = '🇻🇪';
    const FLAG_VIETNAM = '🇻🇳';

    const FLAG_ZAMBIA = '🇿🇲';
    const FLAG_ZIMBABWE = '🇿🇼';

    // Aliases

    const FLAG_CZECHIA = self::FLAG_CZECH_REPUBLIC;

    const FLAGS = [
        self::ALBANIA => self::FLAG_ALBANIA,
        self::ALGERIA => self::FLAG_ALGERIA,
        self::ANDORRA => self::FLAG_ANDORRA,
        self::ARGENTINA => self::FLAG_ARGENTINA,
        self::ARMENIA => self::FLAG_ARMENIA,
        self::AUSTRALIA => self::FLAG_AUSTRALIA,
        self::AUSTRIA => self::FLAG_AUSTRIA,
        self::AZERBAIJAN => self::FLAG_AZERBAIJAN,

        self::BAHRAIN => self::FLAG_BAHRAIN,
        self::BANGLADESH => self::FLAG_BANGLADESH,
        self::BELARUS => self::FLAG_BELARUS,
        self::BELGIUM => self::FLAG_BELGIUM,
        self::BOLIVIA => self::FLAG_BOLIVIA,
        self::BOSNIA_AND_HERZEGOVINA => self::FLAG_BOSNIA_AND_HERZEGOVINA,
        self::BRAZIL => self::FLAG_BRAZIL,
        self::BRUNEI => self::FLAG_BRUNEI,
        self::BULGARIA => self::FLAG_BULGARIA,

        self::CAPE_VERDE => self::FLAG_CAPE_VERDE,
        self::CAMBODIA => self::FLAG_CAMBODIA,
        self::CANADA => self::FLAG_CANADA,
        self::CHILE => self::FLAG_CHILE,
        self::CHINA => self::FLAG_CHINA,
        self::COLOMBIA => self::FLAG_COLOMBIA,
        self::COSTA_RICA => self::FLAG_COSTA_RICA,
        self::CROATIA => self::FLAG_CROATIA,
        self::CUBA => self::FLAG_CUBA,
        self::CYPRUS => self::FLAG_CYPRUS,
        self::CZECH_REPUBLIC => self::FLAG_CZECH_REPUBLIC,

        self::DENMARK => self::FLAG_DENMARK,
        self::DOMINICAN_REPUBLIC => self::FLAG_DOMINICAN_REPUBLIC,

        self::ECUADOR => self::FLAG_ECUADOR,
        self::EGYPT => self::FLAG_EGYPT,
        self::EL_SALVADOR => self::FLAG_EL_SALVADOR,
        self::ESTONIA => self::FLAG_ESTONIA,
        self::ETHIOPIA => self::FLAG_ETHIOPIA,

        self::FINLAND => self::FLAG_FINLAND,
        self::FRANCE => self::FLAG_FRANCE,

        self::GEORGIA => self::FLAG_GEORGIA,
        self::GERMANY => self::FLAG_GERMANY,
        self::GIBRALTAR => self::FLAG_GIBRALTAR,
        self::GHANA => self::FLAG_GHANA,
        self::GREECE => self::FLAG_GREECE,
        self::GUATEMALA => self::FLAG_GUATEMALA,

        self::HONDURAS => self::FLAG_HONDURAS,
        self::HONG_KONG => self::FLAG_HONG_KONG,
        self::HUNGARY => self::FLAG_HUNGARY,

        self::ICELAND => self::FLAG_ICELAND,
        self::INDIA => self::FLAG_INDIA,
        self::INDONESIA => self::FLAG_INDONESIA,
        self::IRAN => self::FLAG_IRAN,
        self::IRELAND => self::FLAG_IRELAND,
        self::ISRAEL => self::FLAG_ISRAEL,
        self::ITALY => self::FLAG_ITALY,
        self::IVORY_COAST => self::FLAG_IVORY_COAST,

        self::JAPAN => self::FLAG_JAPAN,
        self::JORDAN => self::FLAG_JORDAN,

        self::KAZAKHSTAN => self::FLAG_KAZAKHSTAN,
        self::KENYA => self::FLAG_KENYA,
        self::KOSOVO => self::FLAG_KOSOVO,
        self::KUWAIT => self::FLAG_KUWAIT,
        self::KYRGYZSTAN => self::FLAG_KYRGYZSTAN,

        self::LATVIA => self::FLAG_LATVIA,
        self::LEBANON => self::FLAG_LEBANON,
        self::LIBYA => self::FLAG_LIBYA,
        self::LIECHTENSTEIN => self::FLAG_LIECHTENSTEIN,
        self::LITHUANIA => self::FLAG_LITHUANIA,
        self::LUXEMBOURG => self::FLAG_LUXEMBOURG,

        self::MACAU => self::FLAG_MACAU,
        self::MALAYSIA => self::FLAG_MALAYSIA,
        self::MALTA => self::FLAG_MALTA,
        self::MAURITIUS => self::FLAG_MAURITIUS,
        self::MEXICO => self::FLAG_MEXICO,
        self::MOLDOVA => self::FLAG_MOLDOVA,
        self::MONACO => self::FLAG_MONACO,
        self::MONGOLIA => self::FLAG_MONGOLIA,
        self::MONTENEGRO => self::FLAG_MONTENEGRO,
        self::MOROCCO => self::FLAG_MOROCCO,

        self::NETHERLANDS => self::FLAG_NETHERLANDS,
        self::NEW_ZEALAND => self::FLAG_NEW_ZEALAND,
        self::NICARAGUA => self::FLAG_NICARAGUA,
        self::NIGERIA => self::FLAG_NIGERIA,
        self::NORTH_KOREA => self::FLAG_NORTH_KOREA,
        self::NORTH_MACEDONIA => self::FLAG_NORTH_MACEDONIA,
        self::NORWAY => self::FLAG_NORWAY,

        self::PAKISTAN => self::FLAG_PAKISTAN,
        self::PANAMA => self::FLAG_PANAMA,
        self::PARAGUAY => self::FLAG_PARAGUAY,
        self::PERU => self::FLAG_PERU,
        self::PHILIPPINES => self::FLAG_PHILIPPINES,
        self::POLAND => self::FLAG_POLAND,
        self::PORTUGAL => self::FLAG_PORTUGAL,

        self::ROMANIA => self::FLAG_ROMANIA,
        self::RUSSIA => self::FLAG_RUSSIA,

        self::SAN_MARINO => self::FLAG_SAN_MARINO,
        self::SAUDI_ARABIA => self::FLAG_SAUDI_ARABIA,
        self::SENEGAL => self::FLAG_SENEGAL,
        self::SERBIA => self::FLAG_SERBIA,
        self::SINGAPORE => self::FLAG_SINGAPORE,
        self::SLOVAKIA => self::FLAG_SLOVAKIA,
        self::SLOVENIA => self::FLAG_SLOVENIA,
        self::SOUTH_AFRICA => self::FLAG_SOUTH_AFRICA,
        self::SOUTH_KOREA => self::FLAG_SOUTH_KOREA,
        self::SPAIN => self::FLAG_SPAIN,
        self::SRI_LANKA => self::FLAG_SRI_LANKA,
        self::SWEDEN => self::FLAG_SWEDEN,
        self::SWITZERLAND => self::FLAG_SWITZERLAND,
        self::SYRIA => self::FLAG_SYRIA,

        self::TAIWAN => self::FLAG_TAIWAN,
        self::TANZANIA => self::FLAG_TANZANIA,
        self::THAILAND => self::FLAG_THAILAND,
        self::TUNISIA => self::FLAG_TUNISIA,
        self::TURKEY => self::FLAG_TURKEY,

        self::UKRAINE => self::FLAG_UKRAINE,
        self::UNITED_ARAB_EMIRATES => self::FLAG_UNITED_ARAB_EMIRATES,
        self::UNITED_KINGDOM => self::FLAG_UNITED_KINGDOM,
        self::UNITED_STATES => self::FLAG_UNITED_STATES,
        self::URUGUAY => self::FLAG_URUGUAY,
        self::UZBEKISTAN => self::FLAG_UZBEKISTAN,

        self::VATICAN_CITY => self::FLAG_VATICAN_CITY,
        self::VENEZUELA => self::FLAG_VENEZUELA,
        self::VIETNAM => self::FLAG_VIETNAM,

        self::ZAMBIA => self::FLAG_ZAMBIA,
        self::ZIMBABWE => self::FLAG_ZIMBABWE
    ];

    /**
     * @fn for($country_code)
     * @short Returns the argument if the value is one of the <tt>Country</tt> constants
     * @param country_code The ISO 3166 country code to check
     * @return The argument if the value is one of the <tt>Country</tt> constants
     */
    public static function for(string $country_code): ?string
    {
        $cc = mb_strtoupper($country_code);
        if (in_array($cc, self::ALL_COUNTRIES)) {
            return $cc;
        }
        return null;
    }

    /**
     * @fn in_eu($country_code)
     * @short Returns true if the argument is a EU country included in the <tt>EU_COUNTRIES</tt> list
     * @param country_code The ISO 3166 country code to check
     * @return true if the argument is a EU country included in the <tt>EU_COUNTRIES</tt> list
     */
    public static function in_eu(string $country_code): bool
    {
        $cc = mb_strtoupper($country_code);
        return in_array($cc, self::EU_COUNTRIES);
    }

    /**
     * @fn flag($country)
     * @short Returns the flag of the requested country if available
     * @param country The country as one of the <tt>Country</tt> constants
     * @return The flag of the requested country if available
     */
    public static function flag(?string $country): ?string
    {
        if ($country) {
            $cc = mb_strtoupper($country);
            if (array_key_exists($cc, self::FLAGS)) {
                return self::FLAGS[$cc];
            }
        }
        return null;
    }

    /**
     * @fn name($country)
     * @short Returns the name of the requested country
     * @details This function assumes there is a string key <tt>'country-name-xy'</tt> where
     * <tt>'xy'</tt> is the lowercase two letter ISO 3166 code for the country.
     * @param country The country as one of the <tt>Country</tt> constants
     * @return The name of the requested country
     */
    public static function name(string $country): string
    {
        return l(sprintf('country-name-%s', strtolower($country)));
    }

    public static function name_with_flag(string $country): ?string
    {
        $name = self::name($country);
        $flag = self::flag($country);
        return $flag ? sprintf('%s %s', $flag, $name) : $name;
    }
}
