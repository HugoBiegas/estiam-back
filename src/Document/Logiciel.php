<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Description of Logiciel
 *
 * @author YourName
 */
#[MongoDB\Document]
class Logiciel
{
    /**
     * @var string|null L'identifiant MongoDB unique pour ce document
     */
    #[MongoDB\Id]
    protected $id;

    /**
     * @var string|null L'URL pour le logo du logiciel
     */
    #[MongoDB\Field(type: "string")]
    private $logoUrl;

    /**
     * @var string Le nom du logiciel
     */
    #[MongoDB\Field(type: "string")]
    private $name;

    /**
     * @var string La description du logiciel
     */
    #[MongoDB\Field(type: "string")]
    private $description;

    /**
     * @var array Les mots-clés associés au logiciel
     */
    #[MongoDB\Field(type: "collection")]
    private $keywords;

    /**
     * @var string La version minimale requise du logiciel
     */
    #[MongoDB\Field(type: "string")]
    private $versionMin;

    /**
     * @var array Le type de logiciel
     */
    #[MongoDB\Field(type: "hash")]
    private $softwareType;

    /**
     * @var bool Indique si le logiciel a un expert référent ou non
     */
    #[MongoDB\Field(type: "bool")]
    private $hasExpertReferent;

    /**
     * @var string La licence sous laquelle le logiciel est distribué
     */
    #[MongoDB\Field(type: "string")]
    private $license;

    /**
     * @var ComptoirDuLibreSoftware Les données de Comptoir du Libre pour ce logiciel
     */
    #[MongoDB\EmbedOne(targetDocument: ComptoirDuLibreSoftware::class)]
    private $comptoirDuLibreSoftware;

    // Getters and Setters for the Id
    public function getId(): ?string
    {
        return $this->id;
    }
    public function setId(?string $id): void
    {
        $this->id = $id;
    }
    // Getters and Setters for the Logo URL
    public function getLogoUrl(): ?string
    {
        return $this->logoUrl ?? '';
    }
    public function setLogoUrl(?string $logoUrl): void
    {
        $this->logoUrl = $logoUrl;
    }

    // Getters and Setters for the Name
    public function getName(): string
    {
        return $this->name ?? '';
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    // Getters and Setters for the Description
    public function getDescription(): string
    {
        return $this->description ?? '';
    }
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    // Getters and Setters for the Keywords
    public function getKeywords(): array
    {
        return $this->keywords ?? [];
    }
    public function setKeywords(array $keywords): void
    {
        $this->keywords = $keywords;
    }

    // Getters and Setters for the Version Minimum
    public function getVersionMin(): string
    {
        return $this->versionMin ?? '';
    }
    public function setVersionMin(string $versionMin): void
    {
        $this->versionMin = $versionMin;
    }

    // Getters and Setters for the Software Type
    public function getSoftwareType(): array
    {
        return $this->softwareType ?? [];
    }
    public function setSoftwareType(array $softwareType): void
    {
        $this->softwareType = $softwareType;
    }

    // Getters and Setters for Has Expert Referent
    public function getHasExpertReferent(): bool
    {
        return $this->hasExpertReferent ?? false;
    }
    public function setHasExpertReferent(bool $hasExpertReferent): void
    {
        $this->hasExpertReferent = $hasExpertReferent;
    }

    // Getters and Setters for License
    public function getLicense(): string
    {
        return $this->license ?? '';
    }
    public function setLicense(string $license): void
    {
        $this->license = $license;
    }

    public function getComptoirDuLibreSoftware(): ?ComptoirDuLibreSoftware
    {
        return $this->comptoirDuLibreSoftware;
    }

    public function setComptoirDuLibreSoftware(
        ?ComptoirDuLibreSoftware $comptoirDuLibreSoftware
    ): void {
        $this->comptoirDuLibreSoftware = $comptoirDuLibreSoftware;
    }

    public function toArray(): array
    {
        $array = [
            'id' => $this->getId(),
            'logoUrl' => $this->getLogoUrl(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'keywords' => $this->getKeywords(),
            'versionMin' => $this->getVersionMin(),
            'softwareType' => $this->getSoftwareType(),
            'hasExpertReferent' => $this->getHasExpertReferent(),
            'license' => $this->getLicense(),
        ];

        // Si ComptoirDuLibreSoftware a une méthode toArray(), l'utiliser ici
        if ($this->getComptoirDuLibreSoftware() !== null) {
            $array['comptoirDuLibreSoftware'] = $this->getComptoirDuLibreSoftware()->toArray();
        } else {
            $array['comptoirDuLibreSoftware'] = null;
        }

        return $array;
    }

}
