<?php

use PHPUnit\Framework\TestCase;
use GPDAuth\Library\PasswordManager;

class PasswordManagerTest extends TestCase
{
    private string $testPassword = 'TestPassword123!';
    private string $testSalt = 'test_salt_123';

    /**
     * Test encoding with Argon2id (default algorithm)
     */
    public function testEncodeWithArgon2id()
    {
        $hash = PasswordManager::encode($this->testPassword);
        
        // Argon2id hashes should start with $argon2id$
        $this->assertStringStartsWith('$argon2id$', $hash);
        $this->assertNotEmpty($hash);
        $this->assertNotEquals($this->testPassword, $hash);
        
        // Each call should generate a different hash due to salt randomization
        $hash2 = PasswordManager::encode($this->testPassword);
        $this->assertNotEquals($hash, $hash2);
    }

    /**
     * Test encoding with Argon2id explicitly
     */
    public function testEncodeWithExplicitArgon2id()
    {
        $hash = PasswordManager::encode($this->testPassword, null, PasswordManager::ARGON2ID);
        
        $this->assertStringStartsWith('$argon2id$', $hash);
        $this->assertTrue(password_verify($this->testPassword, $hash));
    }

    /**
     * Test encoding with Argon2id custom options
     */
    public function testEncodeWithArgon2idCustomOptions()
    {
        $options = [
            'memory_cost' => 32768, // 32 MB
            'time_cost' => 2,
            'threads' => 2
        ];
        
        $hash = PasswordManager::encode($this->testPassword, null, PasswordManager::ARGON2ID, $options);
        
        $this->assertStringStartsWith('$argon2id$', $hash);
        $this->assertTrue(password_verify($this->testPassword, $hash));
    }

    /**
     * Test encoding with Bcrypt
     */
    public function testEncodeWithBcrypt()
    {
        $hash = PasswordManager::encode($this->testPassword, null, PasswordManager::BCRYPT);
        
        // Bcrypt hashes should start with $2y$
        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertTrue(password_verify($this->testPassword, $hash));
    }

    /**
     * Test encoding with Bcrypt custom options
     */
    public function testEncodeWithBcryptCustomOptions()
    {
        $options = ['cost' => 10];
        
        $hash = PasswordManager::encode($this->testPassword, null, PasswordManager::BCRYPT, $options);
        
        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertStringContainsString('$10$', $hash); // Cost should be included in hash
        $this->assertTrue(password_verify($this->testPassword, $hash));
    }

    /**
     * Test encoding with legacy SHA256
     */
    public function testEncodeWithSHA256()
    {
        $hash = PasswordManager::encode($this->testPassword, $this->testSalt, PasswordManager::SHA256);
        
        $this->assertEquals(64, strlen($hash)); // SHA256 produces 64 character hash
        $this->assertEquals(hash('sha256', $this->testPassword . $this->testSalt), $hash);
    }

    /**
     * Test encoding with legacy SHA1
     */
    public function testEncodeWithSHA1()
    {
        $hash = PasswordManager::encode($this->testPassword, $this->testSalt, PasswordManager::SHA1);
        
        $this->assertEquals(40, strlen($hash)); // SHA1 produces 40 character hash
        $this->assertEquals(hash('sha1', $this->testPassword . $this->testSalt), $hash);
    }

    /**
     * Test encoding with legacy MD5
     */
    public function testEncodeWithMD5()
    {
        $hash = PasswordManager::encode($this->testPassword, $this->testSalt, PasswordManager::MD5);
        
        $this->assertEquals(32, strlen($hash)); // MD5 produces 32 character hash
        $this->assertEquals(hash('md5', $this->testPassword . $this->testSalt), $hash);
    }

    /**
     * Test encoding with unsupported algorithm
     */
    public function testEncodeWithUnsupportedAlgorithm()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Algoritmo de hash no soportado: unsupported');
        
