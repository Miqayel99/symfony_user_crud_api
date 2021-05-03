<?php

declare(strict_types=1);

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Security\Core\User\UserInterface;








/**
 * @ORM\Table(name="app_user")
 * @ORM\Entity()
 * @UniqueEntity(
 *      fields={"email"},
 *
 *      message="email is already in use"
 *      )
 *  @UniqueEntity(
 *      fields={"username"},
 *
 *      message="username is already in use"
 *      )
 *
 */
class User
{

    /**
     * @var int|null;
     *
     * @ORM\Column(name="id",type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
 private $id;
    /**
     * @var string|null;
     *
     * @ORM\Column(name="email",type="string", length=100)
     * @Assert\Email()
     *
     */
 private $email;
    /**
     * @var string|null;
     *
     * @ORM\Column(name="role",type="string" , length=100)
     *
     */

 private $role;
    /**
     * @var string|null;
     *
     * @ORM\Column(name="username",type="string" , length=50)
     */

    private $username;

    /**
     * @var string|null;
     *
     * @ORM\Column(name="password",type="string" , length=50)
     */

    private $password;


    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {


        $metadata->addPropertyConstraint('role', new Assert\Choice([
            'choices' => ['admin', 'user'],
            'message' => 'Choose between admin or user roles.',
        ]));
        $metadata->addPropertyConstraint('username', new Assert\Length([
            'min' => 4,
            'max' => 20,
            'minMessage' => 'Your username must be at least {{ limit }} characters long',
            'maxMessage' => 'Your username cannot be longer than {{ limit }} characters',
        ]));
        $metadata->addPropertyConstraint('email', new Assert\Email([
            'message' => 'The email "{{ value }}" is not a valid email.',
        ]));
            $metadata->addPropertyConstraint('username', new Assert\Regex([
                'pattern' => '/^(?![_.])[a-z0-9_.]+(?<![_.])+$/i',
                'message' => 'username cannot contain uppercase letters, whitespace, symbols except _ .  and cannot start or end with _ .',
            ]));
    }

    /**
     * @return int|null
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string|null $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

}