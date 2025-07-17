<?php

namespace App\Constants\General;

class CurrencyConstants
{
    // Global Currencies
    const USD = 'USD'; // US Dollar
    const NGN = 'NGN'; // Nigerian Naira
    const EUR = 'EUR'; // Euro
    const GBP = 'GBP'; // British Pound
    const AUD = 'AUD'; // Australian Dollar
    const CAD = 'CAD'; // Canadian Dollar
    const INR = 'INR'; // Indian Rupee
    const JPY = 'JPY'; // Japanese Yen
    const CNY = 'CNY'; // Chinese Yuan
    const AED = 'AED'; // UAE Dirham
    const BRL = 'BRL'; // Brazilian Real
    const CHF = 'CHF'; // Swiss Franc
    const SGD = 'SGD'; // Singapore Dollar

    // African Currencies
    const ZAR = 'ZAR'; // South African Rand
    const GHS = 'GHS'; // Ghanaian Cedi
    const KES = 'KES'; // Kenyan Shilling
    const EGP = 'EGP'; // Egyptian Pound
    const DZD = 'DZD'; // Algerian Dinar
    const MAD = 'MAD'; // Moroccan Dirham
    const TND = 'TND'; // Tunisian Dinar
    const XOF = 'XOF'; // West African CFA Franc
    const XAF = 'XAF'; // Central African CFA Franc
    const UGX = 'UGX'; // Ugandan Shilling
    const TZS = 'TZS'; // Tanzanian Shilling
    const RWF = 'RWF'; // Rwandan Franc
    const BIF = 'BIF'; // Burundian Franc
    const MWK = 'MWK'; // Malawian Kwacha
    const ZMW = 'ZMW'; // Zambian Kwacha
    const MZN = 'MZN'; // Mozambican Metical
    const SCR = 'SCR'; // Seychellois Rupee
    const MUR = 'MUR'; // Mauritian Rupee
    const LSL = 'LSL'; // Lesotho Loti
    const SZL = 'SZL'; // Eswatini Lilangeni
    const NAD = 'NAD'; // Namibian Dollar
    const BWP = 'BWP'; // Botswana Pula
    const CVE = 'CVE'; // Cape Verdean Escudo
    const SDG = 'SDG'; // Sudanese Pound
    const SOS = 'SOS'; // Somali Shilling
    const GMD = 'GMD'; // Gambian Dalasi
    const SLL = 'SLL'; // Sierra Leonean Leone
    const LRD = 'LRD'; // Liberian Dollar
    const DJF = 'DJF'; // Djiboutian Franc
    const ERN = 'ERN'; // Eritrean Nakfa
    const MGA = 'MGA'; // Malagasy Ariary
    const KMF = 'KMF'; // Comorian Franc
    const AOA = 'AOA'; // Angolan Kwanza
    const ETB = 'ETB'; // Ethiopian Birr
    const MRU = 'MRU'; // Mauritanian Ouguiya
    const SSP = 'SSP'; // South Sudanese Pound
    const LYD = 'LYD'; // Libyan Dinar

    const CURRENCY_CODES = [
        // Global
        self::USD, self::NGN, self::EUR, self::GBP, self::AUD, self::CAD,
        self::INR, self::JPY, self::CNY, self::AED, self::BRL, self::CHF, self::SGD,

        // Africa
        self::ZAR, self::GHS, self::KES, self::EGP, self::DZD, self::MAD,
        self::TND, self::XOF, self::XAF, self::UGX, self::TZS, self::RWF,
        self::BIF, self::MWK, self::ZMW, self::MZN, self::SCR, self::MUR,
        self::LSL, self::SZL, self::NAD, self::BWP, self::CVE, self::SDG,
        self::SOS, self::GMD, self::SLL, self::LRD, self::DJF, self::ERN,
        self::MGA, self::KMF, self::AOA, self::ETB, self::MRU, self::SSP, self::LYD,
    ];

