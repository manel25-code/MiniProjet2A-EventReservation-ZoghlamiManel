# EventHub — Application Web de Gestion de Réservations d'Événements

## Description du projet

EventHub est une application web complète développée dans le cadre du Mini Projet FIA2-GL à l'ISSAT Sousse.
Elle permet à des utilisateurs de consulter des événements et de réserver en ligne,
et à un administrateur de gérer les événements et les réservations via une interface sécurisée.

Le projet intègre une sécurité renforcée avec JWT (JSON Web Tokens) pour l'authentification
des API REST et Passkeys (WebAuthn/FIDO2) pour une authentification biométrique sans mot de passe.

---

## Technologies utilisées

- PHP 8.2
- Symfony 7.4
- MySQL 8.0
- Doctrine ORM
- Twig
- JWT — LexikJWTAuthenticationBundle
- Passkeys — web-auth/webauthn-lib 5.x
- Docker (PHP-FPM + Nginx + MySQL)
- GitHub

---

## Consignes d'installation

### Prérequis
- Docker Desktop installé
- Git installé

### Étapes

**1. Cloner le projet**
```bash
git clone https://github.com/manel25-code/MiniProjet2A-EventReservation-ZoghlamiManel.git
cd MiniProjet2A-EventReservation-ZoghlamiManel
```

**2. Configurer l'environnement**

Modifier le fichier `.env` :
```env
DATABASE_URL="mysql://user:password@db:3306/event_reservation"
JWT_PASSPHRASE=votre_passphrase_secrete
APP_DOMAIN=localhost
MAILER_DSN=gmail://email@gmail.com:motdepasseapp@default
```

**3. Lancer Docker**
```bash
docker-compose up -d
```

**4. Installer les dépendances**
```bash
docker exec -it event_php bash
composer install
```

**5. Générer les clés JWT**
```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
chmod 600 config/jwt/private.pem config/jwt/public.pem
```

**6. Créer la base de données**
```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

**7. Accéder à l'application**

| http://localhost:8080 | Page d'accueil |
| http://localhost:8080/register | Inscription utilisateur |
| http://localhost:8080/login | Connexion utilisateur |
| http://localhost:8080/admin/login | Connexion administrateur |

**Comptes de test**
Administrateur : username = admin / password = admin123
Utilisateur    : créer un compte sur /register

## Identités des membres de l'équipe

| Manel Zoghlami | FIA2-GL | zoghlamimanel13@gmail.com |

---

