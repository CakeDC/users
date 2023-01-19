<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2023, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Test\TestCase\Webauthn;

use Cake\TestSuite\TestCase;
use CakeDC\Users\Webauthn\Base64Utility;
use ParagonIE\ConstantTime\Base64UrlSafe;

class Base64UtilityTest extends TestCase
{
    /**
     * Test methods basicEncode and basicDecode
     *
     * @param string $originalText
     * @dataProvider dataProviderBasicEncodeDecodeText
     * @return void
     */
    public function testBasicEncodeDecode($originalText)
    {
        $encoded = Base64Utility::basicEncode($originalText);
        $this->assertNotEmpty($encoded);
        $this->assertStringEndsNotWith('=', $encoded);
        $decoded = Base64Utility::basicDecode($encoded);
        $this->assertSame($originalText, $decoded);
    }

    /**
     * @return \string[][]
     */
    public function dataProviderBasicEncodeDecodeText(): array
    {
        return [
            ['00000000-0000-0000-0000-000000000002'],
            ['$2y$10$Nvu7ipP.z8tiIl75OdUvt.86vuG6iKMoHIOc7O7mboFI85hSyTEde'],
            ['First Name'],
        ];
    }

    /**
     * Test method complyEncodedNoPadding
     *
     * @param string $encodedTextWithPadding
     * @param string $originalText
     * @dataProvider dataProviderComplyEncodedNoPadding
     * @return void
     */
    public function testComplyEncodedNoPadding($encodedTextWithPadding, $originalText)
    {
        $encoded = Base64Utility::complyEncodedNoPadding($encodedTextWithPadding);
        $this->assertNotEmpty($encoded);
        $this->assertStringEndsNotWith('=', $encoded);
        $decoded = Base64UrlSafe::decodeNoPadding($encoded);
        $this->assertSame($originalText, $decoded);
    }

    /**
     * @return \string[][]
     */
    public function dataProviderComplyEncodedNoPadding(): array
    {
        return [
            ['JDJ5JDEwJE52dTdpcFAuejh0aUlsNzVPZFV2dC44NnZ1RzZpS01vSElPYzdPN21ib0ZJODU=', '$2y$10$Nvu7ipP.z8tiIl75OdUvt.86vuG6iKMoHIOc7O7mboFI85'],
            ['MDAwMDAwMDAwMDM=', '00000000003'],
            //does not end with =
            ['MDAwMDAwMDAtMDAwMC0wMDAwLTAwMDAtMDAwMDAwMDAwMDAz', '00000000-0000-0000-0000-000000000003'],
            //end with ==
            ['Rmlyc3QgTmFtZQ==', 'First Name'],
        ];
    }
}
