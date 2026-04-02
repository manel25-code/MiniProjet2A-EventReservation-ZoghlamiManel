<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\WebauthnCredentialRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class PasskeyAuthService
{
    public function __construct(
        private WebauthnCredentialRepository $credRepo,
        private RequestStack $requestStack
    ) {}

    public function getRegistrationOptions(User $user): array
    {
        $challenge = bin2hex(random_bytes(32));
        $session = $this->requestStack->getSession();
        $session->set('webauthn_registration_challenge', $challenge);

        return [
            'challenge' => base64_encode($challenge),
            'rp' => [
                'name' => $_ENV['WEBAUTHN_RP_NAME'] ?? 'EventHub',
                'id' => $_ENV['APP_DOMAIN'] ?? 'localhost',
            ],
            'user' => [
                'id' => base64_encode($user->getId()->toBinary()),
                'name' => $user->getEmail(),
                'displayName' => $user->getUsername(),
            ],
            'pubKeyCredParams' => [
                ['alg' => -7,  'type' => 'public-key'],
                ['alg' => -257, 'type' => 'public-key'],
            ],
            'authenticatorSelection' => [
                'userVerification' => 'preferred',
                'residentKey' => 'preferred',
            ],
            'timeout' => 60000,
            'attestation' => 'none',
        ];
    }

    public function verifyRegistration(array $credential, User $user): void
    {
        // Vérification simplifiée pour le projet
        // En production, utiliser une vraie librairie WebAuthn
        $this->credRepo->saveCredentialData($user, $credential);
    }

    public function getLoginOptions(): array
    {
        $challenge = bin2hex(random_bytes(32));
        $session = $this->requestStack->getSession();
        $session->set('webauthn_login_challenge', $challenge);

        return [
            'challenge' => base64_encode($challenge),
            'rpId' => $_ENV['APP_DOMAIN'] ?? 'localhost',
            'timeout' => 60000,
            'userVerification' => 'preferred',
            'allowCredentials' => [],
        ];
    }

    public function verifyLogin(array $credential): User
    {
        $entity = $this->credRepo->findByCredentialId($credential['id']);
        if (!$entity) {
            throw new \RuntimeException('Credential non trouvé');
        }
        $entity->touch();
        return $entity->getUser();
    }
}