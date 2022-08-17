<?php
class SPC_Countries  {

	private $countries;
	private $states;

	function __construct() {

		$this->countries = apply_filters( 'sunshine_countries', array(
				'AF' => __( 'Afghanistan', 'sunshine-photo-cart' ),
				'AX' => __( '&#197;land Islands', 'sunshine-photo-cart' ),
				'AL' => __( 'Albania', 'sunshine-photo-cart' ),
				'DZ' => __( 'Algeria', 'sunshine-photo-cart' ),
				'AS' => __( 'American Samoa', 'sunshine-photo-cart' ),
				'AD' => __( 'Andorra', 'sunshine-photo-cart' ),
				'AO' => __( 'Angola', 'sunshine-photo-cart' ),
				'AI' => __( 'Anguilla', 'sunshine-photo-cart' ),
				'AQ' => __( 'Antarctica', 'sunshine-photo-cart' ),
				'AG' => __( 'Antigua and Barbuda', 'sunshine-photo-cart' ),
				'AR' => __( 'Argentina', 'sunshine-photo-cart' ),
				'AM' => __( 'Armenia', 'sunshine-photo-cart' ),
				'AW' => __( 'Aruba', 'sunshine-photo-cart' ),
				'AU' => __( 'Australia', 'sunshine-photo-cart' ),
				'AT' => __( 'Austria', 'sunshine-photo-cart' ),
				'AZ' => __( 'Azerbaijan', 'sunshine-photo-cart' ),
				'BS' => __( 'Bahamas', 'sunshine-photo-cart' ),
				'BH' => __( 'Bahrain', 'sunshine-photo-cart' ),
				'BD' => __( 'Bangladesh', 'sunshine-photo-cart' ),
				'BB' => __( 'Barbados', 'sunshine-photo-cart' ),
				'BY' => __( 'Belarus', 'sunshine-photo-cart' ),
				'BE' => __( 'Belgium', 'sunshine-photo-cart' ),
				'BZ' => __( 'Belize', 'sunshine-photo-cart' ),
				'BJ' => __( 'Benin', 'sunshine-photo-cart' ),
				'BM' => __( 'Bermuda', 'sunshine-photo-cart' ),
				'BT' => __( 'Bhutan', 'sunshine-photo-cart' ),
				'BO' => __( 'Bolivia', 'sunshine-photo-cart' ),
				'BA' => __( 'Bosnia and Herzegovina', 'sunshine-photo-cart' ),
				'BW' => __( 'Botswana', 'sunshine-photo-cart' ),
				'BR' => __( 'Brazil', 'sunshine-photo-cart' ),
				'IO' => __( 'British Indian Ocean Territory', 'sunshine-photo-cart' ),
				'VG' => __( 'British Virgin Islands', 'sunshine-photo-cart' ),
				'BN' => __( 'Brunei', 'sunshine-photo-cart' ),
				'BG' => __( 'Bulgaria', 'sunshine-photo-cart' ),
				'BF' => __( 'Burkina Faso', 'sunshine-photo-cart' ),
				'BI' => __( 'Burundi', 'sunshine-photo-cart' ),
				'KH' => __( 'Cambodia', 'sunshine-photo-cart' ),
				'CM' => __( 'Cameroon', 'sunshine-photo-cart' ),
				'CA' => __( 'Canada', 'sunshine-photo-cart' ),
				'CV' => __( 'Cape Verde', 'sunshine-photo-cart' ),
				'KY' => __( 'Cayman Islands', 'sunshine-photo-cart' ),
				'CF' => __( 'Central African Republic', 'sunshine-photo-cart' ),
				'TD' => __( 'Chad', 'sunshine-photo-cart' ),
				'CL' => __( 'Chile', 'sunshine-photo-cart' ),
				'CN' => __( 'China', 'sunshine-photo-cart' ),
				'CX' => __( 'Christmas Island', 'sunshine-photo-cart' ),
				'CC' => __( 'Cocos (Keeling) Islands', 'sunshine-photo-cart' ),
				'CO' => __( 'Colombia', 'sunshine-photo-cart' ),
				'KM' => __( 'Comoros', 'sunshine-photo-cart' ),
				'CG' => __( 'Congo (Brazzaville)', 'sunshine-photo-cart' ),
				'CD' => __( 'Congo (Kinshasa)', 'sunshine-photo-cart' ),
				'CK' => __( 'Cook Islands', 'sunshine-photo-cart' ),
				'CR' => __( 'Costa Rica', 'sunshine-photo-cart' ),
				'HR' => __( 'Croatia', 'sunshine-photo-cart' ),
				'CU' => __( 'Cuba', 'sunshine-photo-cart' ),
				'CY' => __( 'Cyprus', 'sunshine-photo-cart' ),
				'CZ' => __( 'Czech Republic', 'sunshine-photo-cart' ),
				'DK' => __( 'Denmark', 'sunshine-photo-cart' ),
				'DJ' => __( 'Djibouti', 'sunshine-photo-cart' ),
				'DM' => __( 'Dominica', 'sunshine-photo-cart' ),
				'DO' => __( 'Dominican Republic', 'sunshine-photo-cart' ),
				'EC' => __( 'Ecuador', 'sunshine-photo-cart' ),
				'EG' => __( 'Egypt', 'sunshine-photo-cart' ),
				'SV' => __( 'El Salvador', 'sunshine-photo-cart' ),
				'GQ' => __( 'Equatorial Guinea', 'sunshine-photo-cart' ),
				'ER' => __( 'Eritrea', 'sunshine-photo-cart' ),
				'EE' => __( 'Estonia', 'sunshine-photo-cart' ),
				'ET' => __( 'Ethiopia', 'sunshine-photo-cart' ),
				'FK' => __( 'Falkland Islands', 'sunshine-photo-cart' ),
				'FO' => __( 'Faroe Islands', 'sunshine-photo-cart' ),
				'FJ' => __( 'Fiji', 'sunshine-photo-cart' ),
				'FI' => __( 'Finland', 'sunshine-photo-cart' ),
				'FR' => __( 'France', 'sunshine-photo-cart' ),
				'GF' => __( 'French Guiana', 'sunshine-photo-cart' ),
				'PF' => __( 'French Polynesia', 'sunshine-photo-cart' ),
				'TF' => __( 'French Southern Territories', 'sunshine-photo-cart' ),
				'GA' => __( 'Gabon', 'sunshine-photo-cart' ),
				'GM' => __( 'Gambia', 'sunshine-photo-cart' ),
				'GE' => __( 'Georgia', 'sunshine-photo-cart' ),
				'DE' => __( 'Germany', 'sunshine-photo-cart' ),
				'GH' => __( 'Ghana', 'sunshine-photo-cart' ),
				'GI' => __( 'Gibraltar', 'sunshine-photo-cart' ),
				'GR' => __( 'Greece', 'sunshine-photo-cart' ),
				'GL' => __( 'Greenland', 'sunshine-photo-cart' ),
				'GD' => __( 'Grenada', 'sunshine-photo-cart' ),
				'GP' => __( 'Guadeloupe', 'sunshine-photo-cart' ),
				'GU' => __( 'Guam', 'sunshine-photo-cart' ),
				'GT' => __( 'Guatemala', 'sunshine-photo-cart' ),
				'GG' => __( 'Guernsey', 'sunshine-photo-cart' ),
				'GN' => __( 'Guinea', 'sunshine-photo-cart' ),
				'GW' => __( 'Guinea-Bissau', 'sunshine-photo-cart' ),
				'GY' => __( 'Guyana', 'sunshine-photo-cart' ),
				'HT' => __( 'Haiti', 'sunshine-photo-cart' ),
				'HN' => __( 'Honduras', 'sunshine-photo-cart' ),
				'HK' => __( 'Hong Kong', 'sunshine-photo-cart' ),
				'HU' => __( 'Hungary', 'sunshine-photo-cart' ),
				'IS' => __( 'Iceland', 'sunshine-photo-cart' ),
				'IN' => __( 'India', 'sunshine-photo-cart' ),
				'ID' => __( 'Indonesia', 'sunshine-photo-cart' ),
				'IR' => __( 'Iran', 'sunshine-photo-cart' ),
				'IQ' => __( 'Iraq', 'sunshine-photo-cart' ),
				'IE' => __( 'Republic of Ireland', 'sunshine-photo-cart' ),
				'IM' => __( 'Isle of Man', 'sunshine-photo-cart' ),
				'IL' => __( 'Israel', 'sunshine-photo-cart' ),
				'IT' => __( 'Italy', 'sunshine-photo-cart' ),
				'CI' => __( 'Ivory Coast', 'sunshine-photo-cart' ),
				'JM' => __( 'Jamaica', 'sunshine-photo-cart' ),
				'JP' => __( 'Japan', 'sunshine-photo-cart' ),
				'JE' => __( 'Jersey', 'sunshine-photo-cart' ),
				'JO' => __( 'Jordan', 'sunshine-photo-cart' ),
				'KZ' => __( 'Kazakhstan', 'sunshine-photo-cart' ),
				'KE' => __( 'Kenya', 'sunshine-photo-cart' ),
				'KI' => __( 'Kiribati', 'sunshine-photo-cart' ),
				'KW' => __( 'Kuwait', 'sunshine-photo-cart' ),
				'KG' => __( 'Kyrgyzstan', 'sunshine-photo-cart' ),
				'LA' => __( 'Laos', 'sunshine-photo-cart' ),
				'LV' => __( 'Latvia', 'sunshine-photo-cart' ),
				'LB' => __( 'Lebanon', 'sunshine-photo-cart' ),
				'LS' => __( 'Lesotho', 'sunshine-photo-cart' ),
				'LR' => __( 'Liberia', 'sunshine-photo-cart' ),
				'LY' => __( 'Libya', 'sunshine-photo-cart' ),
				'LI' => __( 'Liechtenstein', 'sunshine-photo-cart' ),
				'LT' => __( 'Lithuania', 'sunshine-photo-cart' ),
				'LU' => __( 'Luxembourg', 'sunshine-photo-cart' ),
				'MO' => __( 'Macao S.A.R., China', 'sunshine-photo-cart' ),
				'MK' => __( 'Macedonia', 'sunshine-photo-cart' ),
				'MG' => __( 'Madagascar', 'sunshine-photo-cart' ),
				'MW' => __( 'Malawi', 'sunshine-photo-cart' ),
				'MY' => __( 'Malaysia', 'sunshine-photo-cart' ),
				'MV' => __( 'Maldives', 'sunshine-photo-cart' ),
				'ML' => __( 'Mali', 'sunshine-photo-cart' ),
				'MT' => __( 'Malta', 'sunshine-photo-cart' ),
				'MH' => __( 'Marshall Islands', 'sunshine-photo-cart' ),
				'MQ' => __( 'Martinique', 'sunshine-photo-cart' ),
				'MR' => __( 'Mauritania', 'sunshine-photo-cart' ),
				'MU' => __( 'Mauritius', 'sunshine-photo-cart' ),
				'YT' => __( 'Mayotte', 'sunshine-photo-cart' ),
				'MX' => __( 'Mexico', 'sunshine-photo-cart' ),
				'FM' => __( 'Micronesia', 'sunshine-photo-cart' ),
				'MD' => __( 'Moldova', 'sunshine-photo-cart' ),
				'MC' => __( 'Monaco', 'sunshine-photo-cart' ),
				'MN' => __( 'Mongolia', 'sunshine-photo-cart' ),
				'ME' => __( 'Montenegro', 'sunshine-photo-cart' ),
				'MS' => __( 'Montserrat', 'sunshine-photo-cart' ),
				'MA' => __( 'Morocco', 'sunshine-photo-cart' ),
				'MZ' => __( 'Mozambique', 'sunshine-photo-cart' ),
				'MM' => __( 'Myanmar', 'sunshine-photo-cart' ),
				'NA' => __( 'Namibia', 'sunshine-photo-cart' ),
				'NR' => __( 'Nauru', 'sunshine-photo-cart' ),
				'NP' => __( 'Nepal', 'sunshine-photo-cart' ),
				'NL' => __( 'Netherlands', 'sunshine-photo-cart' ),
				'AN' => __( 'Netherlands Antilles', 'sunshine-photo-cart' ),
				'NC' => __( 'New Caledonia', 'sunshine-photo-cart' ),
				'NZ' => __( 'New Zealand', 'sunshine-photo-cart' ),
				'NI' => __( 'Nicaragua', 'sunshine-photo-cart' ),
				'NE' => __( 'Niger', 'sunshine-photo-cart' ),
				'NG' => __( 'Nigeria', 'sunshine-photo-cart' ),
				'NU' => __( 'Niue', 'sunshine-photo-cart' ),
				'NF' => __( 'Norfolk Island', 'sunshine-photo-cart' ),
				'KP' => __( 'North Korea', 'sunshine-photo-cart' ),
				'MP' => __( 'Northern Mariana Islands', 'sunshine-photo-cart' ),
				'NO' => __( 'Norway', 'sunshine-photo-cart' ),
				'OM' => __( 'Oman', 'sunshine-photo-cart' ),
				'PK' => __( 'Pakistan', 'sunshine-photo-cart' ),
				'PW' => __( 'Palau', 'sunshine-photo-cart' ),
				'PS' => __( 'Palestinian Territory', 'sunshine-photo-cart' ),
				'PA' => __( 'Panama', 'sunshine-photo-cart' ),
				'PG' => __( 'Papua New Guinea', 'sunshine-photo-cart' ),
				'PY' => __( 'Paraguay', 'sunshine-photo-cart' ),
				'PE' => __( 'Peru', 'sunshine-photo-cart' ),
				'PH' => __( 'Philippines', 'sunshine-photo-cart' ),
				'PN' => __( 'Pitcairn', 'sunshine-photo-cart' ),
				'PL' => __( 'Poland', 'sunshine-photo-cart' ),
				'PT' => __( 'Portugal', 'sunshine-photo-cart' ),
				'PR' => __( 'Puerto Rico', 'sunshine-photo-cart' ),
				'QA' => __( 'Qatar', 'sunshine-photo-cart' ),
				'RE' => __( 'Reunion', 'sunshine-photo-cart' ),
				'RO' => __( 'Romania', 'sunshine-photo-cart' ),
				'RU' => __( 'Russia', 'sunshine-photo-cart' ),
				'RW' => __( 'Rwanda', 'sunshine-photo-cart' ),
				'BL' => __( 'Saint Barth&eacute;lemy', 'sunshine-photo-cart' ),
				'SH' => __( 'Saint Helena', 'sunshine-photo-cart' ),
				'KN' => __( 'Saint Kitts and Nevis', 'sunshine-photo-cart' ),
				'LC' => __( 'Saint Lucia', 'sunshine-photo-cart' ),
				'MF' => __( 'Saint Martin (French part)', 'sunshine-photo-cart' ),
				'PM' => __( 'Saint Pierre and Miquelon', 'sunshine-photo-cart' ),
				'VC' => __( 'Saint Vincent and the Grenadines', 'sunshine-photo-cart' ),
				'WS' => __( 'Samoa', 'sunshine-photo-cart' ),
				'SM' => __( 'San Marino', 'sunshine-photo-cart' ),
				'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'sunshine-photo-cart' ),
				'SA' => __( 'Saudi Arabia', 'sunshine-photo-cart' ),
				'SN' => __( 'Senegal', 'sunshine-photo-cart' ),
				'RS' => __( 'Serbia', 'sunshine-photo-cart' ),
				'SC' => __( 'Seychelles', 'sunshine-photo-cart' ),
				'SL' => __( 'Sierra Leone', 'sunshine-photo-cart' ),
				'SG' => __( 'Singapore', 'sunshine-photo-cart' ),
				'SK' => __( 'Slovakia', 'sunshine-photo-cart' ),
				'SI' => __( 'Slovenia', 'sunshine-photo-cart' ),
				'SB' => __( 'Solomon Islands', 'sunshine-photo-cart' ),
				'SO' => __( 'Somalia', 'sunshine-photo-cart' ),
				'ZA' => __( 'South Africa', 'sunshine-photo-cart' ),
				'GS' => __( 'South Georgia/Sandwich Islands', 'sunshine-photo-cart' ),
				'KR' => __( 'South Korea', 'sunshine-photo-cart' ),
				'ES' => __( 'Spain', 'sunshine-photo-cart' ),
				'LK' => __( 'Sri Lanka', 'sunshine-photo-cart' ),
				'SD' => __( 'Sudan', 'sunshine-photo-cart' ),
				'SR' => __( 'Suriname', 'sunshine-photo-cart' ),
				'SJ' => __( 'Svalbard and Jan Mayen', 'sunshine-photo-cart' ),
				'SZ' => __( 'Swaziland', 'sunshine-photo-cart' ),
				'SE' => __( 'Sweden', 'sunshine-photo-cart' ),
				'CH' => __( 'Switzerland', 'sunshine-photo-cart' ),
				'SY' => __( 'Syria', 'sunshine-photo-cart' ),
				'TW' => __( 'Taiwan', 'sunshine-photo-cart' ),
				'TJ' => __( 'Tajikistan', 'sunshine-photo-cart' ),
				'TZ' => __( 'Tanzania', 'sunshine-photo-cart' ),
				'TH' => __( 'Thailand', 'sunshine-photo-cart' ),
				'TL' => __( 'Timor-Leste', 'sunshine-photo-cart' ),
				'TG' => __( 'Togo', 'sunshine-photo-cart' ),
				'TK' => __( 'Tokelau', 'sunshine-photo-cart' ),
				'TO' => __( 'Tonga', 'sunshine-photo-cart' ),
				'TT' => __( 'Trinidad and Tobago', 'sunshine-photo-cart' ),
				'TN' => __( 'Tunisia', 'sunshine-photo-cart' ),
				'TR' => __( 'Turkey', 'sunshine-photo-cart' ),
				'TM' => __( 'Turkmenistan', 'sunshine-photo-cart' ),
				'TC' => __( 'Turks and Caicos Islands', 'sunshine-photo-cart' ),
				'TV' => __( 'Tuvalu', 'sunshine-photo-cart' ),
				'VI' => __( 'U.S. Virgin Islands', 'sunshine-photo-cart' ),
				'USAF' => __( 'US Armed Forces', 'sunshine-photo-cart' ),
				'UM' => __( 'US Minor Outlying Islands', 'sunshine-photo-cart' ),
				'UG' => __( 'Uganda', 'sunshine-photo-cart' ),
				'UA' => __( 'Ukraine', 'sunshine-photo-cart' ),
				'AE' => __( 'United Arab Emirates', 'sunshine-photo-cart' ),
				'GB' => __( 'United Kingdom', 'sunshine-photo-cart' ),
				'US' => __( 'United States', 'sunshine-photo-cart' ),
				'UY' => __( 'Uruguay', 'sunshine-photo-cart' ),
				'UZ' => __( 'Uzbekistan', 'sunshine-photo-cart' ),
				'VU' => __( 'Vanuatu', 'sunshine-photo-cart' ),
				'VA' => __( 'Vatican', 'sunshine-photo-cart' ),
				'VE' => __( 'Venezuela', 'sunshine-photo-cart' ),
				'VN' => __( 'Vietnam', 'sunshine-photo-cart' ),
				'WF' => __( 'Wallis and Futuna', 'sunshine-photo-cart' ),
				'EH' => __( 'Western Sahara', 'sunshine-photo-cart' ),
				'YE' => __( 'Yemen', 'sunshine-photo-cart' ),
				'ZM' => __( 'Zambia', 'sunshine-photo-cart' ),
				'ZW' => __( 'Zimbabwe', 'sunshine-photo-cart' )
			) );

		$this->states = apply_filters( 'sunshine_states', array(
				'AU' => array(
					'ACT' => __( 'Australian Capital Territory', 'sunshine-photo-cart' ) ,
					'NSW' => __( 'New South Wales', 'sunshine-photo-cart' ) ,
					'NT' => __( 'Northern Territory', 'sunshine-photo-cart' ) ,
					'QLD' => __( 'Queensland', 'sunshine-photo-cart' ) ,
					'SA' => __( 'South Australia', 'sunshine-photo-cart' ) ,
					'TAS' => __( 'Tasmania', 'sunshine-photo-cart' ) ,
					'VIC' => __( 'Victoria', 'sunshine-photo-cart' ) ,
					'WA' => __( 'Western Australia', 'sunshine-photo-cart' )
				),
				'BR' => array(
					'AM' => __( 'Amazonas', 'sunshine-photo-cart' ),
					'AC' => __( 'Acre', 'sunshine-photo-cart' ),
					'AL' => __( 'Alagoas', 'sunshine-photo-cart' ),
					'AP' => __( 'Amap&aacute;', 'sunshine-photo-cart' ),
					'CE' => __( 'Cear&aacute;', 'sunshine-photo-cart' ),
					'DF' => __( 'Distrito Federal', 'sunshine-photo-cart' ),
					'ES' => __( 'Esp&iacute;rito Santo', 'sunshine-photo-cart' ),
					'MA' => __( 'Maranh&atilde;o', 'sunshine-photo-cart' ),
					'PR' => __( 'Paran&aacute;', 'sunshine-photo-cart' ),
					'PE' => __( 'Pernambuco', 'sunshine-photo-cart' ),
					'PI' => __( 'Piau&iacute;', 'sunshine-photo-cart' ),
					'RN' => __( 'Rio Grande do Norte', 'sunshine-photo-cart' ),
					'RS' => __( 'Rio Grande do Sul', 'sunshine-photo-cart' ),
					'RO' => __( 'Rond&ocirc;nia', 'sunshine-photo-cart' ),
					'RR' => __( 'Roraima', 'sunshine-photo-cart' ),
					'SC' => __( 'Santa Catarina', 'sunshine-photo-cart' ),
					'SE' => __( 'Sergipe', 'sunshine-photo-cart' ),
					'TO' => __( 'Tocantins', 'sunshine-photo-cart' ),
					'PA' => __( 'Par&aacute;', 'sunshine-photo-cart' ),
					'BH' => __( 'Bahia', 'sunshine-photo-cart' ),
					'GO' => __( 'Goi&aacute;s', 'sunshine-photo-cart' ),
					'MT' => __( 'Mato Grosso', 'sunshine-photo-cart' ),
					'MS' => __( 'Mato Grosso do Sul', 'sunshine-photo-cart' ),
					'RJ' => __( 'Rio de Janeiro', 'sunshine-photo-cart' ),
					'SP' => __( 'S&atilde;o Paulo', 'sunshine-photo-cart' ),
					'RS' => __( 'Rio Grande do Sul', 'sunshine-photo-cart' ),
					'MG' => __( 'Minas Gerais', 'sunshine-photo-cart' ),
					'PB' => __( 'Para&iacute;ba', 'sunshine-photo-cart' ),
				),
				'CA' => array(
					'AB' => __( 'Alberta', 'sunshine-photo-cart' ) ,
					'BC' => __( 'British Columbia', 'sunshine-photo-cart' ) ,
					'MB' => __( 'Manitoba', 'sunshine-photo-cart' ) ,
					'NB' => __( 'New Brunswick', 'sunshine-photo-cart' ) ,
					'NF' => __( 'Newfoundland', 'sunshine-photo-cart' ) ,
					'NT' => __( 'Northwest Territories', 'sunshine-photo-cart' ) ,
					'NS' => __( 'Nova Scotia', 'sunshine-photo-cart' ) ,
					'NU' => __( 'Nunavut', 'sunshine-photo-cart' ) ,
					'ON' => __( 'Ontario', 'sunshine-photo-cart' ) ,
					'PE' => __( 'Prince Edward Island', 'sunshine-photo-cart' ) ,
					'QC' => __( 'Quebec', 'sunshine-photo-cart' ) ,
					'SK' => __( 'Saskatchewan', 'sunshine-photo-cart' ) ,
					'YT' => __( 'Yukon Territory', 'sunshine-photo-cart' )
				),
				'HK' => array(
					'HONG KONG' => __( 'Hong Kong Island', 'sunshine-photo-cart' ),
					'KOWLOON' => __( 'Kowloon', 'sunshine-photo-cart' ),
					'NEW TERRITORIES' => __( 'New Territories', 'sunshine-photo-cart' )
				),
				'NL' => array(
					'DR' => __( 'Drenthe', 'sunshine-photo-cart' ) ,
					'FL' => __( 'Flevoland', 'sunshine-photo-cart' ) ,
					'FR' => __( 'Friesland', 'sunshine-photo-cart' ) ,
					'GLD' => __( 'Gelderland', 'sunshine-photo-cart' ) ,
					'GRN' => __( 'Groningen', 'sunshine-photo-cart' ) ,
					'LB' => __( 'Limburg', 'sunshine-photo-cart' ) ,
					'NB' => __( 'Noord-Brabant', 'sunshine-photo-cart' ) ,
					'NH' => __( 'Noord-Holland', 'sunshine-photo-cart' ) ,
					'OV' => __( 'Overijssel', 'sunshine-photo-cart' ) ,
					'UT' => __( 'Utrecht', 'sunshine-photo-cart' ) ,
					'ZLD' => __( 'Zeeland', 'sunshine-photo-cart' ) ,
					'ZH' => __( 'Zuid-Holland', 'sunshine-photo-cart' ) ,
				),
				'NZ' => array(
					'NL' => __( 'Northland', 'sunshine-photo-cart' ) ,
					'AK' => __( 'Auckland', 'sunshine-photo-cart' ) ,
					'WA' => __( 'Waikato', 'sunshine-photo-cart' ) ,
					'BP' => __( 'Bay of Plenty', 'sunshine-photo-cart' ) ,
					'TK' => __( 'Taranaki', 'sunshine-photo-cart' ) ,
					'HB' => __( 'Hawke&rsquo;s Bay', 'sunshine-photo-cart' ) ,
					'MW' => __( 'Manawatu-Wanganui', 'sunshine-photo-cart' ) ,
					'WE' => __( 'Wellington', 'sunshine-photo-cart' ) ,
					'NS' => __( 'Nelson', 'sunshine-photo-cart' ) ,
					'MB' => __( 'Marlborough', 'sunshine-photo-cart' ) ,
					'TM' => __( 'Tasman', 'sunshine-photo-cart' ) ,
					'WC' => __( 'West Coast', 'sunshine-photo-cart' ) ,
					'CT' => __( 'Canterbury', 'sunshine-photo-cart' ) ,
					'OT' => __( 'Otago', 'sunshine-photo-cart' ) ,
					'SL' => __( 'Southland', 'sunshine-photo-cart' ) ,
				),
				'US' => array(
					'AL' => __( 'Alabama', 'sunshine-photo-cart' ) ,
					'AK' => __( 'Alaska', 'sunshine-photo-cart' ) ,
					'AZ' => __( 'Arizona', 'sunshine-photo-cart' ) ,
					'AR' => __( 'Arkansas', 'sunshine-photo-cart' ) ,
					'CA' => __( 'California', 'sunshine-photo-cart' ) ,
					'CO' => __( 'Colorado', 'sunshine-photo-cart' ) ,
					'CT' => __( 'Connecticut', 'sunshine-photo-cart' ) ,
					'DE' => __( 'Delaware', 'sunshine-photo-cart' ) ,
					'DC' => __( 'District Of Columbia', 'sunshine-photo-cart' ) ,
					'FL' => __( 'Florida', 'sunshine-photo-cart' ) ,
					'GA' => __( 'Georgia', 'sunshine-photo-cart' ) ,
					'HI' => __( 'Hawaii', 'sunshine-photo-cart' ) ,
					'ID' => __( 'Idaho', 'sunshine-photo-cart' ) ,
					'IL' => __( 'Illinois', 'sunshine-photo-cart' ) ,
					'IN' => __( 'Indiana', 'sunshine-photo-cart' ) ,
					'IA' => __( 'Iowa', 'sunshine-photo-cart' ) ,
					'KS' => __( 'Kansas', 'sunshine-photo-cart' ) ,
					'KY' => __( 'Kentucky', 'sunshine-photo-cart' ) ,
					'LA' => __( 'Louisiana', 'sunshine-photo-cart' ) ,
					'ME' => __( 'Maine', 'sunshine-photo-cart' ) ,
					'MD' => __( 'Maryland', 'sunshine-photo-cart' ) ,
					'MA' => __( 'Massachusetts', 'sunshine-photo-cart' ) ,
					'MI' => __( 'Michigan', 'sunshine-photo-cart' ) ,
					'MN' => __( 'Minnesota', 'sunshine-photo-cart' ) ,
					'MS' => __( 'Mississippi', 'sunshine-photo-cart' ) ,
					'MO' => __( 'Missouri', 'sunshine-photo-cart' ) ,
					'MT' => __( 'Montana', 'sunshine-photo-cart' ) ,
					'NE' => __( 'Nebraska', 'sunshine-photo-cart' ) ,
					'NV' => __( 'Nevada', 'sunshine-photo-cart' ) ,
					'NH' => __( 'New Hampshire', 'sunshine-photo-cart' ) ,
					'NJ' => __( 'New Jersey', 'sunshine-photo-cart' ) ,
					'NM' => __( 'New Mexico', 'sunshine-photo-cart' ) ,
					'NY' => __( 'New York', 'sunshine-photo-cart' ) ,
					'NC' => __( 'North Carolina', 'sunshine-photo-cart' ) ,
					'ND' => __( 'North Dakota', 'sunshine-photo-cart' ) ,
					'OH' => __( 'Ohio', 'sunshine-photo-cart' ) ,
					'OK' => __( 'Oklahoma', 'sunshine-photo-cart' ) ,
					'OR' => __( 'Oregon', 'sunshine-photo-cart' ) ,
					'PA' => __( 'Pennsylvania', 'sunshine-photo-cart' ) ,
					'RI' => __( 'Rhode Island', 'sunshine-photo-cart' ) ,
					'SC' => __( 'South Carolina', 'sunshine-photo-cart' ) ,
					'SD' => __( 'South Dakota', 'sunshine-photo-cart' ) ,
					'TN' => __( 'Tennessee', 'sunshine-photo-cart' ) ,
					'TX' => __( 'Texas', 'sunshine-photo-cart' ) ,
					'UT' => __( 'Utah', 'sunshine-photo-cart' ) ,
					'VT' => __( 'Vermont', 'sunshine-photo-cart' ) ,
					'VA' => __( 'Virginia', 'sunshine-photo-cart' ) ,
					'WA' => __( 'Washington', 'sunshine-photo-cart' ) ,
					'WV' => __( 'West Virginia', 'sunshine-photo-cart' ) ,
					'WI' => __( 'Wisconsin', 'sunshine-photo-cart' ) ,
					'WY' => __( 'Wyoming', 'sunshine-photo-cart' )
				),
				'USAF' => array(
					'AA' => __( 'Americas', 'sunshine-photo-cart' ) ,
					'AE' => __( 'Europe', 'sunshine-photo-cart' ) ,
					'AP' => __( 'Pacific', 'sunshine-photo-cart' )
				)
			) );
	}

