<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;


class UserTest extends TestCase
{
    public function testSetAndGetUsername()
    {
        // Arrange
        $user = new User();
        $expectedUsername = 'TestUser';

        // Act
        $user->setUsername($expectedUsername);
        $actualUsername = $user->getUsername();

        // Assert
        $this->assertEquals($expectedUsername, $actualUsername);
    }

    public function testSetAndGetPassword()
    {
        // Arrange
        $user = new User();
        $expectedPassword = 'TestPassword';

        // Act
        $user->setPassword($expectedPassword);
        $actualPassword = $user->getPassword();

        // Assert
        $this->assertEquals($expectedPassword, $actualPassword);
    }

    public function testSetAndGetRoles()
    {
        // Arrange
        $user = new User();
        $expectedRoles = ['ROLE_ADMIN'];

        // Act
        $user->setRoles($expectedRoles);
        $actualRoles = $user->getRoles();

        // Assert
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $actualRoles);
    }

    public function testGetSalt()
    {
        // Arrange
        $user = new User();

        // Act
        $salt = $user->getSalt();

        // Assert
        $this->assertNull($salt);
    }

    public function testEraseCredentials()
    {
        // Arrange
        $user = new User();

        // Act & Assert
        // Simply making sure no exception is thrown
        $this->assertNull($user->eraseCredentials());
    }

    public function testGetUserIdentifier()
    {
        // Arrange
        $user = new User();
        $expectedIdentifier = 'TestIdentifier';
        $user->setUsername($expectedIdentifier);

        // Act
        $actualIdentifier = $user->getUserIdentifier();

        // Assert
        $this->assertEquals($expectedIdentifier, $actualIdentifier);
    }
    public function testUserImplementsUserInterface()
    {
        // Arrange
        $user = new User();

        // Assert
        $this->assertInstanceOf(UserInterface::class, $user);
    }

    public function testCompleteEntity()
    {
        // Arrange
        $user = new User();
        $username = 'TestUser';
        $password = 'TestPassword';
        $roles = ['ROLE_ADMIN'];

        // Act
        $user->setUsername($username);
        $user->setPassword($password);
        $user->setRoles($roles);

        // Assert
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals(array_merge($roles, ['ROLE_USER']), $user->getRoles());
        $this->assertNull($user->getSalt());
        $this->assertNull($user->eraseCredentials());
        $this->assertEquals($username, $user->getUserIdentifier());
    }

}
