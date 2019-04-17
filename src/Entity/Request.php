<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RequestRepository")
 */
class Request
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $httpCode;

    /**
     * @ORM\Column(type="integer")
     */
    private $executedIn;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getHttpCode(): ?string
    {
        return $this->httpCode;
    }

    public function setHttpCode(string $httpCode): self
    {
        $this->httpCode = $httpCode;

        return $this;
    }

    public function getExecutedIn(): ?int
    {
        return $this->executedIn;
    }

    public function setExecutedIn(int $executedIn): self
    {
        $this->executedIn = $executedIn;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate($date): self
    {
        $this->date = $date;

        return $this;
    }
}
