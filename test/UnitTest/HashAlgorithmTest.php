<?php

use PHPUnit\Framework\TestCase;
use GPDAuth\Library\HashAlgorithm;

class HashAlgorithmTest extends TestCase
{
    /**
     * Test que verifica que todos los algoritmos tienen los valores esperados
     */
    public function testEnumValues()
    {
        $this->assertEquals('argon2id', HashAlgorithm::Argon2id->value);
        $this->assertEquals('bcrypt', HashAlgorithm::Bcrypt->value);
        $this->assertEquals('sha256', HashAlgorithm::Sha256->value);
        $this->assertEquals('sha1', HashAlgorithm::Sha1->value);
        $this->assertEquals('md5', HashAlgorithm::Md5->value);
    }

    /**
     * Test de métodos de seguridad
     */
    public function testSecurityMethods()
    {
        // Algoritmos seguros
        $this->assertTrue(HashAlgorithm::Argon2id->isSecure());
        $this->assertFalse(HashAlgorithm::Argon2id->isLegacy());
        
        $this->assertTrue(HashAlgorithm::Bcrypt->isSecure());
        $this->assertFalse(HashAlgorithm::Bcrypt->isLegacy());
        
        // Algoritmos legacy
        $this->assertFalse(HashAlgorithm::Sha256->isSecure());
        $this->assertTrue(HashAlgorithm::Sha256->isLegacy());
        
        $this->assertFalse(HashAlgorithm::Sha1->isSecure());
        $this->assertTrue(HashAlgorithm::Sha1->isLegacy());
        
        $this->assertFalse(HashAlgorithm::Md5->isSecure());
        $this->assertTrue(HashAlgorithm::Md5->isLegacy());
    }

    /**
     * Test de métodos estáticos para obtener algoritmos por categoría
     */
    public function testGetAlgorithmsByCategory()
    {
        $secureAlgorithms = HashAlgorithm::getSecureAlgorithms();
        $this->assertCount(2, $secureAlgorithms);
        $this->assertContains(HashAlgorithm::Argon2id, $secureAlgorithms);
        $this->assertContains(HashAlgorithm::Bcrypt, $secureAlgorithms);

        $legacyAlgorithms = HashAlgorithm::getLegacyAlgorithms();
        $this->assertCount(3, $legacyAlgorithms);
        $this->assertContains(HashAlgorithm::Sha256, $legacyAlgorithms);
        $this->assertContains(HashAlgorithm::Sha1, $legacyAlgorithms);
        $this->assertContains(HashAlgorithm::Md5, $legacyAlgorithms);
    }

    /**
     * Test del método fromString con valores válidos
     */
    public function testFromStringValidValues()
    {
        $this->assertEquals(HashAlgorithm::Argon2id, HashAlgorithm::fromString('argon2id'));
        $this->assertEquals(HashAlgorithm::Bcrypt, HashAlgorithm::fromString('bcrypt'));
        $this->assertEquals(HashAlgorithm::Sha256, HashAlgorithm::fromString('sha256'));
        $this->assertEquals(HashAlgorithm::Sha1, HashAlgorithm::fromString('sha1'));
        $this->assertEquals(HashAlgorithm::Md5, HashAlgorithm::fromString('md5'));
        
        // Test case insensitive
        $this->assertEquals(HashAlgorithm::Argon2id, HashAlgorithm::fromString('ARGON2ID'));
        $this->assertEquals(HashAlgorithm::Bcrypt, HashAlgorithm::fromString('BCRYPT'));
        $this->assertEquals(HashAlgorithm::Sha256, HashAlgorithm::fromString('SHA256'));
    }

    /**
     * Test del método fromString con valor inválido
     */
    public function testFromStringInvalidValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Algoritmo de hash no soportado: invalid');
        