    const CURRENCY_NAMES = [
        // Global
        self::USD => 'US Dollar',
        self::NGN => 'Nigerian Naira',
        self::EUR => 'Euro',
        self::GBP => 'British Pound',
        self::AUD => 'Australian Dollar',
        self::CAD => 'Canadian Dollar',
        self::INR => 'Indian Rupee',
        self::JPY => 'Japanese Yen',
        self::CNY => 'Chinese Yuan',
        self::AED => 'UAE Dirham',
        self::BRL => 'Brazilian Real',
        self::CHF => 'Swiss Franc',
        self::SGD => 'Singapore Dollar',

        // Africa
        self::ZAR => 'South African Rand',
        self::GHS => 'Ghanaian Cedi',
        self::KES => 'Kenyan Shilling',
        self::EGP => 'Egyptian Pound',
        self::DZD => 'Algerian Dinar',
        self::MAD => 'Moroccan Dirham',
        self::TND => 'Tunisian Dinar',
        self::XOF => 'West African CFA Franc',
        self::XAF => 'Central African CFA Franc',
        self::UGX => 'Ugandan Shilling',
        self::TZS => 'Tanzanian Shilling',
        self::RWF => 'Rwandan Franc',
        self::BIF => 'Burundian Franc',
        self::MWK => 'Malawian Kwacha',
        self::ZMW => 'Zambian Kwacha',
        self::MZN => 'Mozambican Metical',
        self::SCR => 'Seychellois Rupee',
        self::MUR => 'Mauritian Rupee',
        self::LSL => 'Lesotho Loti',
        self::SZL => 'Eswatini Lilangeni',
        self::NAD => 'Namibian Dollar',
        self::BWP => 'Botswana Pula',
        self::CVE => 'Cape Verdean Escudo',
        self::SDG => 'Sudanese Pound',
        self::SOS => 'Somali Shilling',
        self::GMD => 'Gambian Dalasi',
        self::SLL => 'Sierra Leonean Leone',
        self::LRD => 'Liberian Dollar',
        self::DJF => 'Djiboutian Franc',
        self::ERN => 'Eritrean Nakfa',
        self::MGA => 'Malagasy Ariary',
        self::KMF => 'Comorian Franc',
        self::AOA => 'Angolan Kwanza',
        self::ETB => 'Ethiopian Birr',
        self::MRU => 'Mauritanian Ouguiya',
        self::SSP => 'South Sudanese Pound',
        self::LYD => 'Libyan Dinar',
    ];

    const CURRENCY_SYMBOLS = [
        self::USD => '$',
        self::NGN => '₦',
        self::EUR => '€',
        self::GBP => '£',
        self::AUD => 'A$',
        self::CAD => 'C$',
        self::INR => '₹',
        self::JPY => '¥',
        self::CNY => '¥',
        self::AED => 'د.إ',
        self::BRL => 'R$',
        self::CHF => 'CHF',
        self::SGD => 'S$',

        self::ZAR => 'R',
        self::GHS => 'GH₵',
        self::KES => 'KSh',
        self::EGP => 'E£',
        self::DZD => 'دج',
        self::MAD => 'MAD',
        self::TND => 'د.ت',
        self::XOF => 'CFA',
        self::XAF => 'FCFA',
        self::UGX => 'USh',
        self::TZS => 'TSh',
        self::RWF => 'FRw',
        self::BIF => 'FBu',
        self::MWK => 'MK',
        self::ZMW => 'ZK',
        self::MZN => 'MT',
        self::SCR => '₨',
        self::MUR => 'Rs',
        self::LSL => 'L',
        self::SZL => 'E',
        self::NAD => 'N$',
        self::BWP => 'P',
        self::CVE => 'Esc',
        self::SDG => '£',
        self::SOS => 'Sh.So.',
        self::GMD => 'D',
        self::SLL => 'Le',
        self::LRD => 'L$',
        self::DJF => 'Fdj',
        self::ERN => 'Nkf',
        self::MGA => 'Ar',
        self::KMF => 'CF',
        self::AOA => 'Kz',
        self::ETB => 'Br',
        self::MRU => 'UM',
        self::SSP => 'SS£',
        self::LYD => 'LD',
    ];

    // You can optionally add them to CURRENCY_MAPPING if you want the country reference, or keep it short and dynamic using separate lookup functions.
}
