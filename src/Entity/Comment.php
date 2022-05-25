<?php


namespace Positron48\CommentExtension\Entity;


use Bolt\Entity\Content;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="comment")
 */
class Comment
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(type="string")
     */
    protected $authorName;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     * @ORM\Column(type="string")
     */
    protected $authorEmail;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(min = 3)
     * @ORM\Column(type="string")
     */
    protected $message;

    /**
     * @var Content
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Bolt\Entity\Content", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $content;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    /**
     * @param string $authorName
     */
    public function setAuthorName(string $authorName): void
    {
        $this->authorName = $authorName;
    }

    /**
     * @return string
     */
    public function getAuthorEmail(): string
    {
        return $this->authorEmail;
    }

    /**
     * @param string $authorEmail
     */
    public function setAuthorEmail(string $authorEmail): void
    {
        $this->authorEmail = $authorEmail;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return Content
     */
    public function getContent(): Content
    {
        return $this->content;
    }

    /**
     * @param Content $content
     */
    public function setContent(Content $content): void
    {
        $this->content = $content;
    }

    /**
     * @param int $size
     * @param string $imageset
     * @param string $rating
     * @return string
     */
    public function getGravatar(int $size = 80, string $imageset = 'mp', string $rating = 'g'): string
    {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $this->getAuthorEmail() ) ) );
        $url .= "?s=$size&d=$imageset&r=$rating";
        return $url;
    }


}