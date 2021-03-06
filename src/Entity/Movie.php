<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 128)]
    #[Assert\NotBlank(message: 'Name cannot be empty.')]
    #[Assert\Length(max: 128, maxMessage: 'Name cannot have more than 128 characters.')]
    private $name;

    #[ORM\Column(type: 'string', length: 2048)]
    #[Assert\NotBlank(message: 'Description cannot be empty.')]
    #[Assert\Length(max: 2048, maxMessage: 'Description cannot have more than 2048 characters.')]
    private $description;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: 'Released date cannot be empty.')]
    private $releasedAt;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Assert\Range(
        notInRangeMessage: 'Note has to be between 0 and 5.',
        invalidMessage: 'Wrong format, note has to be a number.',
        min: 0, max: 5
    )]
    private $note;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getReleasedAt(): ?DateTime
    {
        return $this->releasedAt;
    }

    /**
     * @throws Exception
     */
    public function setReleasedAt(string|DateTime $releasedAt): self
    {
        $this->releasedAt = $releasedAt instanceof DateTime ? $releasedAt : new DateTime($releasedAt);

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;

        return $this;
    }
}