	public function get_base_country() {
		return SPC()->get_option( 'country' );
	}

    public function get_countries() {
        return $this->countries;
    }

	public function get_states( $country ) {
		if ( !empty( $this->states[ $country ] ) ) {
            return $this->states[ $country ];
        }
	}

	public function get_allowed_countries() {
        $allowed_countries = SPC()->get_option( 'allowed_countries' );
		if ( empty( $allowed_countries ) || !is_array( $allowed_countries ) || in_array( 'all', $allowed_countries ) ) {
			return $this->get_countries();
		}
		$selected_allowed_countries = array();
		foreach ( $allowed_countries as $code ) {
			$selected_allowed_countries[ $code ] = $this->countries[ $code ];
		}
		return $selected_allowed_countries;
	}

	public function country_only_dropdown( $name = 'country', $selected = '', $required = false ) {
		$countries = $this->get_allowed_countries();
		if ( empty( $countries ) ) {
            $countries = $this->countries;
        }
		if ( $selected == '' ) {
            $selected = SPC()->get_option( 'country' );
        }
		if ( !empty( $countries ) ) {
			asort( $countries );
			$required = ( $required ) ? 'required' : '';
			echo '<select name="'.esc_attr( $name ).'" ' . $required . '>';
			echo '<option value="">'.__( 'Select country', 'sunshine-photo-cart' ).'</option>';
			foreach ( $countries as $key => $value ) {
                echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $selected, 0 ) . '>' . esc_html( $value ) . '</option>';
            }
			echo '</select>';
		}
	}

	public function countries_dropdown( $name = 'state', $selected = '', $required = false ) {
		if ( !empty( $this->$countries ) ) {
			asort( $this->countries );
			foreach ( $this->countries as $key => $value ) {
                $states =  $this->get_states( $key );
				if ( $states ) {
					echo '<optgroup label="' . esc_attr( $value ) . '">';
				    foreach ( $states as $state_key => $state_value ) {
                        echo '<option value="' . esc_attr( $key ) . '|' . esc_attr( $state_key ) . '" ' . selected( $key . '|' . $state_key, $selected, 0 ) . '>' . esc_html( $value ).' &mdash; '.esc_html( $state_value ) . '</option>';
				    }
        			echo '</optgroup>';
        		} else {
        			echo '<option value="'.esc_attr( $key ).'" '.selected( $state_key, $selected, 0 ).'>'. ( $escape ? esc_html( $value ) : $value ) .'</option>';
        		}
            }
		}
	}

	public function state_dropdown( $country, $name = 'state', $selected = '', $required = false ) {
		$states = array();
		if ( $country == '' ) {
            $country = SPC()->get_option( 'country' );
        }
		if ( isset( $this->states[ $country ] ) ) {
            $states = $this->staes[ $country ];
        }
		if ( $states ) {
			$required = ( $required ) ? 'required' : '';
			echo '<select name="' . esc_attr( $name ) . '" ' . $required . '>';
			echo '<option value="">' . __( 'Select state','sunshine-photo-cart' ) . '</option>';
			foreach ( $states as $code => $name ) {
				echo '<option value="' . esc_attr( $code ) . '" ' . selected( $selected, $code, 0 ) . '>' . esc_html( $name ) . '</option>';
			}
			echo '</select>';
		} else {
			echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $selected ) . '" />';
		}
	}

	public function get_address_formats() {
		$formats = apply_filters(
			'sunshine_address_formats',
			array(
				'default' => "{name}\n{address1}\n{address2}\n{city}\n{state}\n{postcode}\n{country}",
				'AT'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'AU'      => "{name}\n{address1}\n{address2}\n{city} {state} {postcode}\n{country}",
				'BE'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'CA'      => "{name}\n{address1}\n{address2}\n{city} {state_code} {postcode}\n{country}",
				'CH'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'CL'      => "{name}\n{address1}\n{address2}\n{state}\n{postcode} {city}\n{country}",
				'CN'      => "{country} {postcode}\n{state}, {city}, {address2}, {address1}\n{name}",
				'CZ'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'DE'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'DK'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'EE'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'ES'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{state}\n{country}",
				'FI'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'FR'      => "{name}\n{address1}\n{address2}\n{postcode} {city_upper}\n{country}",
				'HK'      => "{first_name} {last_name_upper}\n{address1}\n{address2}\n{city_upper}\n{state_upper}\n{country}",
				'HU'      => "{last_name} {first_name}\n{city}\n{address1}\n{address2}\n{postcode}\n{country}",
				'IN'      => "{name}\n{address1}\n{address2}\n{city} {postcode}\n{state}, {country}",
				'IS'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'IT'      => "{name}\n{address1}\n{address2}\n{postcode}\n{city}\n{state_upper}\n{country}",
				'JM'      => "{name}\n{address1}\n{address2}\n{city}\n{state}\n{postcode_upper}\n{country}",
				'JP'      => "{postcode}\n{state} {city} {address1}\n{address2}\n{last_name} {first_name}\n{country}",
				'LI'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'NL'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'NO'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'NZ'      => "{name}\n{address1}\n{address2}\n{city} {postcode}\n{country}",
				'PL'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'PR'      => "{name}\n{address1} {address2}\n{city} \n{country} {postcode}",
				'PT'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'RS'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'SE'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'SI'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'SK'      => "{name}\n{address1}\n{address2}\n{postcode} {city}\n{country}",
				'TR'      => "{name}\n{address1}\n{address2}\n{postcode} {city} {state}\n{country}",
				'TW'      => "{last_name} {first_name}\n{address1}\n{address2}\n{state}, {city} {postcode}\n{country}",
				'UG'      => "{name}\n{address1}\n{address2}\n{city}\n{state}, {country}",
				'US'      => "{name}\n{address1}\n{address2}\n{city}, {state_code} {postcode}\n{country}",
				'VN'      => "{name}\n{address1}\n{city}\n{country}",
			)
		);
		return $formats;
	}

	public function get_formatted_address( $args = array(), $separator = '<br/>' ) {
		$default_args = array(
			'first_name' => '',
			'last_name'  => '',
			'address1'  => '',
			'address2'  => '',
			'city'       => '',
			'state'      => '',
			'postcode'   => '',
			'country'    => '',
		);

		$args    = array_map( 'trim', wp_parse_args( $args, $default_args ) );
		$state   = $args['state'];
		$country = $args['country'];

		// Get all formats.
		$formats = $this->get_address_formats();

		// Get format for the address' country.
		$format = ( $country && isset( $formats[ $country ] ) ) ? $formats[ $country ] : $formats['default'];

		// Handle full country name.
		$full_country = ( isset( $this->countries[ $country ] ) ) ? $this->countries[ $country ] : $country;

		// Country is not needed if the same as base.
		if ( $country === $this->get_base_country() && ! apply_filters( 'sunshine_formatted_address_force_country_display', false ) ) {
			$format = str_replace( '{country}', '', $format );
		}

		// Handle full state name.
		$full_state = ( $country && $state && isset( $this->states[ $country ][ $state ] ) ) ? $this->states[ $country ][ $state ] : $state;

		// Substitute address parts into the string.
		$replace = array_map(
			'esc_html',
			apply_filters(
				'sunshine_formatted_address_replacements',
				array(
					'{first_name}'       => $args['first_name'],
					'{last_name}'        => $args['last_name'],
					'{name}'             => sprintf(
						/* translators: 1: first name 2: last name */
						_x( '%1$s %2$s', 'full name', 'sunshine-photo-cart' ),
						$args['first_name'],
						$args['last_name']
					),
					//'{company}'          => $args['company'],
					'{address1}'        => $args['address1'],
					'{address2}'        => $args['address2'],
					'{city}'             => $args['city'],
					'{state}'            => $full_state,
					'{postcode}'         => $args['postcode'],
					'{country}'          => $full_country,
					'{first_name_upper}' => strtoupper( $args['first_name'] ),
					'{last_name_upper}'  => strtoupper( $args['last_name'] ),
					'{name_upper}'       => strtoupper(
						sprintf(
							/* translators: 1: first name 2: last name */
							_x( '%1$s %2$s', 'full name', 'sunshine-photo-cart' ),
							$args['first_name'],
							$args['last_name']
						)
					),
					//'{company_upper}'    => strtoupper( $args['company'] ),
					'{address1_upper}'  => strtoupper( $args['address1'] ),
					'{address2_upper}'  => strtoupper( $args['address2'] ),
					'{city_upper}'       => strtoupper( $args['city'] ),
					'{state_upper}'      => strtoupper( $full_state ),
					'{state_code}'       => strtoupper( $state ),
					'{postcode_upper}'   => strtoupper( $args['postcode'] ),
					'{country_upper}'    => strtoupper( $full_country ),
				),
				$args
			)
		);

		$formatted_address = str_replace( array_keys( $replace ), $replace, $format );

		// Clean up white space.
		$formatted_address = preg_replace( '/  +/', ' ', trim( $formatted_address ) );
		$formatted_address = preg_replace( '/\n\n+/', "\n", $formatted_address );

		// Break newlines apart and remove empty lines/trim commas and white space.
		$formatted_address = array_filter( array_map( array( $this, 'trim_formatted_address_line' ), explode( "\n", $formatted_address ) ) );

		// Add html breaks.
		$formatted_address = implode( $separator, $formatted_address );

		// We're done!
		return $formatted_address;
	}

	private function trim_formatted_address_line( $line ) {
		return trim( $line, ', ' );
	}

	public function get_country_locale() {
		if ( empty( $this->locale ) ) {
			$this->locale = apply_filters(
				'sunshine_get_country_locale',
				array(
					'AE' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
						'state'    => array(
							'required' => false,
						),
					),
					'AF' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'AO' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
						'state'    => array(
							'name' => __( 'Province', 'woocommerce' ),
						),
					),
					'AT' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'AU' => array(
						'city'     => array(
							'name' => __( 'Suburb', 'woocommerce' ),
						),
						'postcode' => array(
							'name' => __( 'Postcode', 'woocommerce' ),
						),
						'state'    => array(
							'name' => __( 'State', 'woocommerce' ),
						),
					),
					'AX' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'BA' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'name'    => __( 'Canton', 'woocommerce' ),
							'required' => false,
							'hidden'   => true,
						),
					),
					'BD' => array(
						'postcode' => array(
							'required' => false,
						),
						'state'    => array(
							'name' => __( 'District', 'woocommerce' ),
						),
					),
					'BE' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'BH' => array(
						'postcode' => array(
							'required' => false,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'BI' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'BO' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'BS' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'CA' => array(
						'postcode' => array(
							'name' => __( 'Postal code', 'woocommerce' ),
						),
						'state'    => array(
							'name' => __( 'Province', 'woocommerce' ),
						),
					),
					'CH' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'name'    => __( 'Canton', 'woocommerce' ),
							'required' => false,
						),
					),
					'CL' => array(
						'city'     => array(
							'required' => true,
						),
						'postcode' => array(
							'required' => false,
						),
						'state'    => array(
							'name' => __( 'Region', 'woocommerce' ),
						),
					),
					'CN' => array(
						'state' => array(
							'name' => __( 'Province', 'woocommerce' ),
						),
					),
					'CO' => array(
						'postcode' => array(
							'required' => false,
						),
					),
					'CW' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
						'state'    => array(
							'required' => false,
						),
					),
					'CZ' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'DE' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'DK' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'EE' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'FI' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'FR' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'GH' => array(
						'postcode' => array(
							'required' => false,
						),
						'state'    => array(
							'name' => __( 'Region', 'woocommerce' ),
						),
					),
					'GP' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'GF' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'GR' => array(
						'state' => array(
							'required' => false,
						),
					),
					'GT' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
						'state'    => array(
							'name' => __( 'Department', 'woocommerce' ),
						),
					),
					'HK' => array(
						'postcode' => array(
							'required' => false,
						),
						'city'     => array(
							'name' => __( 'Town / District', 'woocommerce' ),
						),
						'state'    => array(
							'name' => __( 'Region', 'woocommerce' ),
						),
					),
					'HU' => array(
						'last_name'  => array(
							'class'    => array( 'form-row-first' ),
							'priority' => 10,
						),
						'first_name' => array(
							'class'    => array( 'form-row-last' ),
							'priority' => 20,
						),
						'postcode'   => array(
							'class'    => array( 'form-row-first', 'address-field' ),
							'priority' => 65,
						),
						'city'       => array(
							'class' => array( 'form-row-last', 'address-field' ),
						),
						'address_1'  => array(
							'priority' => 71,
						),
						'address_2'  => array(
							'priority' => 72,
						),
						'state'      => array(
							'name' => __( 'County', 'woocommerce' ),
						),
					),
					'ID' => array(
						'state' => array(
							'name' => __( 'Province', 'woocommerce' ),
						),
					),
					'IE' => array(
						'postcode' => array(
							'required' => false,
							'name'    => __( 'Eircode', 'woocommerce' ),
						),
						'state'    => array(
							'name' => __( 'County', 'woocommerce' ),
						),
					),
					'IS' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'IL' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'IM' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'IN' => array(
						'postcode' => array(
							'name' => __( 'PIN', 'woocommerce' ),
						),
						'state'    => array(
							'name' => __( 'State', 'woocommerce' ),
						),
					),
					'IT' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => true,
							'name'    => __( 'Province', 'woocommerce' ),
						),
					),
					'JM' => array(
						'city'     => array(
							'name' => __( 'Town / City / Post Office', 'woocommerce' ),
						),
						'postcode' => array(
							'required' => false,
							'name'    => __( 'Postal Code', 'woocommerce' ),
						),
						'state'    => array(
							'required' => true,
							'name'    => __( 'Parish', 'woocommerce' ),
						),
					),
					'JP' => array(
						'last_name'  => array(
							'class'    => array( 'form-row-first' ),
							'priority' => 10,
						),
						'first_name' => array(
							'class'    => array( 'form-row-last' ),
							'priority' => 20,
						),
						'postcode'   => array(
							'class'    => array( 'form-row-first', 'address-field' ),
							'priority' => 65,
						),
						'state'      => array(
							'name'    => __( 'Prefecture', 'woocommerce' ),
							'class'    => array( 'form-row-last', 'address-field' ),
							'priority' => 66,
						),
						'city'       => array(
							'priority' => 67,
						),
						'address_1'  => array(
							'priority' => 68,
						),
						'address_2'  => array(
							'priority' => 69,
						),
					),
					'KR' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'KW' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'LV' => array(
						'state' => array(
							'name'    => __( 'Municipality', 'woocommerce' ),
							'required' => false,
						),
					),
					'LB' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'MQ' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'MT' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'MZ' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
						'state'    => array(
							'name' => __( 'Province', 'woocommerce' ),
						),
					),
					'NL' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'NG' => array(
						'postcode' => array(
							'name'    => __( 'Postcode', 'woocommerce' ),
							'required' => false,
							'hidden'   => true,
						),
						'state'    => array(
							'name' => __( 'State', 'woocommerce' ),
						),
					),
					'NZ' => array(
						'postcode' => array(
							'name' => __( 'Postcode', 'woocommerce' ),
						),
						'state'    => array(
							'required' => false,
							'name'    => __( 'Region', 'woocommerce' ),
						),
					),
					'NO' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'NP' => array(
						'state'    => array(
							'name' => __( 'State / Zone', 'woocommerce' ),
						),
						'postcode' => array(
							'required' => false,
						),
					),
					'PL' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'PR' => array(
						'city'  => array(
							'name' => __( 'Municipality', 'woocommerce' ),
						),
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'PT' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'RE' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'RO' => array(
						'state' => array(
							'name'    => __( 'County', 'woocommerce' ),
							'required' => true,
						),
					),
					'RS' => array(
						'city'     => array(
							'required' => true,
						),
						'postcode' => array(
							'required' => true,
						),
						'state'    => array(
							'name'    => __( 'District', 'woocommerce' ),
							'required' => false,
						),
					),
					'SG' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
						'city'  => array(
							'required' => false,
						),
					),
					'SK' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'SI' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'SR' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'ES' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'name' => __( 'Province', 'woocommerce' ),
						),
					),
					'LI' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'name'    => __( 'Municipality', 'woocommerce' ),
							'required' => false,
						),
					),
					'LK' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'LU' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'MD' => array(
						'state' => array(
							'name' => __( 'Municipality / District', 'woocommerce' ),
						),
					),
					'SE' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'TR' => array(
						'postcode' => array(
							'priority' => 65,
						),
						'state'    => array(
							'name' => __( 'Province', 'woocommerce' ),
						),
					),
					'UG' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
						'city'     => array(
							'name'    => __( 'Town / Village', 'woocommerce' ),
							'required' => true,
						),
						'state'    => array(
							'name'    => __( 'District', 'woocommerce' ),
							'required' => true,
						),
					),
					'US' => array(
						'postcode' => array(
							'name' => __( 'ZIP Code', 'woocommerce' ),
						),
						'state'    => array(
							'name' => __( 'State', 'woocommerce' ),
						),
					),
					'GB' => array(
						'postcode' => array(
							'name' => __( 'Postcode', 'woocommerce' ),
						),
						'state'    => array(
							'name'    => __( 'County', 'woocommerce' ),
							'required' => false,
						),
					),
					'ST' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
						'state'    => array(
							'name' => __( 'District', 'woocommerce' ),
						),
					),
					'VN' => array(
						'state'     => array(
							'required' => false,
							'hidden'   => true,
						),
						'postcode'  => array(
							'priority' => 65,
							'required' => false,
							'hidden'   => false,
						),
						'address_2' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'WS' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'YT' => array(
						'state' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
					'ZA' => array(
						'state' => array(
							'name' => __( 'Province', 'woocommerce' ),
						),
					),
					'ZW' => array(
						'postcode' => array(
							'required' => false,
							'hidden'   => true,
						),
					),
				)
			);

			$this->locale = array_intersect_key( $this->locale, $this->get_allowed_countries() );

			// Default Locale Can be filtered to override fields in get_address_fields(). Countries with no specific locale will use default.
			$this->locale['default'] = apply_filters( 'sunshine_get_country_locale_default', $this->get_default_address_fields() );

			// Filter default AND shop base locales to allow overides via a single function. These will be used when changing countries on the checkout.
			if ( ! isset( $this->locale[ $this->get_base_country() ] ) ) {
				$this->locale[ $this->get_base_country() ] = $this->locale['default'];
			}

			$this->locale['default']                   = apply_filters( 'sunshine_get_country_locale_base', $this->locale['default'] );
			$this->locale[ $this->get_base_country() ] = apply_filters( 'sunshine_get_country_locale_base', $this->locale[ $this->get_base_country() ] );
		}

		return $this->locale;
	}

	public function get_default_address_fields( $country = '' ) {

		if ( empty( $country ) ) {
			$country = $this->get_base_country();
		}

		$fields = array(
			array(
				'id' => 'country',
				'type' => 'country',
				'name' => __( 'Country', 'sunshine-photo-cart' ),
				'required' => true,
				'default' => $country,
				'options' => SPC()->countries->get_allowed_countries(),
				'autocomplete' => 'address-level2'
			),
			array(
				'id' => 'first_name',
				'type' => 'text',
				'name' => __( 'First Name', 'sunshine-photo-cart' ),
				'required' => true,
				'default' => SPC()->customer->get_first_name(),
				'size' => 'half'
			),
			array(
				'id' => 'last_name',
				'type' => 'text',
				'name' => __( 'Last Name', 'sunshine-photo-cart' ),
				'required' => true,
				'default' => SPC()->customer->get_last_name(),
				'size' => 'half'
			),
			array(
				'id' => 'address1',
				'type' => 'text',
				'name' => __( 'Address', 'sunshine-photo-cart' ),
				'required' => true,
				'autocomplete' => 'address-line1'
			),
			array(
				'id' => 'address2',
				'type' => 'text',
				'name' => __( 'Address 2', 'sunshine-photo-cart' ),
				'required' => false,
				'autocomplete' => 'address-line2'
			),
			array(
				'id' => 'city',
				'type' => 'text',
				'name' => __( 'City', 'sunshine-photo-cart' ),
				'required' => true,
				'autocomplete' => 'address-level2',
				'size' => 'third'
			),
			array(
				'id' => 'state',
				'type' => 'state',
				'name' => __( 'State', 'sunshine-photo-cart' ),
				'required' => true,
				'options' => SPC()->countries->get_states( $country ),
				'autocomplete' => 'address-level1',
				'placeholder' => __( 'State', 'sunshine-photo-cart' ),
				'size' => 'third'
			),
			array(
				'id' => 'postcode',
				'type' => 'text',
				'name' => __( 'Zip / Postal Code', 'sunshine-photo-cart' ),
				'required' => true,
				'autocomplete' => 'postal-code',
				'size' => 'third'
			)
		);

		$default_address_fields = apply_filters( 'sunshine_default_address_fields', $fields );

		return $default_address_fields;
	}


	/**
	 * Apply locale and get address fields.
	 *
	 * @param  mixed  $country Country.
	 * @param  string $type    Address type, defaults to 'billing_'.
	 * @return array
	 */
	public function get_address_fields( $country = '', $type = 'billing_' ) {
		if ( ! $country ) {
			$country = $this->get_base_country();
		}

		$default_address_fields = $this->get_default_address_fields( $country );
		$locale = $this->get_country_locale();

		$address_fields = array();

		if ( isset( $locale[ $country ] ) ) {
			// Merge the locale defaults over the address defaults
			$last_line_count = 0;
			foreach ( $default_address_fields as $priority => $address_field ) {
				$address_field_id = $address_field['id'];
				if ( array_key_exists( $address_field_id, $locale[ $country ] ) ) {
					if ( !empty( $locale[ $country ][ $address_field_id ]['hidden'] ) ) {
						continue; // Complete remove any hidden fields
					}
					if ( !empty( $locale[ $country ][ $address_field_id ]['priority'] ) ) {
						$priority = $locale[ $country ][ $address_field_id ]['priority'];
					}
					$address_fields[ $priority ] = array_merge( $address_field, $locale[ $country ][ $address_field_id ] );
				} else {
					$address_fields[ $priority ] = $address_field;
				}
				if ( $address_field_id == 'city' || $address_field_id == 'state' || $address_field_id == 'postcode' ) {
					$last_line_count++;
				}
			}

		}

		// Prepend field keys.
		foreach ( $address_fields as $key => &$field ) {
			if ( $field['id'] == 'country' ) { // Set the Country select default option to the passed country
				$field['default'] = $country;
			}
			// Easy way to determine last line field sizes based on how many there are
			if ( $field['id'] == 'city' || $field['id'] == 'state' || $field['id'] == 'postcode' ) {
				if ( $last_line_count == 3 ) {
					$field['size'] = 'third';
				} elseif ( $last_line_count == 2 ) {
					$field['size'] = 'half';
				} elseif ( $last_line_count == 1 ) {
					$field['size'] = 'full';
				}
			}
			$field['id'] = $type . $field['id'];
		}

		$address_fields = apply_filters( 'sunshine_' . $type . 'fields', $address_fields, $country );


		return $address_fields;
	}


}
