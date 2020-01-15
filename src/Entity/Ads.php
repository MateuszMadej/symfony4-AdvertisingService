<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdsRepository")
 */
class Ads
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modify_date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users", inversedBy="ads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AdsCategories", inversedBy="ads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getModifyDate(): ?\DateTimeInterface
    {
        return $this->modify_date;
    }

    public function setModifyDate(\DateTimeInterface $modify_date): self
    {
        $this->modify_date = $modify_date;

        return $this;
    }

    public function getUserId(): ?Users
    {
        return $this->user_id;
    }

    public function setUserId(?Users $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getCategoryId(): ?AdsCategories
    {
        return $this->category_id;
    }

    public function setCategoryId(?AdsCategories $category_id): self
    {
        $this->category_id = $category_id;

        return $this;
    }
}
