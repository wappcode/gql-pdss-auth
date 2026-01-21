<?php

use PHPUnit\Framework\TestCase;
use GPDAuth\Library\AuthMethod;

class AuthMethodTest extends TestCase
{
    /**
     * Test enum values
     */
    public function testEnumValues()
    {
        $this->assertEquals('SESSION', AuthMethod::Session->value);
        $this->assertEquals('JWT', AuthMethod::Jwt->value);
        $this->assertEquals('JWT_OR_SESSION', AuthMethod::JwtOrSession->value);
        $this->assertEquals('SESSION_OR_JWT', AuthMethod::SessionOrJwt->value);
    }

    /**
     * Test getValue method
     */
    public function testGetValue()
    {
        $this->assertEquals('SESSION', AuthMethod::Session->getValue());
        $this->assertEquals('JWT', AuthMethod::Jwt->getValue());
        $this->assertEquals('JWT_OR_SESSION', AuthMethod::JwtOrSession->getValue());
        $this->assertEquals('SESSION_OR_JWT', AuthMethod::SessionOrJwt->getValue());
    }

    /**
     * Test fromString method
     */
    public function testFromString()
    {
        $this->assertEquals(AuthMethod::Session, AuthMethod::fromString('SESSION'));
        $this->assertEquals(AuthMethod::Jwt, AuthMethod::fromString('JWT'));
        $this->assertEquals(AuthMethod::JwtOrSession, AuthMethod::fromString('JWT_OR_SESSION'));
        $this->assertEquals(AuthMethod::SessionOrJwt, AuthMethod::fromString('SESSION_OR_JWT'));
    }

    /**
     * Test fromString with invalid value
     */
    public function testFromStringWithInvalidValue()
    {
        $this->expectException(\ValueError::class);
        AuthMethod::fromString('INVALID');
    }

    /**
     * Test tryFromString method
     */
    public function testTryFromString()
    {
        $this->assertEquals(AuthMethod::Session, AuthMethod::tryFromString('SESSION'));
        $this->assertEquals(AuthMethod::Jwt, AuthMethod::tryFromString('JWT'));
        $this->assertNull(AuthMethod::tryFromString('INVALID'));
        
        // Test with default
        $this->assertEquals(AuthMethod::Session, AuthMethod::tryFromString('INVALID', AuthMethod::Session));
    }

    /**
     * Test usesSession method
     */
    public function testUsesSession()
    {
        $this->assertTrue(AuthMethod::Session->usesSession());
        $this->assertFalse(AuthMethod::Jwt->usesSession());
        $this->assertTrue(AuthMethod::JwtOrSession->usesSession());
        $this->assertTrue(AuthMethod::SessionOrJwt->usesSession());
    }

    /**
     * Test usesJwt method
     */
    public function testUsesJwt()
    {
        $this->assertFalse(AuthMethod::Session->usesJwt());
        $this->assertTrue(AuthMethod::Jwt->usesJwt());
        $this->assertTrue(AuthMethod::JwtOrSession->usesJwt());
        $this->assertTrue(AuthMethod::SessionOrJwt->usesJwt());
    }

    /**
     * Test isHybrid method
     */
    public function testIsHybrid()
    {
        $this->assertFalse(AuthMethod::Session->isHybrid());
        $this->assertFalse(AuthMethod::Jwt->isHybrid());
        $this->assertTrue(AuthMethod::JwtOrSession->isHybrid());
        $this->assertTrue(AuthMethod::SessionOrJwt->isHybrid());
    }

    /**
     * Test getPrimaryMethod
     */
    public function testGetPrimaryMethod()
    {
        $this->assertEquals(AuthMethod::Session, AuthMethod::Session->getPrimaryMethod());
        $this->assertEquals(AuthMethod::Jwt, AuthMethod::Jwt->getPrimaryMethod());
        $this->assertEquals(AuthMethod::Jwt, AuthMethod::JwtOrSession->getPrimaryMethod());
        $this->assertEquals(AuthMethod::Session, AuthMethod::SessionOrJwt->getPrimaryMethod());
    }

    /**
     * Test getFallbackMethod
     */
    public function testGetFallbackMethod()
    {
        $this->assertNull(AuthMethod::Session->getFallbackMethod());
        $this->assertNull(AuthMethod::Jwt->getFallbackMethod());
        $this->assertEquals(AuthMethod::Session, AuthMethod::JwtOrSession->getFallbackMethod());
        $this->assertEquals(AuthMethod::Jwt, AuthMethod::SessionOrJwt->getFallbackMethod());
    }

    /**
     * Test enum comparison
     */
    public function testEnumComparison()
    {
        $session1 = AuthMethod::Session;
        $session2 = AuthMethod::Session;
        $jwt = AuthMethod::Jwt;

        $this->assertTrue($session1 === $session2);
        $this->assertFalse($session1 === $jwt);
        $this->assertTrue($session1 === AuthMethod::Session);
    }

    /**
     * Test all enum cases
     */
    public function testAllCases()
    {
        $cases = AuthMethod::cases();
        $this->assertCount(4, $cases);
        
        $expectedValues = ['SESSION', 'JWT', 'JWT_OR_SESSION', 'SESSION_OR_JWT'];
        $actualValues = array_map(fn($case) => $case->value, $cases);
        
        $this->assertEquals($expectedValues, $actualValues);
    }

    /**
     * Test backwards compatibility with legacy values
     */
    public function testBackwardsCompatibility()
    {
        // Test que los valores del enum coincidan con los valores legacy esperados
        $this->assertEquals('SESSION', AuthMethod::Session->value);
        $this->assertEquals('JWT', AuthMethod::Jwt->value);
        $this->assertEquals('JWT_OR_SESSION', AuthMethod::JwtOrSession->value);
        $this->assertEquals('SESSION_OR_JWT', AuthMethod::SessionOrJwt->value);
        
        // Conversión de strings legacy a enum
        $this->assertEquals(AuthMethod::Session, AuthMethod::fromString('SESSION'));
        $this->assertEquals(AuthMethod::Jwt, AuthMethod::fromString('JWT'));
        $this->assertEquals(AuthMethod::JwtOrSession, AuthMethod::fromString('JWT_OR_SESSION'));
        $this->assertEquals(AuthMethod::SessionOrJwt, AuthMethod::fromString('SESSION_OR_JWT'));
    }
}