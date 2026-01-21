<?php

use PHPUnit\Framework\TestCase;
use GPDAuth\Library\AuthConfigKey;

class AuthConfigKeyTest extends TestCase
{
    /**
     * Test enum values
     */
    public function testEnumValues()
    {
        $this->assertEquals('gpd_auth_jwt_algorithm_key', AuthConfigKey::JwtAlgorithm->value);
        $this->assertEquals('gpd_auth_jwt_secure_key', AuthConfigKey::JwtSecureKey->value);
        $this->assertEquals('gpd_auth_session_key', AuthConfigKey::AuthSessionKey->value);
        $this->assertEquals('gpd_auth_auth_method_key', AuthConfigKey::AuthMethodKey->value);
        $this->assertEquals('gpd_auth_iss_key', AuthConfigKey::AuthIssKey->value);
        $this->assertEquals('gpd_auth_jwt_default_expiration_time', AuthConfigKey::JwtExpirationTime->value);
        $this->assertEquals('gpd_auth_jwt_iss_config', AuthConfigKey::JwtIssConfig->value);
        $this->assertEquals('gpd_auth_jwt_iss_allowed_roes', AuthConfigKey::AuthIssAllowedRoles->value);
    }

    /**
     * Test getValue method
     */
    public function testGetValue()
    {
        $this->assertEquals('gpd_auth_jwt_algorithm_key', AuthConfigKey::JwtAlgorithm->getValue());
        $this->assertEquals('gpd_auth_jwt_secure_key', AuthConfigKey::JwtSecureKey->getValue());
    }

    /**
     * Test fromString method
     */
    public function testFromString()
    {
        $this->assertEquals(AuthConfigKey::JwtAlgorithm, AuthConfigKey::fromString('gpd_auth_jwt_algorithm_key'));
        $this->assertEquals(AuthConfigKey::JwtSecureKey, AuthConfigKey::fromString('gpd_auth_jwt_secure_key'));
    }

    /**
     * Test fromString with invalid value
     */
    public function testFromStringWithInvalidValue()
    {
        $this->expectException(\ValueError::class);
        AuthConfigKey::fromString('invalid_key');
    }

    /**
     * Test tryFromString method
     */
    public function testTryFromString()
    {
        $this->assertEquals(AuthConfigKey::JwtAlgorithm, AuthConfigKey::tryFromString('gpd_auth_jwt_algorithm_key'));
        $this->assertNull(AuthConfigKey::tryFromString('invalid_key'));
        $this->assertEquals(AuthConfigKey::JwtSecureKey, AuthConfigKey::tryFromString('invalid_key', AuthConfigKey::JwtSecureKey));
    }

    /**
     * Test isJwtRelated method
     */
    public function testIsJwtRelated()
    {
        $this->assertTrue(AuthConfigKey::JwtAlgorithm->isJwtRelated());
        $this->assertTrue(AuthConfigKey::JwtSecureKey->isJwtRelated());
        $this->assertTrue(AuthConfigKey::JwtExpirationTime->isJwtRelated());
        $this->assertTrue(AuthConfigKey::JwtIssConfig->isJwtRelated());
        $this->assertTrue(AuthConfigKey::AuthIssKey->isJwtRelated());
        $this->assertTrue(AuthConfigKey::AuthIssAllowedRoles->isJwtRelated());
        
        $this->assertFalse(AuthConfigKey::AuthSessionKey->isJwtRelated());
        $this->assertFalse(AuthConfigKey::AuthMethodKey->isJwtRelated());
    }

    /**
     * Test isSessionRelated method
     */
    public function testIsSessionRelated()
    {
        $this->assertTrue(AuthConfigKey::AuthSessionKey->isSessionRelated());
        $this->assertTrue(AuthConfigKey::AuthMethodKey->isSessionRelated());
        
        $this->assertFalse(AuthConfigKey::JwtAlgorithm->isSessionRelated());
        $this->assertFalse(AuthConfigKey::JwtSecureKey->isSessionRelated());
    }

