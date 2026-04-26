@extends('layouts.app')

@section('page-title', 'Modifier la boutique')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-store me-2"></i>
                            Modifier la boutique : {{ $boutique->nom }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('boutiques.update', $boutique) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nom" class="form-label">Nom de la boutique *</label>
                                        <input type="text" class="form-control @error('nom') is-invalid @enderror"
                                            id="nom" name="nom" value="{{ old('nom', $boutique->nom) }}" required>
                                        @error('nom')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                            rows="3">{{ old('description', $boutique->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="adresse" class="form-label">Adresse *</label>
                                        <textarea class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" rows="2"
                                            required>{{ old('adresse', $boutique->adresse) }}</textarea>
                                        @error('adresse')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telephone" class="form-label">Téléphone *</label>
                                        <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                            id="telephone" name="telephone"
                                            value="{{ old('telephone', $boutique->telephone) }}" required>
                                        @error('telephone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', $boutique->email) }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="devise" class="form-label">Devise *</label>
                                        <select class="form-select @error('devise') is-invalid @enderror" id="devise"
                                            name="devise" required>
                                            <option value="FCFA"
                                                {{ old('devise', $boutique->devise) == 'FCFA' ? 'selected' : '' }}>FCFA
                                            </option>
                                            <option value="EUR"
                                                {{ old('devise', $boutique->devise) == 'EUR' ? 'selected' : '' }}>EUR
                                            </option>
                                            <option value="USD"
                                                {{ old('devise', $boutique->devise) == 'USD' ? 'selected' : '' }}>USD
                                            </option>
                                            <option value="XOF"
                                                {{ old('devise', $boutique->devise) == 'XOF' ? 'selected' : '' }}>XOF
                                            </option>
                                        </select>
                                        @error('devise')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="theme_color" class="form-label">Couleur du thème</label>
                                        <div class="theme-color-selector">
                                            <div class="row g-2">
                                                <div class="col-6 col-md-3">
                                                    <label class="theme-option">
                                                        <input type="radio" name="theme_color" value="default"
                                                            {{ old('theme_color', $boutique->theme_color ?? 'default') == 'default' ? 'checked' : '' }}>
                                                        <div class="theme-preview theme-default">
                                                            <div class="theme-color-box" style="background: #475569;"></div>
                                                            <span class="theme-label">Par défaut</span>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="theme-option">
                                                        <input type="radio" name="theme_color" value="blue"
                                                            {{ old('theme_color', $boutique->theme_color ?? 'default') == 'blue' ? 'checked' : '' }}>
                                                        <div class="theme-preview theme-blue">
                                                            <div class="theme-color-box" style="background: #2563eb;"></div>
                                                            <span class="theme-label">Bleu</span>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="theme-option">
                                                        <input type="radio" name="theme_color" value="green"
                                                            {{ old('theme_color', $boutique->theme_color ?? 'default') == 'green' ? 'checked' : '' }}>
                                                        <div class="theme-preview theme-green">
                                                            <div class="theme-color-box" style="background: #059669;"></div>
                                                            <span class="theme-label">Vert</span>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="theme-option">
                                                        <input type="radio" name="theme_color" value="purple"
                                                            {{ old('theme_color', $boutique->theme_color ?? 'default') == 'purple' ? 'checked' : '' }}>
                                                        <div class="theme-preview theme-purple">
                                                            <div class="theme-color-box" style="background: #7c3aed;">
                                                            </div>
                                                            <span class="theme-label">Violet</span>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="theme-option">
                                                        <input type="radio" name="theme_color" value="orange"
                                                            {{ old('theme_color', $boutique->theme_color ?? 'default') == 'orange' ? 'checked' : '' }}>
                                                        <div class="theme-preview theme-orange">
                                                            <div class="theme-color-box" style="background: #ea580c;">
                                                            </div>
                                                            <span class="theme-label">Orange</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        @error('theme_color')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Choisissez une couleur de thème pour personnaliser l'apparence de votre boutique
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="logo" class="form-label">Logo de la boutique</label>
                                        <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                            id="logo" name="logo" accept="image/*">
                                        @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Formats acceptés : JPEG, PNG, JPG, GIF, SVG. Taille max : 2MB
                                        </div>
                                    </div>

                                    <!-- Aperçu du logo actuel -->
                                    @if ($boutique->logo)
                                        <div class="mb-3">
                                            <label class="form-label">Logo actuel</label>
                                            <div class="logo-preview">
                                                <img src="{{ $boutique->logo }}" alt="Logo actuel" class="img-thumbnail"
                                                    style="max-width: 200px; max-height: 100px;">
                                            </div>
                                        </div>
                                    @endif

                                    <hr>
                                    <h6 class="fw-semibold mb-3">Images de la page caisse (POS)</h6>

                                    <div class="mb-3">
                                        <label for="shared_hero_image" class="form-label">Image commune (onglets concernés)</label>
                                        <input type="file"
                                            class="form-control @error('shared_hero_image') is-invalid @enderror"
                                            id="shared_hero_image" name="shared_hero_image" accept="image/*">
                                        @error('shared_hero_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Si renseignée, cette image sera appliquée identiquement sur tableau de bord, point de vente, produits/articles et stock.</div>
                                    </div>
                                    @if ($boutique->pos_banner_image)
                                        <div class="mb-3">
                                            <label class="form-label">Image commune actuelle</label>
                                            <div>
                                                <img src="{{ asset('images/pos/' . basename($boutique->pos_banner_image)) }}"
                                                    alt="Image commune actuelle" class="img-thumbnail"
                                                    style="max-width: 100%; max-height: 180px;">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Configuration Email -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card" style="border: 1px solid var(--border-light);">
                                        <div class="card-header" style="background-color: var(--bg-primary); border-bottom: 1px solid var(--border-light);">
                                            <h6 class="mb-0" style="color: var(--text-primary);">
                                                <i class="fas fa-envelope me-2" style="color: var(--primary-color);"></i>
                                                Configuration Email
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Configuration Email :</strong> Configurez les paramètres email pour
                                                envoyer les factures aux clients.
                                                Si non configuré, les paramètres par défaut du système (fichier .env) seront
                                                utilisés.
                                                <br><small class="mt-2 d-block">
                                                    <strong>Note :</strong> Pour Gmail, vous devez activer "Accès moins
                                                    sécurisé" ou utiliser un "Mot de passe d'application"
                                                    dans les paramètres de votre compte Google.
                                                </small>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="mail_mailer" class="form-label">Type de mailer</label>
                                                        <select
                                                            class="form-select @error('mail_mailer') is-invalid @enderror"
                                                            id="mail_mailer" name="mail_mailer">
                                                            <option value="">Utiliser les paramètres par défaut
                                                            </option>
                                                            <option value="smtp"
                                                                {{ old('mail_mailer', $boutique->mail_mailer) == 'smtp' ? 'selected' : '' }}>
                                                                SMTP</option>
                                                            <option value="sendmail"
                                                                {{ old('mail_mailer', $boutique->mail_mailer) == 'sendmail' ? 'selected' : '' }}>
                                                                Sendmail</option>
                                                            <option value="mailgun"
                                                                {{ old('mail_mailer', $boutique->mail_mailer) == 'mailgun' ? 'selected' : '' }}>
                                                                Mailgun</option>
                                                            <option value="ses"
                                                                {{ old('mail_mailer', $boutique->mail_mailer) == 'ses' ? 'selected' : '' }}>
                                                                Amazon SES</option>
                                                            <option value="postmark"
                                                                {{ old('mail_mailer', $boutique->mail_mailer) == 'postmark' ? 'selected' : '' }}>
                                                                Postmark</option>
                                                        </select>
                                                        @error('mail_mailer')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="mail_host" class="form-label">Serveur SMTP
                                                            (Host)</label>
                                                        <input type="text"
                                                            class="form-control @error('mail_host') is-invalid @enderror"
                                                            id="mail_host" name="mail_host"
                                                            value="{{ old('mail_host', $boutique->mail_host) }}"
                                                            placeholder="smtp.gmail.com">
                                                        @error('mail_host')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <div class="form-text">Exemple: smtp.gmail.com, smtp.mailtrap.io
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="mail_port" class="form-label">Port SMTP</label>
                                                        <input type="number"
                                                            class="form-control @error('mail_port') is-invalid @enderror"
                                                            id="mail_port" name="mail_port"
                                                            value="{{ old('mail_port', $boutique->mail_port ?? 587) }}"
                                                            placeholder="587" min="1" max="65535">
                                                        @error('mail_port')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <div class="form-text">Ports courants: 587 (TLS), 465 (SSL), 25
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="mail_encryption"
                                                            class="form-label">Chiffrement</label>
                                                        <select
                                                            class="form-select @error('mail_encryption') is-invalid @enderror"
                                                            id="mail_encryption" name="mail_encryption">
                                                            <option value="">Aucun</option>
                                                            <option value="tls"
                                                                {{ old('mail_encryption', $boutique->mail_encryption ?? 'tls') == 'tls' ? 'selected' : '' }}>
                                                                TLS</option>
                                                            <option value="ssl"
                                                                {{ old('mail_encryption', $boutique->mail_encryption) == 'ssl' ? 'selected' : '' }}>
                                                                SSL</option>
                                                        </select>
                                                        @error('mail_encryption')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="mail_username" class="form-label">Nom d'utilisateur /
                                                            Email</label>
                                                        <input type="text"
                                                            class="form-control @error('mail_username') is-invalid @enderror"
                                                            id="mail_username" name="mail_username"
                                                            value="{{ old('mail_username', $boutique->mail_username) }}"
                                                            placeholder="votre-email@gmail.com">
                                                        @error('mail_username')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="mail_password" class="form-label">Mot de passe</label>
                                                        <input type="password"
                                                            class="form-control @error('mail_password') is-invalid @enderror"
                                                            id="mail_password" name="mail_password"
                                                            value="{{ old('mail_password', $boutique->mail_password) }}"
                                                            placeholder="Votre mot de passe email">
                                                        @error('mail_password')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <div class="form-text">
                                                            <small>Pour Gmail, utilisez un "Mot de passe d'application" au
                                                                lieu de votre mot de passe habituel.</small>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="mail_from_address" class="form-label">Adresse email
                                                            expéditeur</label>
                                                        <input type="email"
                                                            class="form-control @error('mail_from_address') is-invalid @enderror"
                                                            id="mail_from_address" name="mail_from_address"
                                                            value="{{ old('mail_from_address', $boutique->mail_from_address ?? $boutique->email) }}"
                                                            placeholder="noreply@votreboutique.com">
                                                        @error('mail_from_address')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <div class="form-text">L'adresse email qui apparaîtra comme
                                                            expéditeur</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="mail_from_name" class="form-label">Nom de
                                                            l'expéditeur</label>
                                                        <input type="text"
                                                            class="form-control @error('mail_from_name') is-invalid @enderror"
                                                            id="mail_from_name" name="mail_from_name"
                                                            value="{{ old('mail_from_name', $boutique->mail_from_name ?? $boutique->nom) }}"
                                                            placeholder="{{ $boutique->nom }}">
                                                        @error('mail_from_name')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <div class="form-text">Le nom qui apparaîtra comme expéditeur</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <button type="button" class="btn btn-outline-info btn-sm"
                                                            onclick="testEmailConfig()">
                                                            <i class="fas fa-paper-plane me-1"></i>
                                                            Tester la configuration
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                <a href="{{ route('boutiques.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Retour
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Mettre à jour
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .theme-color-selector {
            margin-top: 0.5rem;
        }

        .theme-option {
            cursor: pointer;
            display: block;
        }

        .theme-option input[type="radio"] {
            display: none;
        }

        .theme-preview {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            transition: all 0.3s ease;
            background: #fff;
        }

        .theme-preview:hover {
            border-color: #94a3b8;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .theme-option input[type="radio"]:checked+.theme-preview {
            border-color: var(--primary-color, #475569);
            border-width: 3px;
            box-shadow: 0 0 0 3px rgba(71, 85, 105, 0.1);
        }

        .theme-color-box {
            width: 100%;
            height: 50px;
            border-radius: 6px;
            margin-bottom: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .theme-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary, #0f172a);
        }
    </style>

    <script>
        // Aperçu du logo en temps réel
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Supprimer l'ancien aperçu s'il existe
                    const oldPreview = document.querySelector('.logo-preview-new');
                    if (oldPreview) {
                        oldPreview.remove();
                    }

                    // Créer le nouvel aperçu
                    const preview = document.createElement('div');
                    preview.className = 'logo-preview-new mt-2';
                    preview.innerHTML = '<label class="form-label">Nouveau logo</label><br><img src="' + e
                        .target.result +
                        '" class="img-thumbnail" style="max-width: 200px; max-height: 100px;">';

                    // Insérer après le champ logo
                    document.getElementById('logo').parentNode.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        });

        // Fonction pour tester la configuration email
        function testEmailConfig() {
            const mailHost = document.getElementById('mail_host').value;
            const mailPort = document.getElementById('mail_port').value;
            const mailUsername = document.getElementById('mail_username').value;
            const mailPassword = document.getElementById('mail_password').value;
            const mailFromAddress = document.getElementById('mail_from_address').value;

            if (!mailHost || !mailPort || !mailUsername || !mailPassword || !mailFromAddress) {
                alert('Veuillez remplir tous les champs requis pour tester la configuration email.');
                return;
            }

            if (confirm('Voulez-vous envoyer un email de test à ' + mailFromAddress + ' ?')) {
                // Ici, vous pouvez ajouter une route AJAX pour tester la configuration
                alert(
                    'Fonctionnalité de test à implémenter. Pour l\'instant, enregistrez la configuration et testez en envoyant une facture.');
            }
        }
    </script>
@endsection
