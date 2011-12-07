DROP TABLE IF EXISTS `countries`;

CREATE TABLE `countries` (
  `id` int(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) DEFAULT NULL,
  `iso2` varchar(5) NOT NULL,
  `iso3` varchar(5) NOT NULL,
  `tld` varchar(5) NOT NULL,
  `idd` varchar(10) NOT NULL,
  `region` varchar(255) NOT NULL,
  `capital` varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `countries`
--

INSERT INTO `countries` (`id`, `name`, `iso2`, `iso3`, `tld`, `idd`, `region`, `capital`) VALUES
(1, 'Andorra', 'AD', 'AND', 'AN', '+376', 'Europe ', 'Andorra la Vella '),
(2, 'United Arab Emirates', 'AE', 'ARE', 'AE', '+971', 'Middle East ', 'Abu Dhabi '),
(3, 'Afghanistan', 'AF', 'AFG', 'AF', '+93', 'Asia ', 'Kabul '),
(4, 'Antigua And Barbuda', 'AG', 'ATG', 'AC', '+1 268', 'Central America and the Caribbean ', 'Saint John''s '),
(5, 'Albania', 'AL', 'ALB', 'AL', '+355', 'Europe ', 'Tirana '),
(6, 'Armenia', 'AM', 'ARM', 'AM', '+374', 'Commonwealth of Independent States ', 'Yerevan '),
(7, 'Angola', 'AO', 'AGO', 'AO', '+244', 'Africa ', 'Luanda '),
(8, 'Antarctica', 'AQ', 'ATA', 'AY', '+672', 'Antarctic Region ', '--'),
(9, 'Argentina', 'AR', 'ARG', 'AR', '+54', 'South America ', 'Buenos Aires '),
(10, 'Austria', 'AT', 'AUT', 'AU', '+43', 'Europe ', 'Vienna '),
(11, 'Australia', 'AU', 'AUS', 'AS', '+61', 'Oceania ', 'Canberra '),
(12, 'Aruba', 'AW', 'ABW', 'AA', '+297', 'Central America and the Caribbean ', 'Oranjestad '),
(13, 'Azerbaijan', 'AZ', 'AZE', 'AJ', '+994', 'Commonwealth of Independent States ', 'Baku (Baki) '),
(14, 'Bosnia And Herzegovina', 'BA', 'BIH', 'BK', '+387', 'Bosnia and Herzegovina, Europe ', 'Sarajevo '),
(15, 'Barbados', 'BB', 'BRB', 'BB', '+1 246', 'Central America and the Caribbean ', 'Bridgetown '),
(16, 'Bangladesh', 'BD', 'BGD', 'BG', '+880', 'Asia ', 'Dhaka '),
(17, 'Belgium', 'BE', 'BEL', 'BE', '+32', 'Europe ', 'Brussels '),
(18, 'Burkina Faso', 'BF', 'BFA', 'UV', '+226', 'Africa ', 'Ouagadougou '),
(19, 'Bulgaria', 'BG', 'BGR', 'BU', '+359', 'Europe ', 'Sofia '),
(20, 'Bahrain', 'BH', 'BHR', 'BA', '+973', 'Middle East ', 'Manama '),
(21, 'Burundi', 'BI', 'BDI', 'BY', '+257', 'Africa ', 'Bujumbura '),
(22, 'Benin', 'BJ', 'BEN', 'BN', '+229', 'Africa ', 'Porto-Novo  '),
(23, 'Bermuda', 'BM', 'BMU', 'BD', '+1 441', 'North America ', 'Hamilton '),
(24, 'Brunei Darussalam', 'BN', 'BRN', 'BX', '+673', 'Southeast Asia ', 'Bandar Seri Begawan'),
(25, 'Bolivia', 'BO', 'BOL', 'BL', '+591', 'South America ', 'La Paz /Sucre '),
(26, 'Brazil', 'BR', 'BRA', 'BR', '+55', 'South America ', 'Brasilia '),
(27, 'Bahamas', 'BS', 'BHS', 'BF', '+1 242', 'Central America and the Caribbean ', 'Nassau '),
(28, 'Bhutan', 'BT', 'BTN', 'BT', '+975', 'Asia ', 'Thimphu '),
(29, 'Botswana', 'BW', 'BWA', 'BC', '+267', 'Africa ', 'Gaborone '),
(30, 'Belarus', 'BY', 'BLR', 'BO', '+375', 'Commonwealth of Independent States ', 'Minsk '),
(31, 'Belize', 'BZ', 'BLZ', 'BH', '+501', 'Central America and the Caribbean ', 'Belmopan '),
(32, 'Canada', 'CA', 'CAN', 'CA', '+1', 'North America ', 'Ottawa '),
(33, 'Congo, Democratic Republic Of The', 'CD', 'COD', 'CG', '+243', 'Africa ', 'Kinshasa '),
(34, 'Central African Republic', 'CF', 'CAF', 'CT', '+236', 'Africa ', 'Bangui '),
(35, 'Congo', 'CG', 'COG', 'CF', '+242', 'Africa ', 'Brazzaville'),
(36, 'Switzerland', 'CH', 'CHE', 'SZ', '+41', 'Europe ', 'Bern '),
(37, 'Cote D''ivoire', 'CI', 'CIV', 'IV', '+225', 'Africa ', 'Yamoussoukro'),
(38, 'Chile', 'CL', 'CHL', 'CI', '+56', 'South America ', 'Santiago '),
(39, 'Cameroon', 'CM', 'CMR', 'CM', '+237', 'Africa ', 'Yaounde '),
(40, 'China', 'CN', 'CHN', 'CH', '+86', 'Asia ', 'Beijing '),
(41, 'Colombia', 'CO', 'COL', 'CO', '+57', 'South America', 'Bogota '),
(42, 'Costa Rica', 'CR', 'CRI', 'CS', '+506', 'Central America and the Caribbean ', 'San Jose '),
(43, 'Cuba', 'CU', 'CUB', 'CU', '+53', 'Central America and the Caribbean ', 'Havana '),
(44, 'Cape Verde', 'CV', 'CPV', 'CV', '+238', 'World ', 'Praia '),
(45, 'Cyprus', 'CY', 'CYP', 'CY', '+357', 'Middle East ', 'Nicosia '),
(46, 'Czech Republic', 'CZ', 'CZE', 'EZ', '+420', 'Europe ', 'Prague '),
(47, 'Germany', 'DE', 'DEU', 'GM', '+49', 'Europe ', 'Berlin '),
(48, 'Djibouti', 'DJ', 'DJI', 'DJ', '+253', 'Africa ', 'Djibouti '),
(49, 'Denmark', 'DK', 'DNK', 'DA', '+45', 'Europe ', 'Copenhagen '),
(50, 'Dominica', 'DM', 'DMA', 'DO', '+1 767', 'Central America and the Caribbean ', 'Roseau '),
(51, 'Dominican Republic', 'DO', 'DOM', 'DR', '+1 809', 'Central America and the Caribbean ', 'Santo Domingo '),
(52, 'Algeria', 'DZ', 'DZA', 'AG', '+213', 'Africa ', 'Algiers '),
(53, 'Ecuador', 'EC', 'ECU', 'EC', '+593', 'South America ', 'Quito '),
(54, 'Estonia', 'EE', 'EST', 'EN', '+372', 'Europe ', 'Tallinn '),
(55, 'Egypt', 'EG', 'EGY', 'EG', '+20', 'Africa ', 'Cairo '),
(56, 'Western Sahara', 'EH', 'ESH', 'WI', '+212', 'Africa ', '--'),
(57, 'Eritrea', 'ER', 'ERI', 'ER', '+291', 'Africa ', 'Asmara '),
(58, 'Spain', 'ES', 'ESP', 'SP', '+34', 'Europe ', 'Madrid '),
(59, 'Ethiopia', 'ET', 'ETH', 'ET', '+251', 'Africa ', 'Addis Ababa '),
(60, 'Finland', 'FI', 'FIN', 'FI', '+358', 'Europe ', 'Helsinki '),
(61, 'Fiji', 'FJ', 'FJI', 'FJ', '+679', 'Oceania ', 'Suva '),
(62, 'Micronesia, Federated States Of', 'FM', 'FSM', 'FM', '+691', 'Oceania ', 'Palikir '),
(63, 'Faroe Islands', 'FO', 'FRO', 'FO', '+298', 'Europe ', 'Torshavn '),
(64, 'France', 'FR', 'FRA', 'FR', '+33', 'Europe ', 'Paris '),
(65, 'Gabon', 'GA', 'GAB', 'GB', '+241', 'Africa ', 'Libreville '),
(66, 'United Kingdom', 'GB', 'GBR', 'UK', '+44', 'Europe ', 'London '),
(67, 'Grenada', 'GD', 'GRD', 'GJ', '+1 473', 'Central America and the Caribbean ', 'Saint George''s '),
(68, 'Georgia', 'GE', 'GEO', 'GG', '+995', 'Commonwealth of Independent States ', 'T''bilisi '),
(69, 'French Guiana', 'GF', 'GUF', 'FG', '+594', 'South America ', 'Cayenne '),
(70, 'Ghana', 'GH', 'GHA', 'GH', '+233', 'Africa ', 'Accra '),
(71, 'Gambia', 'GM', 'GMB', 'GA', '+220', 'Africa ', 'Banjul '),
(72, 'Guinea', 'GN', 'GIN', 'GV', '+224', 'Africa ', 'Conakry '),
(73, 'Guadeloupe', 'GP', 'GLP', 'GP', '+590', 'Central America and the Caribbean ', 'Basse-Terre '),
(74, 'Equatorial Guinea', 'GQ', 'GNQ', 'EK', '+240', 'Africa ', 'Malabo '),
(75, 'Greece', 'GR', 'GRC', 'GR', '+30', 'Europe ', 'Athens '),
(76, 'Guatemala', 'GT', 'GTM', 'GT', '+502', 'Central America and the Caribbean ', 'Guatemala '),
(77, 'Guinea-bissau', 'GW', 'GNB', 'PU', '+245', 'Africa ', 'Bissau '),
(78, 'Guyana', 'GY', 'GUY', 'GY', '+592', 'South America ', 'Georgetown '),
(79, 'Honduras', 'HN', 'HND', 'HO', '+504', 'Central America and the Caribbean ', 'Tegucigalpa '),
(80, 'Croatia', 'HR', 'HRV', 'HR', '+385', 'Europe ', 'Zagreb '),
(81, 'Haiti', 'HT', 'HTI', 'HA', '+509', 'Central America and the Caribbean ', 'Port-au-Prince '),
(82, 'Hungary', 'HU', 'HUN', 'HU', '+36', 'Europe ', 'Budapest '),
(83, 'Indonesia', 'ID', 'IDN', 'ID', '+62', 'Southeast Asia ', 'Jakarta '),
(84, 'Ireland', 'IE', 'IRL', 'EI', '+353', 'Europe ', 'Dublin '),
(85, 'Israel', 'IL', 'ISR', 'IS', '+972', 'Middle East ', 'Jerusalem'),
(86, 'India', 'IN', 'IND', 'IN', '+91', 'Asia ', 'New Delhi '),
(87, 'British Indian Ocean Territory', 'IO', 'IOT', 'IO', '+246', 'World ', '--'),
(88, 'Iraq', 'IQ', 'IRQ', 'IZ', '+964', 'Middle East ', 'Baghdad '),
(89, 'Iran, Islamic Republic Of', 'IR', 'IRN', 'IR', '+98', 'Middle East ', 'Tehran'),
(90, 'Iceland', 'IS', 'ISL', 'IC', '+354', 'Arctic Region ', 'Reykjavik '),
(91, 'Italy', 'IT', 'ITA', 'IT', '+39', 'Europe ', 'Rome '),
(92, 'Jersey', 'JE', 'JEY', 'JE', '+44', 'Europe ', 'St. Helier '),
(93, 'Jamaica', 'JM', 'JAM', 'JM', '+1 876', 'Central America and the Caribbean ', 'Kingston '),
(94, 'Jordan', 'JO', 'JOR', 'JO', '+962', 'Middle East ', 'Amman '),
(95, 'Japan', 'JP', 'JPN', 'JA', '+81', 'Asia ', 'Tokyo '),
(96, 'Kenya', 'KE', 'KEN', 'KE', '+254', 'Africa ', 'Nairobi '),
(97, 'Kyrgyzstan', 'KG', 'KGZ', 'KG', '+996', 'Commonwealth of Independent States ', 'Bishkek '),
(98, 'Cambodia', 'KH', 'KHM', 'CB', '+855', 'Southeast Asia ', 'Phnom Penh '),
(99, 'Kiribati', 'KI', 'KIR', 'KR', '+686', 'Oceania ', 'Tarawa '),
(100, 'Comoros', 'KM', 'COM', 'CN', '+269', 'Africa ', 'Moroni '),
(101, 'Saint Kitts And Nevis', 'KN', 'KNA', 'SC', '+1 869', 'Central America and the Caribbean ', 'Basseterre '),
(102, 'Korea, Democratic People''s Republic Of', 'KR', 'KOR', 'KS', '+82', 'Asia', 'Seoul'),
(103, 'Korea, Republic Of', 'KP', 'PRK', 'KN', '+850', 'Asia', 'P''yongyang '),
(104, 'Kuwait', 'KW', 'KWT', 'KU', '+965', 'Middle East ', 'Kuwait '),
(105, 'Kazakhstan', 'KZ', 'KAZ', 'KZ', '+7', 'Commonwealth of Independent States ', 'Astana '),
(106, 'Lao People''s Democratic Republic', 'LA', 'LAO', 'LA', '+856', 'Southeast Asia ', 'Vientiane '),
(107, 'Lebanon', 'LB', 'LBN', 'LE', '+961', 'Middle East ', 'Beirut '),
(108, 'Saint Lucia', 'LC', 'LCA', 'ST', '+1 758', 'Central America and the Caribbean ', 'Castries '),
(109, 'Liechtenstein', 'LI', 'LIE', 'LS', '+423', 'Europe ', 'Vaduz '),
(110, 'Sri Lanka', 'LK', 'LKA', 'CE', '+94', 'Asia ', 'Colombo'),
(111, 'Liberia', 'LR', 'LBR', 'LI', '+231', 'Africa ', 'Monrovia '),
(112, 'Lesotho', 'LS', 'LSO', 'LT', '+266', 'Africa ', 'Maseru '),
(113, 'Lithuania', 'LT', 'LTU', 'LH', '+370', 'Europe ', 'Vilnius '),
(114, 'Luxembourg', 'LU', 'LUX', 'LU', '+352', 'Europe ', 'Luxembourg '),
(115, 'Latvia', 'LV', 'LVA', 'LG', '+371', 'Europe ', 'Riga '),
(116, 'Libyan Arab Jamahiriya', 'LY', 'LBY', 'LY', '+218', 'Africa ', 'Tripoli '),
(117, 'Morocco', 'MA', 'MAR', 'MO', '+212', 'Africa ', 'Rabat '),
(118, 'Monaco', 'MC', 'MCO', 'MN', '+377', 'Europe ', 'Monaco '),
(119, 'Moldova', 'MD', 'MDA', 'MD', '+373', 'Commonwealth of Independent States ', 'Chisinau '),
(120, 'Montenegro', 'ME', 'MNE', 'ME', '+382', 'Europe ', 'PodgoricaÂ '),
(121, 'Madagascar', 'MG', 'MDG', 'MA', '+261', 'Africa ', 'Antananarivo '),
(122, 'Macedonia, The Former Yugoslav Republic Of', 'MK', 'MKD', 'MK', '+389', 'Europe', 'Skopje'),
(123, 'Mali', 'ML', 'MLI', 'ML', '+223', 'Africa ', 'Bamako '),
(124, 'Myanmar', 'MM', 'MMR', 'BM', '+95', 'Southeast Asia', 'Rangoon'),
(125, 'Mongolia', 'MN', 'MNG', 'MG', '+976', 'Asia ', 'Ulaanbaatar '),
(126, 'Macau', 'MO', 'MAC', 'MC', '+853', 'Southeast Asia', 'Macao'),
(127, 'Martinique', 'MQ', 'MTQ', 'MB', '+596', 'Central America and the Caribbean ', 'Fort-de-France '),
(128, 'Mauritania', 'MR', 'MRT', 'MR', '+222', 'Africa ', 'Nouakchott '),
(129, 'Montserrat', 'MS', 'MSR', 'MH', '+1 664', 'Central America and the Caribbean ', 'Plymouth'),
(130, 'Mauritius', 'MU', 'MUS', 'MP', '+230', 'World ', 'Port Louis '),
(131, 'Maldives', 'MV', 'MDV', 'MV', '+960', 'Asia ', 'Male '),
(132, 'Malawi', 'MW', 'MWI', 'MI', '+265', 'Africa ', 'Lilongwe '),
(133, 'Mexico', 'MX', 'MEX', 'MX', '+52', 'North America ', 'Mexico '),
(134, 'Malaysia', 'MY', 'MYS', 'MY', '+60', 'Southeast Asia ', 'Kuala Lumpur '),
(135, 'Mozambique', 'MZ', 'MOZ', 'MZ', '+258', 'Africa ', 'Maputo '),
(136, 'Namibia', 'NA', 'NAM', 'WA', '+264', 'Africa ', 'Windhoek '),
(137, 'Niger', 'NE', 'NER', 'NG', '+227', 'Africa ', 'Niamey '),
(138, 'Nigeria', 'NG', 'NGA', 'NI', '+234', 'Africa ', 'Abuja'),
(139, 'Nicaragua', 'NI', 'NIC', 'NU', '+505', 'Central America and the Caribbean ', 'Managua '),
(140, 'Netherlands', 'NL', 'NLD', 'NL', '+31', 'Europe ', 'Amsterdam '),
(141, 'Norway', 'NO', 'NOR', 'NO', '+47', 'Europe ', 'Oslo '),
(142, 'Nepal', 'NP', 'NPL', 'NP', '+977', 'Asia ', 'Kathmandu '),
(143, 'New Zealand', 'NZ', 'NZL', 'NZ', '+64', 'Oceania ', 'Wellington '),
(144, 'Oman', 'OM', 'OMN', 'MU', '+968', 'Middle East ', 'Muscat '),
(145, 'Panama', 'PA', 'PAN', 'PM', '+507', 'Central America and the Caribbean ', 'Panama '),
(146, 'Peru', 'PE', 'PER', 'PE', '+51', 'South America ', 'Lima '),
(147, 'Papua New Guinea', 'PG', 'PNG', 'PP', '+675', 'Oceania ', 'Port Moresby '),
(148, 'Philippines', 'PH', 'PHL', 'RP', '+63', 'Southeast Asia ', 'Manila '),
(149, 'Pakistan', 'PK', 'PAK', 'PK', '+92', 'Asia ', 'Islamabad '),
(150, 'Poland', 'PL', 'POL', 'PL', '+48', 'Europe ', 'Warsaw '),
(151, 'Palestinian Territory', 'PS', 'PSE', '--', '+970', 'Asia ', '--'),
(152, 'Portugal', 'PT', 'PRT', 'PO', '+351', 'Europe ', 'Lisbon '),
(153, 'Palau', 'PW', 'PLW', 'PS', '+680', 'Oceania ', 'Koror '),
(154, 'Paraguay', 'PY', 'PRY', 'PA', '+595', 'South America ', 'Asuncion '),
(155, 'Qatar', 'QA', 'QAT', 'QA', '+974', 'Middle East ', 'Doha '),
(156, 'Reunion', 'RE', 'REU', 'RE', '+262', 'World', 'Saint-Denis'),
(157, 'Romania', 'RO', 'ROU', 'RO', '+40', 'Europe ', 'Bucharest '),
(158, 'Serbia', 'RS', 'SRB', 'RS', '+381', 'Asia ', 'Belgrade '),
(159, 'Russian Federation', 'RU', 'RUS', 'RS', '+7', 'Asia', 'Moscow'),
(160, 'Rwanda', 'RW', 'RWA', 'RW', '+250', 'Africa ', 'Kigali '),
(161, 'Saudi Arabia', 'SA', 'SAU', 'SA', '+966', 'Middle East ', 'Riyadh '),
(162, 'Solomon Islands', 'SB', 'SLB', 'BP', '+677', 'Oceania ', 'Honiara '),
(163, 'Seychelles', 'SC', 'SYC', 'SE', '+248', 'Africa ', 'Victoria '),
(164, 'Sudan', 'SD', 'SDN', 'SU', '+249', 'Africa ', 'Khartoum '),
(165, 'Sweden', 'SE', 'SWE', 'SW', '+46', 'Europe ', 'Stockholm '),
(166, 'Saint Helena', 'SH', 'SHN', 'SH', '+290', 'Africa ', 'Jamestown '),
(167, 'Slovenia', 'SI', 'SVN', 'SI', '+386', 'Europe ', 'Ljubljana '),
(168, 'Slovakia', 'SK', 'SVK', 'LO', '+421', 'Europe ', 'Bratislava '),
(169, 'Sierra Leone', 'SL', 'SLE', 'SL', '+232', 'Africa ', 'Freetown '),
(170, 'San Marino', 'SM', 'SMR', 'SM', '+378', 'Europe ', 'San Marino '),
(171, 'Senegal', 'SN', 'SEN', 'SG', '+221', 'Africa ', 'Dakar '),
(172, 'Somalia', 'SO', 'SOM', 'SO', '+252', 'Africa ', 'Mogadishu '),
(173, 'Suriname', 'SR', 'SUR', 'NS', '+597', 'South America ', 'Paramaribo '),
(174, 'Sao Tome And Principe', 'ST', 'STP', 'TP', '+239', 'Africa', 'Sao Tome'),
(175, 'El Salvador', 'SV', 'SLV', 'ES', '+503', 'Central America and the Caribbean ', 'San Salvador '),
(176, 'Syrian Arab Republic', 'SY', 'SYR', 'SY', '+963', 'Middle East', 'Damascus '),
(177, 'Swaziland', 'SZ', 'SWZ', 'WZ', '+268', 'Africa ', 'Mbabane '),
(178, 'Chad', 'TD', 'TCD', 'CD', '+235', 'Africa ', 'N''Djamena '),
(179, 'French Southern Territories', 'TF', 'ATF', 'FS', '--', 'Antarctic Region ', '--'),
(180, 'Togo', 'TG', 'TGO', 'TO', '+228', 'Africa ', 'Lome '),
(181, 'Thailand', 'TH', 'THA', 'TH', '+66', 'Southeast Asia ', 'Bangkok '),
(182, 'Tajikistan', 'TJ', 'TJK', 'TI', '+992', 'Commonwealth of Independent States ', 'Dushanbe '),
(183, 'Timor-leste (east Timor)', 'TL', 'TLS', 'TL', '+670', 'Middle East', 'Dili'),
(184, 'Turkmenistan', 'TM', 'TKM', 'TX', '+993', 'Commonwealth of Independent States ', 'Ashgabat '),
(185, 'Tunisia', 'TN', 'TUN', 'TS', '+216', 'Africa ', 'Tunis '),
(186, 'Turkey', 'TR', 'TUR', 'TU', '+90', 'Middle East ', 'Ankara '),
(187, 'Trinidad And Tobago', 'TT', 'TTO', 'TD', '+1 868', 'Central America and the Caribbean ', 'Port-of-Spain '),
(188, 'Taiwan, Province Of China', 'TW', 'TWN', 'TW', '+886', 'Southeast Asia', 'Taipei '),
(189, 'Tanzania, United Republic Of', 'TZ', 'TZA', 'TZ', '+255', 'Africa', 'Dar es Salaam'),
(190, 'Ukraine', 'UA', 'UKR', 'UP', '+380', 'Commonwealth of Independent States ', 'Kiev '),
(191, 'Uganda', 'UG', 'UGA', 'UG', '+256', 'Africa ', 'Kampala '),
(192, 'United States', 'US', 'USA', 'US', '+1', 'North America ', 'Washington, DC '),
(193, 'Uruguay', 'UY', 'URY', 'UY', '+598', 'South America ', 'Montevideo '),
(194, 'Uzbekistan', 'UZ', 'UZB', 'UZ', '+998', 'Commonwealth of Independent States ', 'Tashkent'),
(195, 'Saint Vincent And The Grenadines', 'VC', 'VCT', 'VC', '+1 784', 'Central America and the Caribbean ', 'Kingstown '),
(196, 'Venezuela', 'VE', 'VEN', 'VE', '+58', 'South America', 'Caracas '),
(197, 'Vietnam', 'VN', 'VNM', 'VM', '+84', 'Southeast Asia ', 'Hanoi '),
(198, 'Vanuatu', 'VU', 'VUT', 'NH', '+678', 'Oceania ', 'Port-Vila '),
(199, 'Yemen', 'YE', 'YEM', 'YM', '+967', 'Middle East ', 'Sanaa '),
(200, 'South Africa', 'ZA', 'ZAF', 'SF', '+27', 'Africa ', 'Pretoria'),
(201, 'Zambia', 'ZM', 'ZMB', 'ZA', '+260', 'Africa', '--'),
(202, 'Zimbabwe', 'ZW', 'ZWE', 'ZI', '+263', 'Africa ', 'Harare ');
