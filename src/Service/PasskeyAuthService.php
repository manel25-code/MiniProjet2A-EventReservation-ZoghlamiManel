<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\WebauthnCredentialRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\Server;

class PasskeyAuthService
{
    public function __construct(
        private Server $webauthnServer,
        private SessionInterface $session,
        private WebauthnCredentialRepository $credRepo
    ) {}

    /**
     * Génère les options pour l'enregistrement d'une passkey
     */
    public function getRegistrationOptions(User $user): array
    {
        $userEntity = new PublicKeyCredentialUserEntity(
            $user->getEmail(),
            $user->getId()->toBinary(),
            $user->getEmail()
        );

        $options = $this->webauthnServer->generatePublicKeyCredentialCreationOptions(
            $userEntity,
            authenticatorSelection: null,
            excludeCredentials: $this->getExcludedCredentials($user)
        );

        $this->session->set('webauthn_registration', $options);
        return $options->jsonSerialize();
    }

    /**
     * Valide l'enregistrement et lie la passkey à l'utilisateur
     */
    public function verifyRegistration(string $response, User $user): void
    {
        $options = $this->session->get('webauthn_registration');
        $userEntity = new PublicKeyCredentialUserEntity(
            $user->getEmail(),
            $user->getId()->toBinary(),
            $user->getEmail()
        );

        $credential = $this->webauthnServer->loadAndCheckAttestationResponse(
            $response, $options, $userEntity
        );

        $this->credRepo->saveCredential($user, $credential);
        $this->session->remove('webauthn_registration');
    }

    /**
     * Génère les options pour la connexion par passkey
     */
    public function getLoginOptions(): array
    {
        $options = $this->webauthnServer->generatePublicKeyCredentialRequestOptions();
        $this->session->set('webauthn_login', $options);
        return $options->jsonSerialize();
    }

    /**
     * Valide la connexion et retourne l'utilisateur authentifié
     */
    public function verifyLogin(string $response): User
    {
        $options = $this->session->get('webauthn_login');

        $credential = $this->webauthnServer->loadAndCheckAssertionResponse(
            $response, $options
        );

        $entity = $this->credRepo->findByCredentialId(
            $credential->getPublicKeyCredentialId()
        );

        $entity->touch();
        $this->session->remove('webauthn_login');

        return $entity->getUser();
    }

    private function getExcludedCredentials(User $user): array
    {
        return [];
    }
}