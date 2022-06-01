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
    public const STATUS_NEW = 'new';
    public const STATUS_PUBLICHED = 'published';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_UNPUBLISHED = 'unpublished';

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
     * @var string
     * @ORM\Column(type="string", length=191, nullable=true)
     */
    private $status;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modifiedAt = null;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishedAt = null;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $depublishedAt = null;

    /**
     * @var Comment|null
     *
     * @ORM\ManyToOne(targetEntity="Positron48\CommentExtension\Entity\Comment", fetch="EAGER")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $comment;

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

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getModifiedAt(): ?\DateTime
    {
        return $this->modifiedAt;
    }

    /**
     * @param \DateTime|null $modifiedAt
     */
    public function setModifiedAt(?\DateTime $modifiedAt): void
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param \DateTime|null $publishedAt
     */
    public function setPublishedAt(?\DateTime $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getDepublishedAt(): ?\DateTime
    {
        return $this->depublishedAt;
    }

    /**
     * @param \DateTime|null $depublishedAt
     */
    public function setDepublishedAt(?\DateTime $depublishedAt): void
    {
        $this->depublishedAt = $depublishedAt;
    }

    /**
     * @return Comment|null
     */
    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    /**
     * @param Comment|null $comment
     */
    public function setComment(?Comment $comment): void
    {
        $this->comment = $comment;
    }


}