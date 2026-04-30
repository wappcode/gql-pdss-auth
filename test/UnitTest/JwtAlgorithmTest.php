<?php

use PHPUnit\Framework\TestCase;
use GPDAuth\Enums\JwtAlgorithm;

class JwtAlgorithmTest extends TestCase
{
    /**
     * Test enum values
     */
    public function testEnumValues()
    {
        $this->assertEquals('HS256', JwtAlgorithm::HS256->value);
        $this->assertEquals('HS384', JwtAlgorithm::HS384->value);
        $this->assertEquals('HS512', JwtAlgorithm::HS512->value);
        $this->assertEquals('RS256', JwtAlgorithm::RS256->value);
        $this->assertEquals('RS384', JwtAlgorithm::RS384->value);
        $this->assertEquals('RS512', JwtAlgorithm::RS512->value);
        $this->assertEquals('ES256', JwtAlgorithm::ES256->value);
        $this->assertEquals('ES384', JwtAlgorithm::ES384->value);
        $this->assertEquals('ES256K', JwtAlgorithm::ES256K->value);
    }

    /**
     * Test getValue method
     */
    public function testGetValue()
    {
        $this->assertEquals('HS256', JwtAlgorithm::HS256->getValue());
        $this->assertEquals('RS256', JwtAlgorithm::RS256->getValue());
    }

    /**
     * Test fromString method
     */
    public function testFromString()
    {
        $this->assertEquals(JwtAlgorithm::HS256, JwtAlgorithm::fromString('HS256'));
        $this->assertEquals(JwtAlgorithm::RS256, JwtAlgorithm::fromString('RS256'));
        $this->assertEquals(JwtAlgorithm::ES256, JwtAlgorithm::fromString('ES256'));
    }

    /**
     * Test fromString with invalid value
     */
    public function testFromStringWithInvalidValue()
    {
        $this->expectException(\ValueError::class);
        JwtAlgorithm::fromString('INVALID');
    }

    /**
     * Test tryFromString method
     */
    public function testTryFromString()
    {
        $this->assertEquals(JwtAlgorithm::HS256, JwtAlgorithm::tryFromString('HS256'));
        $this->assertNull(JwtAlgorithm::tryFromString('INVALID'));
        $this->assertEquals(JwtAlgorithm::HS256, JwtAlgorithm::tryFromString('INVALID', JwtAlgorithm::HS256));
    }

    /**
     * Test isSymmetric method
     */
    public function testIsSymmetric()
    {
        $this->assertTrue(JwtAlgorithm::HS256->isSymmetric());
        $this->assertTrue(JwtAlgorithm::HS384->isSymmetric());
        $this->assertTrue(JwtAlgorithm::HS512->isSymmetric());

        $this->assertFalse(JwtAlgorithm::RS256->isSymmetric());
        $this->assertFalse(JwtAlgorithm::ES256->isSymmetric());
    }

    /**
     * Test isAsymmetric method
     */
    public function testIsAsymmetric()
    {
        $this->assertFalse(JwtAlgorithm::HS256->isAsymmetric());
        $this->assertFalse(JwtAlgorithm::HS384->isAsymmetric());
        $this->assertFalse(JwtAlgorithm::HS512->isAsymmetric());

        $this->assertTrue(JwtAlgorithm::RS256->isAsymmetric());
        $this->assertTrue(JwtAlgorithm::ES256->isAsymmetric());
    }

    /**
     * Test isRSA method
     */
    public function testIsRSA()
    {
        $this->assertTrue(JwtAlgorithm::RS256->isRSA());
        $this->assertTrue(JwtAlgorithm::RS384->isRSA());
        $this->assertTrue(JwtAlgorithm::RS512->isRSA());

        $this->assertFalse(JwtAlgorithm::HS256->isRSA());
        $this->assertFalse(JwtAlgorithm::ES256->isRSA());
    }

    /**
     * Test isECDSA method
     */
    public function testIsECDSA()
    {
        $this->assertTrue(JwtAlgorithm::ES256->isECDSA());
        $this->assertTrue(JwtAlgorithm::ES384->isECDSA());
        $this->assertTrue(JwtAlgorithm::ES256K->isECDSA());

        $this->assertFalse(JwtAlgorithm::HS256->isECDSA());
        $this->assertFalse(JwtAlgorithm::RS256->isECDSA());
    }

    /**
     * Test getHashAlgorithm method
     */
    public function testGetHashAlgorithm()
    {
        $this->assertEquals('SHA256', JwtAlgorithm::HS256->getHashAlgorithm());
        $this->assertEquals('SHA256', JwtAlgorithm::RS256->getHashAlgorithm());
        $this->assertEquals('SHA256', JwtAlgorithm::ES256->getHashAlgorithm());
        $this->assertEquals('SHA256', JwtAlgorithm::ES256K->getHashAlgorithm());

        $this->assertEquals('SHA384', JwtAlgorithm::HS384->getHashAlgorithm());
        $this->assertEquals('SHA384', JwtAlgorithm::RS384->getHashAlgorithm());
        $this->assertEquals('SHA384', JwtAlgorithm::ES384->getHashAlgorithm());

        $this->assertEquals('SHA512', JwtAlgorithm::HS512->getHashAlgorithm());
        $this->assertEquals('SHA512', JwtAlgorithm::RS512->getHashAlgorithm());
    }

