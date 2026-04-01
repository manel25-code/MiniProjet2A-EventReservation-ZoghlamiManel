<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Webauthn\PublicKeyCredentialSource;

#[ORM\Entity]
#[ORM\Table(name: 'webauthn_credential')]
class WebauthnCredential
{
#[ORM\Id]
#[ORM\Column(type: 'uuid')]
private Uuid $id;

#[ORM\ManyToOne(inversedBy: 'webauthnCredentials')]
#[ORM\JoinColumn(nullable: false)]
 private User $user;

 #[ORM\Column(type: 'text')]
 private string $credentialData; 
 #[ORM\Column(length: 255)]
 private string $name; 

 #[ORM\Column]
 private \DateTimeImmutable $createdAt;

 #[ORM\Column]
 private \DateTimeImmutable $lastUsedAt;

 public function getCredentialSource(): PublicKeyCredentialSource
 {
 $data = json_decode($this->credentialData, true);
 return PublicKeyCredentialSource::createFromArray($data);
 }
 public function setCredentialSource(PublicKeyCredentialSource
$source): void
 {
 $this->credentialData = json_encode($source);
 }

}