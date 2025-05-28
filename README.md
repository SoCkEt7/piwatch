# π PIWatch - Surveillance Crypto PI Network

## 📋 Description

PIWatch est une application Symfony de surveillance en temps réel de la cryptomonnaie PI Network. Elle affiche les prix, statistiques de marché, actualités Twitter et analyse de sentiment.

## ✨ Fonctionnalités

- **Prix en temps réel** - API CoinGecko pour données crypto authentiques
- **Graphiques interactifs** - Historique des prix par période (1H, 24H, 7J, 1M, 1A)
- **Actualités Twitter** - Scraping des comptes officiels PI Team avec analyse de sentiment
- **Statistiques marché** - Market cap, volume, indicateurs techniques
- **Interface moderne** - Design dark responsive avec animations

## 🚀 Technologies

- **Backend**: Symfony 7, PHP 8.2
- **Frontend**: HTML5, CSS3, JavaScript vanilla
- **APIs**: CoinGecko API, Twitter/Nitter scraping
- **Base de données**: SQLite (via Symfony ORM)
- **Serveur**: Apache/Nginx + PHP-FPM

## 📊 Sources de données

### 🔸 Prix et marché (CoinGecko API)
- Prix PI/USD en temps réel
- Market cap et volume 24h
- Historique des prix
- Indicateurs techniques (RSI, MACD, Bollinger Bands)

### 🐦 Actualités (Twitter Scraping)
- Comptes officiels: @PiCoreTeam, @PiNetworkOfficial, @PIDevelopment
- Analyse de sentiment automatique
- Prédictions basées sur l'actualité

## 🛠️ Installation

### Prérequis
- PHP 8.2+
- Composer
- Apache/Nginx configuré

### Configuration
1. Cloner le projet
2. Installer les dépendances: `composer install`
3. Configurer la base de données dans `.env`
4. Exécuter les migrations: `php bin/console doctrine:migrations:migrate`

### Apache Configuration
```apache
# À ajouter dans votre virtualhost
Alias /piwatch /var/www/piwatch/public
<Directory /var/www/piwatch/public>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
    
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ /piwatch/index.php [QSA,L]
</Directory>
```

## 📡 API Endpoints

- `GET /api/pi/price` - Prix actuel PI/USD
- `GET /api/pi/stats` - Statistiques complètes de marché
- `GET /api/pi/history?period=24h` - Historique des prix
- `GET /api/twitter/tweets` - Actualités Twitter PI Team
- `GET /api/sentiment` - Analyse de sentiment
- `GET /api/alerts` - Alertes de mouvement de prix

## 🔐 Sécurité

- Authentification utilisateur requise pour le dashboard
- APIs publiques pour les données en temps réel
- Validation et sanitisation des entrées
- Headers de sécurité configurés

## 🎨 Interface

- **Design**: Dark theme avec gradients cyan/violet
- **Animations**: Particules flottantes, effets de glow
- **Responsive**: Adapté mobile et desktop
- **Temps réel**: Mise à jour automatique (30s prix, 2min news)

## 🔄 Fallbacks

Si les APIs externes sont indisponibles:
- Prix fixé sur dernière valeur connue ($0.74)
- Actualités PI Network réalistes
- Graphiques générés avec variations réalistes

## 📈 Métriques suivies

- Prix PI/USD
- Market cap et volume 24h
- Variations de prix 24h
- Sentiment communautaire
- Indicateurs techniques
- Nombre de mineurs actifs

## 👥 Comptes Twitter surveillés

- **@PiCoreTeam** - Équipe principale PI Network
- **@PiNetworkOfficial** - Canal officiel
- **@PIDevelopment** - Développements techniques
- **@PIFutures** - Annonces et alertes

## 🎯 Roadmap

- [ ] Alertes email/SMS personnalisées
- [ ] Dashboard admin pour configuration
- [ ] API GraphQL pour requêtes complexes
- [ ] Intégration d'autres exchanges
- [ ] Analyse technique avancée
- [ ] Portefeuille virtuel de suivi

## 📝 Licence

Propriétaire - © 2025 Antonin Nvh pour TalentAI

## 🔗 Liens

- **Demo**: https://antonin.talentai.fr/piwatch
- **TalentAI**: https://talentai.fr
- **PI Network**: https://minepi.com