    /**
     * Test getDescription method
     */
    public function testGetDescription()
    {
        $this->assertStringContainsString('HMAC', JwtAlgorithm::HS256->getDescription());
        $this->assertStringContainsString('simétrico', JwtAlgorithm::HS256->getDescription());

        $this->assertStringContainsString('RSA', JwtAlgorithm::RS256->getDescription());
        $this->assertStringContainsString('asimétrico', JwtAlgorithm::RS256->getDescription());

        $this->assertStringContainsString('ECDSA', JwtAlgorithm::ES256->getDescription());
        $this->assertStringContainsString('curva elíptica', JwtAlgorithm::ES256->getDescription());

        $this->assertStringContainsString('Bitcoin/Ethereum', JwtAlgorithm::ES256K->getDescription());
    }

    /**
     * Test getSecurityLevel method
     */
    public function testGetSecurityLevel()
    {
        $this->assertEquals(3, JwtAlgorithm::HS256->getSecurityLevel());
        $this->assertEquals(3, JwtAlgorithm::RS256->getSecurityLevel());
        $this->assertEquals(3, JwtAlgorithm::ES256->getSecurityLevel());

        $this->assertEquals(4, JwtAlgorithm::HS384->getSecurityLevel());
        $this->assertEquals(4, JwtAlgorithm::RS384->getSecurityLevel());
        $this->assertEquals(4, JwtAlgorithm::ES384->getSecurityLevel());
        $this->assertEquals(4, JwtAlgorithm::ES256K->getSecurityLevel());

        $this->assertEquals(5, JwtAlgorithm::HS512->getSecurityLevel());
        $this->assertEquals(5, JwtAlgorithm::RS512->getSecurityLevel());
    }

    /**
     * Test requiresKeyPair method
     */
    public function testRequiresKeyPair()
    {
        $this->assertFalse(JwtAlgorithm::HS256->requiresKeyPair());
        $this->assertFalse(JwtAlgorithm::HS384->requiresKeyPair());
        $this->assertFalse(JwtAlgorithm::HS512->requiresKeyPair());

        $this->assertTrue(JwtAlgorithm::RS256->requiresKeyPair());
        $this->assertTrue(JwtAlgorithm::ES256->requiresKeyPair());
        $this->assertTrue(JwtAlgorithm::ES256K->requiresKeyPair());
    }

    /**
     * Test getRecommended static method
     */
    public function testGetRecommended()
    {
        $recommended = JwtAlgorithm::getRecommended();

        $this->assertContains(JwtAlgorithm::HS256, $recommended);
        $this->assertContains(JwtAlgorithm::HS384, $recommended);
        $this->assertContains(JwtAlgorithm::RS256, $recommended);
        $this->assertContains(JwtAlgorithm::ES256, $recommended);

        // Estos no deberían estar en recomendados para uso general
        $this->assertNotContains(JwtAlgorithm::ES256K, $recommended);
    }

    /**
     * Test getSymmetricAlgorithms static method
     */
    public function testGetSymmetricAlgorithms()
    {
        $symmetric = JwtAlgorithm::getSymmetricAlgorithms();

        $this->assertContains(JwtAlgorithm::HS256, $symmetric);
        $this->assertContains(JwtAlgorithm::HS384, $symmetric);
        $this->assertContains(JwtAlgorithm::HS512, $symmetric);

        $this->assertNotContains(JwtAlgorithm::RS256, $symmetric);
        $this->assertNotContains(JwtAlgorithm::ES256, $symmetric);
    }

    /**
     * Test getAsymmetricAlgorithms static method
     */
    public function testGetAsymmetricAlgorithms()
    {
        $asymmetric = JwtAlgorithm::getAsymmetricAlgorithms();

        $this->assertContains(JwtAlgorithm::RS256, $asymmetric);
        $this->assertContains(JwtAlgorithm::ES256, $asymmetric);
        $this->assertContains(JwtAlgorithm::ES256K, $asymmetric);

        $this->assertNotContains(JwtAlgorithm::HS256, $asymmetric);
        $this->assertNotContains(JwtAlgorithm::HS384, $asymmetric);
    }

    /**
     * Test getDefault static method
     */
    public function testGetDefault()
    {
        $this->assertEquals(JwtAlgorithm::HS256, JwtAlgorithm::getDefault());
    }

    /**
     * Test all cases
     */
    public function testAllCases()
    {
        $cases = JwtAlgorithm::cases();
        $this->assertCount(9, $cases);

        $expectedAlgorithms = ['HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512', 'ES256', 'ES384', 'ES256K'];
        $actualAlgorithms = array_map(fn($case) => $case->value, $cases);

        $this->assertEquals($expectedAlgorithms, $actualAlgorithms);
    }

    /**
     * Test algorithm compatibility with common JWT libraries
     */
    public function testAlgorithmCompatibility()
    {
        // Los algoritmos más comunes que deben estar presentes
        $commonAlgorithms = ['HS256', 'HS384', 'HS512', 'RS256', 'ES256'];

        foreach ($commonAlgorithms as $algorithm) {
            $this->assertTrue(
                JwtAlgorithm::tryFromString($algorithm) !== null,
                "Algorithm $algorithm should be supported"
            );
        }
    }
}
