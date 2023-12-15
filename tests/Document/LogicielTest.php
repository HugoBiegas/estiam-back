<?php

namespace App\Tests\Document;

use App\Document\Logiciel;
use PHPUnit\Framework\TestCase;
use App\Document\ComptoirDuLibreSoftware;

class LogicielTest extends TestCase
{
    public function testCreateLogiciel()
    {
        // Créez une instance de Logiciel
        $logiciel = new Logiciel();

        // Vérifiez que l'instance a été créée correctement
        $this->assertInstanceOf(Logiciel::class, $logiciel);
    }

    public function testLogoUrl()
    {
        $logiciel = new Logiciel();
        $logiciel->setLogoUrl('https://example.com/logo.png');

        $this->assertEquals('https://example.com/logo.png', $logiciel->getLogoUrl());
    }

    public function testName()
    {
        $logiciel = new Logiciel();
        $logiciel->setName('Mon Logiciel');

        $this->assertEquals('Mon Logiciel', $logiciel->getName());
    }

    public function testDescription()
    {
        $logiciel = new Logiciel();
        $logiciel->setDescription('Description du logiciel');

        $this->assertEquals('Description du logiciel', $logiciel->getDescription());
    }

    public function testKeywords()
    {
        $logiciel = new Logiciel();
        $logiciel->setKeywords(['keyword1', 'keyword2']);

        $this->assertEquals(['keyword1', 'keyword2'], $logiciel->getKeywords());
    }

    public function testVersionMin()
    {
        $logiciel = new Logiciel();
        $logiciel->setVersionMin('1.0');

        $this->assertEquals('1.0', $logiciel->getVersionMin());
    }

    public function testSoftwareType()
    {
        $logiciel = new Logiciel();
        $logiciel->setSoftwareType(['type1' => 'Type 1']);

        $this->assertEquals(['type1' => 'Type 1'], $logiciel->getSoftwareType());
    }

    public function testHasExpertReferent()
    {
        $logiciel = new Logiciel();
        $logiciel->setHasExpertReferent(true);

        $this->assertTrue($logiciel->getHasExpertReferent());
    }

    public function testLicense()
    {
        $logiciel = new Logiciel();
        $logiciel->setLicense('MIT');

        $this->assertEquals('MIT', $logiciel->getLicense());
    }
    public function testComptoirDuLibreSoftware()
    {
        $logiciel = new Logiciel();
        $comptoirDuLibreSoftware = new ComptoirDuLibreSoftware();
        $comptoirDuLibreSoftware->setProviders(['provider1', 'provider2']);
        $comptoirDuLibreSoftware->setUsers(['user1', 'user2']);
        $logiciel->setComptoirDuLibreSoftware($comptoirDuLibreSoftware);

        $this->assertInstanceOf(ComptoirDuLibreSoftware::class, $logiciel->getComptoirDuLibreSoftware());
        $this->assertEquals(['provider1', 'provider2'], $logiciel->getComptoirDuLibreSoftware()->getProviders());
        $this->assertEquals(['user1', 'user2'], $logiciel->getComptoirDuLibreSoftware()->getUsers());
    }

}