        HashAlgorithm::fromString('invalid');
    }

    /**
     * Test del algoritmo por defecto
     */
    public function testGetDefault()
    {
        $default = HashAlgorithm::getDefault();
        $this->assertEquals(HashAlgorithm::Argon2id, $default);
        $this->assertTrue($default->isSecure());
    }

    /**
     * Test de información de seguridad
     */
    public function testGetSecurityInfo()
    {
        // Argon2id
        $argonInfo = HashAlgorithm::Argon2id->getSecurityInfo();
        $this->assertEquals('high', $argonInfo['security_level']);
        $this->assertTrue($argonInfo['recommended']);
        $this->assertArrayHasKey('description', $argonInfo);

        // Bcrypt
        $bcryptInfo = HashAlgorithm::Bcrypt->getSecurityInfo();
        $this->assertEquals('high', $bcryptInfo['security_level']);
        $this->assertTrue($bcryptInfo['recommended']);

        // SHA256 (legacy)
        $sha256Info = HashAlgorithm::Sha256->getSecurityInfo();
        $this->assertEquals('low', $sha256Info['security_level']);
        $this->assertFalse($sha256Info['recommended']);

        // SHA1 (legacy)
        $sha1Info = HashAlgorithm::Sha1->getSecurityInfo();
        $this->assertEquals('very_low', $sha1Info['security_level']);
        $this->assertFalse($sha1Info['recommended']);

        // MD5 (legacy)
        $md5Info = HashAlgorithm::Md5->getSecurityInfo();
        $this->assertEquals('very_low', $md5Info['security_level']);
        $this->assertFalse($md5Info['recommended']);
    }

    /**
     * Test de compatibilidad con casos de uso reales
     */
    public function testRealWorldUsage()
    {
        // Verificar que todos los algoritmos pueden ser utilizados
        $algorithms = [
            HashAlgorithm::Argon2id,
            HashAlgorithm::Bcrypt,
            HashAlgorithm::Sha256,
            HashAlgorithm::Sha1,
            HashAlgorithm::Md5
        ];

        foreach ($algorithms as $algorithm) {
            // Verificar que el valor string es válido
            $this->assertIsString($algorithm->value);
            $this->assertNotEmpty($algorithm->value);
            
            // Verificar que fromString es el inverso
            $this->assertEquals($algorithm, HashAlgorithm::fromString($algorithm->value));
            
            // Verificar que getSecurityInfo funciona
            $securityInfo = $algorithm->getSecurityInfo();
            $this->assertIsArray($securityInfo);
            $this->assertArrayHasKey('security_level', $securityInfo);
            $this->assertArrayHasKey('recommended', $securityInfo);
            $this->assertArrayHasKey('description', $securityInfo);
        }
    }

    /**
     * Test de consistencia entre métodos
     */
    public function testMethodConsistency()
    {
        $allAlgorithms = [
            HashAlgorithm::Argon2id,
            HashAlgorithm::Bcrypt,
            HashAlgorithm::Sha256,
            HashAlgorithm::Sha1,
            HashAlgorithm::Md5
        ];

        $secureAlgorithms = HashAlgorithm::getSecureAlgorithms();
        $legacyAlgorithms = HashAlgorithm::getLegacyAlgorithms();

        // Verificar que no hay superposición (convertir a valores string para comparar)
        $secureValues = array_map(fn($algo) => $algo->value, $secureAlgorithms);
        $legacyValues = array_map(fn($algo) => $algo->value, $legacyAlgorithms);
        $this->assertEmpty(array_intersect($secureValues, $legacyValues));

        // Verificar que cubren todos los algoritmos
        $allFromMethods = array_merge($secureAlgorithms, $legacyAlgorithms);
        $this->assertCount(count($allAlgorithms), $allFromMethods);

        // Verificar consistencia de isSecure/isLegacy
        foreach ($allAlgorithms as $algorithm) {
            if ($algorithm->isSecure()) {
                $this->assertContains($algorithm, $secureAlgorithms);
                $this->assertNotContains($algorithm, $legacyAlgorithms);
            }
            if ($algorithm->isLegacy()) {
                $this->assertContains($algorithm, $legacyAlgorithms);
                $this->assertNotContains($algorithm, $secureAlgorithms);
            }
        }
    }
}