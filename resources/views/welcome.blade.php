<x-guest-layout>
    <div class="welcome-wrapper">
        <!-- Header Navigation -->
        <header class="welcome-header-nav">
            <div class="welcome-header-container">
                <div class="welcome-header-brand">
                    <div class="welcome-logo">
                        <img src="{{ asset('images/logos/logo_wmc_orange.png') }}" alt="Logo WMC"
                            class="welcome-logo-img">
                    </div>

                </div>
                <nav class="welcome-nav">
                    <a href="#fonctionnalites" class="welcome-nav-link">Fonctionnalités</a>
                    <a href="#temoignages" class="welcome-nav-link">Témoignages</a>
                </nav>
                <a href="{{ route('login') }}" class="welcome-header-btn">Connexion</a>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="welcome-hero">
            <div class="welcome-hero-container">
                <!-- New Feature Badge -->
                <div class="welcome-badge">
                    <span class="welcome-badge-dot"></span>
                    <span class="welcome-badge-text">NOUVEAU : GESTION INTELLIGENTE DE CAISSE</span>
                </div>

                <!-- Main Headline -->
                <h2 class="welcome-hero-title">
                    Gérez votre caisse
                    <span class="welcome-hero-title-accent">sans limites.</span>
                </h2>

                <!-- Description -->
                <p class="welcome-hero-description">
                    La plateforme unique pour unifier la gestion de caisse, les ventes, le stock et les rapports.
                    Optimisez vos processus et prenez des décisions éclairées grâce à WMC.
                </p>

                <!-- CTA Buttons -->
                <div class="welcome-hero-cta">
                    <a href="{{ route('login') }}" class="welcome-cta-btn welcome-cta-primary">
                        Démarrer maintenant
                    </a>
                    <button class="welcome-cta-btn welcome-cta-secondary">
                        <i class="fas fa-play-circle"></i>
                        Voir la vidéo
                    </button>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="fonctionnalites" class="welcome-features-section">
            <div class="welcome-features-container">
                <h3 class="welcome-features-title">Tout ce dont vous avez besoin. Rien de superflu.</h3>
                <p class="welcome-features-subtitle">
                    Une suite complète de modules interconnectés pour gérer chaque aspect de votre activité depuis une
                    interface unique.
                </p>

                <div class="welcome-features-grid">
                    <!-- Feature Card 1 -->
                    <div class="welcome-feature-card">
                        <div class="welcome-feature-icon welcome-feature-icon-orange">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <h4 class="welcome-feature-card-title">Suivi de caisse</h4>
                        <p class="welcome-feature-card-description">
                            Suivez vos transactions du premier paiement à la clôture. Gestion de caisse en temps réel.
                        </p>
                    </div>

                    <!-- Feature Card 2 -->
                    <div class="welcome-feature-card">
                        <div class="welcome-feature-icon welcome-feature-icon-orange">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <h4 class="welcome-feature-card-title">Ventes & Factures</h4>
                        <p class="welcome-feature-card-description">
                            Point de vente, factures et suivi de trésorerie en temps réel.
                        </p>
                    </div>

                    <!-- Feature Card 3 -->
                    <div class="welcome-feature-card">
                        <div class="welcome-feature-icon welcome-feature-icon-orange">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h4 class="welcome-feature-card-title">Gestion de stock</h4>
                        <p class="welcome-feature-card-description">
                            Contrôle précis de vos inventaires et alertes de stock bas.
                        </p>
                    </div>

                    <!-- Feature Card 4 -->
                    <div class="welcome-feature-card">
                        <div class="welcome-feature-icon welcome-feature-icon-orange">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="welcome-feature-card-title">Rapports & Analyses</h4>
                        <p class="welcome-feature-card-description">
                            Analysez vos performances avec des rapports détaillés et des statistiques.
                        </p>
                    </div>

                    <!-- Feature Card 5 -->
                    <div class="welcome-feature-card">
                        <div class="welcome-feature-icon welcome-feature-icon-orange">
                            <i class="fas fa-history"></i>
                        </div>
                        <h4 class="welcome-feature-card-title">Historique complet</h4>
                        <p class="welcome-feature-card-description">
                            Consultez l'historique détaillé de toutes vos opérations et transactions.
                        </p>
                    </div>

                    <!-- Feature Card 6 -->
                    <div class="welcome-feature-card">
                        <div class="welcome-feature-icon welcome-feature-icon-orange">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="welcome-feature-card-title">Sécurité renforcée</h4>
                        <p class="welcome-feature-card-description">
                            Protégez vos données avec un système sécurisé et des contrôles d'accès.
                        </p>
                    </div>

                    <!-- Feature Card 7 -->
                    <div class="welcome-feature-card">
                        <div class="welcome-feature-icon welcome-feature-icon-orange">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h4 class="welcome-feature-card-title">Paiements partiels</h4>
                        <p class="welcome-feature-card-description">
                            Permettez à vos clients de commander un article et de payer partiellement, puis de solder le reste plus tard. Gestion flexible des acomptes et soldes.
                        </p>
                    </div>

                    <!-- Feature Card 8 -->
                    <div class="welcome-feature-card">
                        <div class="welcome-feature-icon welcome-feature-icon-orange">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4 class="welcome-feature-card-title">Paiements multiples</h4>
                        <p class="welcome-feature-card-description">
                            Acceptez plusieurs modes de paiement pour une même facture : espèces, mobile money (Moov, MTN, Orange), carte bancaire. Flexibilité totale pour vos clients.
                        </p>
                    </div>

                    <!-- Feature Card 9 -->
                    <div class="welcome-feature-card">
                        <div class="welcome-feature-icon welcome-feature-icon-orange">
                            <i class="fas fa-store"></i>
                        </div>
                        <h4 class="welcome-feature-card-title">Application multiboutique</h4>
                        <p class="welcome-feature-card-description">
                            Gérez plusieurs boutiques depuis un seul compte. Un client propriétaire de plusieurs boutiques peut toutes les administrer avec un seul accès. Vue centralisée et gestion simplifiée.
                        </p>
                    </div>

                    <!-- Feature Card 10 -->
                    <div class="welcome-feature-card">
                        <div class="welcome-feature-icon welcome-feature-icon-orange">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h4 class="welcome-feature-card-title">Personnalisation complète</h4>
                        <p class="welcome-feature-card-description">
                            Personnalisez l'application comme si elle vous appartenait. Ajoutez le logo de votre boutique, choisissez le thème de couleur de l'application selon vos préférences. Faites de WMC votre propre outil de gestion.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="temoignages" class="welcome-testimonials-section">
            <div class="welcome-testimonials-container">
                <div class="welcome-testimonials-header">
                    <h3 class="welcome-testimonials-title">Ils nous font confiance</h3>
                    <p class="welcome-testimonials-subtitle">
                        Découvrez ce que nos clients disent de WMC et comment notre solution transforme leur gestion quotidienne.
                    </p>
                </div>

                <div class="welcome-testimonials-grid">
                    <!-- Testimonial 1 -->
                    <div class="welcome-testimonial-card">
                        <div class="welcome-testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="welcome-testimonial-text">
                            "WMC a complètement transformé la gestion de ma boutique. Le suivi de caisse en temps réel et les rapports détaillés m'ont permis d'augmenter mes ventes de 30% en seulement 3 mois. L'interface est intuitive et facile à utiliser."
                        </p>
                        <div class="welcome-testimonial-author">
                            <div class="welcome-testimonial-avatar">
                                <span>AK</span>
                            </div>
                            <div class="welcome-testimonial-info">
                                <div class="welcome-testimonial-name">Amara Kouassi</div>
                                <div class="welcome-testimonial-role">Propriétaire, Boutique Mode Abidjan</div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="welcome-testimonial-card">
                        <div class="welcome-testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="welcome-testimonial-text">
                            "La gestion de stock était un vrai casse-tête avant WMC. Maintenant, je reçois des alertes automatiques quand mes produits sont en rupture. Ça m'a fait gagner un temps précieux et réduit mes pertes. Je recommande à 100% !"
                        </p>
                        <div class="welcome-testimonial-author">
                            <div class="welcome-testimonial-avatar">
                                <span>FT</span>
                            </div>
                            <div class="welcome-testimonial-info">
                                <div class="welcome-testimonial-name">Fatou Traoré</div>
                                <div class="welcome-testimonial-role">Gérante, Supermarché Cocody</div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="welcome-testimonial-card">
                        <div class="welcome-testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="welcome-testimonial-text">
                            "Le point de vente est rapide et efficace. Mes clients apprécient la rapidité de traitement. Les factures sont professionnelles et le suivi des ventes me permet de mieux comprendre mes clients. Excellent outil pour les commerces en Côte d'Ivoire."
                        </p>
                        <div class="welcome-testimonial-author">
                            <div class="welcome-testimonial-avatar">
                                <span>YK</span>
                            </div>
                            <div class="welcome-testimonial-info">
                                <div class="welcome-testimonial-name">Yacouba Koné</div>
                                <div class="welcome-testimonial-role">Directeur, Électronique Yopougon</div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 4 -->
                    <div class="welcome-testimonial-card">
                        <div class="welcome-testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="welcome-testimonial-text">
                            "Les rapports et analyses sont d'une précision remarquable. Je peux maintenant prendre des décisions éclairées basées sur des données réelles. La gestion de caisse est transparente et sécurisée. WMC est devenu indispensable à mon activité."
                        </p>
                        <div class="welcome-testimonial-author">
                            <div class="welcome-testimonial-avatar">
                                <span>MD</span>
                            </div>
                            <div class="welcome-testimonial-info">
                                <div class="welcome-testimonial-name">Mariam Diallo</div>
                                <div class="welcome-testimonial-role">Propriétaire, Pharmacie Plateau</div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 5 -->
                    <div class="welcome-testimonial-card">
                        <div class="welcome-testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="welcome-testimonial-text">
                            "J'utilise WMC depuis 6 mois et je ne peux plus m'en passer. La simplicité d'utilisation est remarquable. Mes employés ont appris à l'utiliser en quelques heures seulement. Le support client est également très réactif."
                        </p>
                        <div class="welcome-testimonial-author">
                            <div class="welcome-testimonial-avatar">
                                <span>BS</span>
                            </div>
                            <div class="welcome-testimonial-info">
                                <div class="welcome-testimonial-name">Bakary Sanogo</div>
                                <div class="welcome-testimonial-role">Gérant, Magasin Général Adjamé</div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 6 -->
                    <div class="welcome-testimonial-card">
                        <div class="welcome-testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="welcome-testimonial-text">
                            "La fonctionnalité de gestion des dépenses m'a permis de mieux contrôler mes coûts. Je peux suivre chaque dépense et analyser mes marges en temps réel. WMC m'aide à optimiser ma rentabilité. C'est un investissement qui se rentabilise rapidement."
                        </p>
                        <div class="welcome-testimonial-author">
                            <div class="welcome-testimonial-avatar">
                                <span>KT</span>
                            </div>
                            <div class="welcome-testimonial-info">
                                <div class="welcome-testimonial-name">Koffi Tano</div>
                                <div class="welcome-testimonial-role">Propriétaire, Restaurant Treichville</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA Section -->
        <section class="welcome-final-cta">
            <div class="welcome-final-cta-container">
                <h3 class="welcome-final-cta-title">Prêt à transformer votre gestion de caisse ?</h3>
                <a href="{{ route('login') }}" class="welcome-cta-btn welcome-cta-primary welcome-cta-large">
                    Se connecter
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </section>
    </div>
</x-guest-layout>