    /**
     * Test isSecurityCritical method
     */
    public function testIsSecurityCritical()
    {
        $this->assertTrue(AuthConfigKey::JwtSecureKey->isSecurityCritical());
        $this->assertTrue(AuthConfigKey::JwtAlgorithm->isSecurityCritical());
        $this->assertTrue(AuthConfigKey::AuthIssAllowedRoles->isSecurityCritical());
        
        $this->assertFalse(AuthConfigKey::AuthSessionKey->isSecurityCritical());
        $this->assertFalse(AuthConfigKey::JwtExpirationTime->isSecurityCritical());
    }

    /**
     * Test getDescription method
     */
    public function testGetDescription()
    {
        $this->assertStringContainsString('JWT', AuthConfigKey::JwtAlgorithm->getDescription());
        $this->assertStringContainsString('secreta', AuthConfigKey::JwtSecureKey->getDescription());
        $this->assertStringContainsString('sesión', AuthConfigKey::AuthSessionKey->getDescription());
    }

    /**
     * Test getJwtKeys static method
     */
    public function testGetJwtKeys()
    {
        $jwtKeys = AuthConfigKey::getJwtKeys();
        
        $this->assertContains(AuthConfigKey::JwtAlgorithm, $jwtKeys);
        $this->assertContains(AuthConfigKey::JwtSecureKey, $jwtKeys);
        $this->assertContains(AuthConfigKey::JwtExpirationTime, $jwtKeys);
        $this->assertContains(AuthConfigKey::JwtIssConfig, $jwtKeys);
        $this->assertContains(AuthConfigKey::AuthIssKey, $jwtKeys);
        $this->assertContains(AuthConfigKey::AuthIssAllowedRoles, $jwtKeys);
        
        $this->assertNotContains(AuthConfigKey::AuthSessionKey, $jwtKeys);
        $this->assertNotContains(AuthConfigKey::AuthMethodKey, $jwtKeys);
    }

    /**
     * Test getSessionKeys static method
     */
    public function testGetSessionKeys()
    {
        $sessionKeys = AuthConfigKey::getSessionKeys();
        
        $this->assertContains(AuthConfigKey::AuthSessionKey, $sessionKeys);
        $this->assertContains(AuthConfigKey::AuthMethodKey, $sessionKeys);
        
        $this->assertNotContains(AuthConfigKey::JwtAlgorithm, $sessionKeys);
        $this->assertNotContains(AuthConfigKey::JwtSecureKey, $sessionKeys);
    }

    /**
     * Test getSecurityCriticalKeys static method
     */
    public function testGetSecurityCriticalKeys()
    {
        $criticalKeys = AuthConfigKey::getSecurityCriticalKeys();
        
        $this->assertContains(AuthConfigKey::JwtSecureKey, $criticalKeys);
        $this->assertContains(AuthConfigKey::JwtAlgorithm, $criticalKeys);
        $this->assertContains(AuthConfigKey::AuthIssAllowedRoles, $criticalKeys);
        
        $this->assertNotContains(AuthConfigKey::AuthSessionKey, $criticalKeys);
        $this->assertNotContains(AuthConfigKey::JwtExpirationTime, $criticalKeys);
    }

    /**
     * Test all cases
     */
    public function testAllCases()
    {
        $cases = AuthConfigKey::cases();
        $this->assertCount(8, $cases);
        
        $expectedKeys = [
            'gpd_auth_jwt_algorithm_key',
            'gpd_auth_jwt_secure_key',
            'gpd_auth_session_key',
            'gpd_auth_auth_method_key',
            'gpd_auth_iss_key',
            'gpd_auth_jwt_default_expiration_time',
            'gpd_auth_jwt_iss_config',
            'gpd_auth_jwt_iss_allowed_roes'
        ];
        
        $actualKeys = array_map(fn($case) => $case->value, $cases);
        
        $this->assertEquals($expectedKeys, $actualKeys);
    }
}