        PasswordManager::encode($this->testPassword, null, 'unsupported');
    }

    /**
     * Test password verification with Argon2id
     */
    public function testVerifyArgon2id()
    {
        $hash = PasswordManager::encode($this->testPassword, null, PasswordManager::ARGON2ID);
        
        // Correct password should verify
        $this->assertTrue(PasswordManager::verify($this->testPassword, $hash));
        
        // Wrong password should fail
        $this->assertFalse(PasswordManager::verify('WrongPassword', $hash));
        
        // Auto-detection should work
        $this->assertTrue(PasswordManager::verify($this->testPassword, $hash, null, null));
    }

    /**
     * Test password verification with Bcrypt
     */
    public function testVerifyBcrypt()
    {
        $hash = PasswordManager::encode($this->testPassword, null, PasswordManager::BCRYPT);
        
        // Correct password should verify
        $this->assertTrue(PasswordManager::verify($this->testPassword, $hash));
        
        // Wrong password should fail
        $this->assertFalse(PasswordManager::verify('WrongPassword', $hash));
        
        // Auto-detection should work
        $this->assertTrue(PasswordManager::verify($this->testPassword, $hash, null, null));
    }

    /**
     * Test password verification with legacy algorithms
     */
    public function testVerifyLegacyAlgorithms()
    {
        $algorithms = [PasswordManager::SHA256, PasswordManager::SHA1, PasswordManager::MD5];
        
        foreach ($algorithms as $algo) {
            $hash = PasswordManager::encode($this->testPassword, $this->testSalt, $algo);
            
            // Correct password and salt should verify
            $this->assertTrue(PasswordManager::verify($this->testPassword, $hash, $this->testSalt, $algo));
            
            // Wrong password should fail
            $this->assertFalse(PasswordManager::verify('WrongPassword', $hash, $this->testSalt, $algo));
            
            // Wrong salt should fail
            $this->assertFalse(PasswordManager::verify($this->testPassword, $hash, 'wrong_salt', $algo));
            
            // Auto-detection should work
            $this->assertTrue(PasswordManager::verify($this->testPassword, $hash, $this->testSalt, null));
        }
    }

    /**
     * Test algorithm detection
     */
    public function testDetectHashAlgorithm()
    {
        // Test Argon2id detection
        $argonHash = PasswordManager::encode($this->testPassword, null, PasswordManager::ARGON2ID);
        $this->assertTrue(PasswordManager::verify($this->testPassword, $argonHash));
        
        // Test Bcrypt detection
        $bcryptHash = PasswordManager::encode($this->testPassword, null, PasswordManager::BCRYPT);
        $this->assertTrue(PasswordManager::verify($this->testPassword, $bcryptHash));
        
        // Test legacy algorithm detection by creating known hashes
        $md5Hash = hash('md5', $this->testPassword);
        $sha1Hash = hash('sha1', $this->testPassword);
        $sha256Hash = hash('sha256', $this->testPassword);
        
        $this->assertTrue(PasswordManager::verify($this->testPassword, $md5Hash, null, null));
        $this->assertTrue(PasswordManager::verify($this->testPassword, $sha1Hash, null, null));
        $this->assertTrue(PasswordManager::verify($this->testPassword, $sha256Hash, null, null));
    }

    /**
     * Test detection with unrecognized hash format
     */
    public function testDetectHashAlgorithmWithUnknownFormat()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No se pudo detectar el algoritmo del hash');
        
        PasswordManager::verify($this->testPassword, 'invalid_hash_format');
    }

    /**
     * Test needsRehash with Argon2id
     */
    public function testNeedsRehashArgon2id()
    {
        $hash = PasswordManager::encode($this->testPassword, null, PasswordManager::ARGON2ID);
        
        // Same options should not need rehash
        $this->assertFalse(PasswordManager::needsRehash($hash, PasswordManager::ARGON2ID));
        
        // Different options should need rehash
        $newOptions = ['memory_cost' => 131072]; // Higher memory cost
        $this->assertTrue(PasswordManager::needsRehash($hash, PasswordManager::ARGON2ID, $newOptions));
    }

    /**
     * Test needsRehash with Bcrypt
     */
    public function testNeedsRehashBcrypt()
    {
        $hash = PasswordManager::encode($this->testPassword, null, PasswordManager::BCRYPT);
        
        // Same options should not need rehash
        $this->assertFalse(PasswordManager::needsRehash($hash, PasswordManager::BCRYPT));
        
        // Different cost should need rehash
        $newOptions = ['cost' => 14]; // Higher cost
        $this->assertTrue(PasswordManager::needsRehash($hash, PasswordManager::BCRYPT, $newOptions));
    }

    /**
     * Test needsRehash with legacy algorithms
     */
    public function testNeedsRehashLegacy()
    {
        $sha256Hash = PasswordManager::encode($this->testPassword, $this->testSalt, PasswordManager::SHA256);
        $sha1Hash = PasswordManager::encode($this->testPassword, $this->testSalt, PasswordManager::SHA1);
        $md5Hash = PasswordManager::encode($this->testPassword, $this->testSalt, PasswordManager::MD5);
        
        // Legacy algorithms should always need rehash to secure algorithm
        $this->assertTrue(PasswordManager::needsRehash($sha256Hash));
        $this->assertTrue(PasswordManager::needsRehash($sha1Hash));
        $this->assertTrue(PasswordManager::needsRehash($md5Hash));
    }

    /**
     * Test createSalt (legacy function)
     */
    public function testCreateSalt()
    {
        $salt1 = PasswordManager::createSalt();
        $salt2 = PasswordManager::createSalt();
        
        // Salts should be different
        $this->assertNotEquals($salt1, $salt2);
        
        // Should be 64 characters (SHA256 hash)
        $this->assertEquals(64, strlen($salt1));
        $this->assertEquals(64, strlen($salt2));
        
        // Test with different algorithm
        $md5Salt = PasswordManager::createSalt('md5');
        $this->assertEquals(32, strlen($md5Salt));
    }

    /**
     * Test password security: empty passwords
     */
    public function testPasswordSecurity()
    {
        // Empty password should still generate hash
        $emptyHash = PasswordManager::encode('');
        $this->assertNotEmpty($emptyHash);
        $this->assertTrue(PasswordManager::verify('', $emptyHash));
        
        // Very long password should work
        $longPassword = str_repeat('A', 1000);
        $longHash = PasswordManager::encode($longPassword);
        $this->assertTrue(PasswordManager::verify($longPassword, $longHash));
        
        // Unicode password should work
        $unicodePassword = '测试密码🔐';
        $unicodeHash = PasswordManager::encode($unicodePassword);
        $this->assertTrue(PasswordManager::verify($unicodePassword, $unicodeHash));
    }

    /**
     * Test timing attack resistance
     */
    public function testTimingAttackResistance()
    {
        $hash = PasswordManager::encode($this->testPassword);
        
        $startTime = microtime(true);
        PasswordManager::verify($this->testPassword, $hash);
        $correctTime = microtime(true) - $startTime;
        
        $startTime = microtime(true);
        PasswordManager::verify('WrongPassword', $hash);
        $wrongTime = microtime(true) - $startTime;
        
        // Times should be similar (within reasonable variance)
        // This is a basic test - real timing attack testing would be more sophisticated
        $timeDiff = abs($correctTime - $wrongTime);
        $this->assertLessThan(0.01, $timeDiff, 'Timing difference too large, possible timing attack vulnerability');
    }

    /**
     * Integration test: password migration scenario
     */
    public function testPasswordMigrationScenario()
    {
        // Simulate old password hash
        $oldHash = PasswordManager::encode($this->testPassword, $this->testSalt, PasswordManager::SHA256);
        
        // Verify old hash works
        $this->assertTrue(PasswordManager::verify($this->testPassword, $oldHash, $this->testSalt, PasswordManager::SHA256));
        
        // Check if it needs rehashing
        $this->assertTrue(PasswordManager::needsRehash($oldHash));
        
        // Simulate migration to new hash
        $newHash = PasswordManager::encode($this->testPassword); // Default to Argon2id
        
        // New hash should work and not need rehashing
        $this->assertTrue(PasswordManager::verify($this->testPassword, $newHash));
        $this->assertFalse(PasswordManager::needsRehash($newHash));
        
        // Old and new hashes should be different
        $this->assertNotEquals($oldHash, $newHash);
    }
}