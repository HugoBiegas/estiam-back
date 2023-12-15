<?php
namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\EmbeddedDocument]
class ComptoirDuLibreSoftware
{
    /**
     * @var array Les fournisseurs associés au logiciel
     */
    #[MongoDB\Field(type: "collection")]
    private $providers;

    /**
     * @var array Les utilisateurs associés au logiciel
     */
    #[MongoDB\Field(type: "collection")]
    private $users;

    public function getProviders(): ?array
    {
        return $this->providers ?? [];
    }

    public function setProviders(?array $providers): void
    {
        $this->providers = $providers;
    }
    // Getters and Setters for Users
    public function getUsers(): ?array
    {
        return $this->users ?? [];
    }
    public function setUsers(array $users): void
    {
        $this->users = $users;
    }
    public function toArray(): array
{
    return [
        'providers' => $this->getProviders(),
        'users' => $this->getUsers()
    ];
}

}
