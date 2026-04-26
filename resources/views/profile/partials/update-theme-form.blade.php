@if ($boutiqueActive)
    <p class="text-muted small mb-3">
        <i class="fas fa-palette me-1"></i>
        Personnalisez l'apparence de votre boutique en choisissant une couleur de thème, puis un style d'interface.
    </p>

    <form method="POST" action="{{ route('boutiques.update', $boutiqueActive) }}" id="themeForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="nom" value="{{ $boutiqueActive->nom }}">
        <input type="hidden" name="description" value="{{ $boutiqueActive->description }}">
        <input type="hidden" name="adresse" value="{{ $boutiqueActive->adresse }}">
        <input type="hidden" name="telephone" value="{{ $boutiqueActive->telephone }}">
        <input type="hidden" name="email" value="{{ $boutiqueActive->email }}">
        <input type="hidden" name="devise" value="{{ $boutiqueActive->devise }}">

        <!-- Section Couleur de thème -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold">
                <i class="fas fa-paint-brush me-2 text-primary"></i>
                Couleur principale
            </h6>
            <div class="theme-color-selector">
                <div class="row g-2">
                    <div class="col-6 col-md-4">
                        <label class="theme-option">
                            <input type="radio" name="theme_color" value="default"
                                {{ old('theme_color', $boutiqueActive->theme_color ?? 'default') == 'default' ? 'checked' : '' }}
                                onchange="this.form.submit(); setTimeout(function(){ window.location.reload(true); }, 300);">
                            <div class="theme-preview theme-default">
                                <div class="theme-color-box" style="background: #475569;"></div>
                                <span class="theme-label">Par défaut</span>
                            </div>
                        </label>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="theme-option">
                            <input type="radio" name="theme_color" value="blue"
                                {{ old('theme_color', $boutiqueActive->theme_color ?? 'default') == 'blue' ? 'checked' : '' }}
                                onchange="this.form.submit(); setTimeout(function(){ window.location.reload(true); }, 300);">
                            <div class="theme-preview theme-blue">
                                <div class="theme-color-box" style="background: #2563eb;"></div>
                                <span class="theme-label">Bleu</span>
                            </div>
                        </label>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="theme-option">
                            <input type="radio" name="theme_color" value="green"
                                {{ old('theme_color', $boutiqueActive->theme_color ?? 'default') == 'green' ? 'checked' : '' }}
                                onchange="this.form.submit(); setTimeout(function(){ window.location.reload(true); }, 300);">
                            <div class="theme-preview theme-green">
                                <div class="theme-color-box" style="background: #059669;"></div>
                                <span class="theme-label">Vert</span>
                            </div>
                        </label>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="theme-option">
                            <input type="radio" name="theme_color" value="purple"
                                {{ old('theme_color', $boutiqueActive->theme_color ?? 'default') == 'purple' ? 'checked' : '' }}
                                onchange="this.form.submit(); setTimeout(function(){ window.location.reload(true); }, 300);">
                            <div class="theme-preview theme-purple">
                                <div class="theme-color-box" style="background: #7c3aed;"></div>
                                <span class="theme-label">Violet</span>
                            </div>
                        </label>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="theme-option">
                            <input type="radio" name="theme_color" value="orange"
                                {{ old('theme_color', $boutiqueActive->theme_color ?? 'default') == 'orange' ? 'checked' : '' }}
                                onchange="this.form.submit(); setTimeout(function(){ window.location.reload(true); }, 300);">
                            <div class="theme-preview theme-orange">
                                <div class="theme-color-box" style="background: #F98B06;"></div>
                                <span class="theme-label">Orange</span>
                            </div>
                        </label>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="theme-option">
                            <input type="radio" name="theme_color" value="abaya"
                                {{ old('theme_color', $boutiqueActive->theme_color ?? 'default') == 'abaya' ? 'checked' : '' }}
                                onchange="this.form.submit(); setTimeout(function(){ window.location.reload(true); }, 300);">
                            <div class="theme-preview theme-abaya">
                                <div class="theme-color-box" style="background: #dc2626;"></div>
                                <span class="theme-label">Rouge</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Style/Thème -->
        <div class="mb-4 pt-3 border-top">
            <h6 class="mb-3 fw-semibold">
                <i class="fas fa-layer-group me-2 text-primary"></i>
                Style d'interface
            </h6>
            <p class="text-muted small mb-3">
                Choisissez le style visuel de votre interface après avoir sélectionné la couleur.
            </p>
            <div class="theme-style-selector">
                <div class="row g-2">
                    <div class="col-6 col-md-4">
                        <label class="theme-style-option">
                            <input type="radio" name="theme_style" value="modern"
                                {{ old('theme_style', $boutiqueActive->theme_style ?? 'modern') == 'modern' ? 'checked' : '' }}
                                onchange="this.form.submit(); setTimeout(function(){ window.location.reload(true); }, 300);">
                            <div class="theme-style-preview">
                                <div class="theme-style-icon">
                                    <i class="fas fa-gem"></i>
                                </div>
                                <span class="theme-style-label">Moderne</span>
                                <small class="theme-style-desc">Design épuré et élégant</small>
                            </div>
                        </label>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="theme-style-option">
                            <input type="radio" name="theme_style" value="minimal"
                                {{ old('theme_style', $boutiqueActive->theme_style ?? 'modern') == 'minimal' ? 'checked' : '' }}
                                onchange="this.form.submit(); setTimeout(function(){ window.location.reload(true); }, 300);">
                            <div class="theme-style-preview">
                                <div class="theme-style-icon">
                                    <i class="fas fa-minus"></i>
                                </div>
                                <span class="theme-style-label">Minimal</span>
                                <small class="theme-style-desc">Interface ultra-simple</small>
                            </div>
                        </label>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="theme-style-option">
                            <input type="radio" name="theme_style" value="classic"
                                {{ old('theme_style', $boutiqueActive->theme_style ?? 'modern') == 'classic' ? 'checked' : '' }}
                                onchange="this.form.submit(); setTimeout(function(){ window.location.reload(true); }, 300);">
                            <div class="theme-style-preview">
                                <div class="theme-style-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <span class="theme-style-label">Classique</span>
                                <small class="theme-style-desc">Style traditionnel</small>
                            </div>
                        </label>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="theme-style-option">
                            <input type="radio" name="theme_style" value="compact"
                                {{ old('theme_style', $boutiqueActive->theme_style ?? 'modern') == 'compact' ? 'checked' : '' }}
                                onchange="this.form.submit(); setTimeout(function(){ window.location.reload(true); }, 300);">
                            <div class="theme-style-preview">
                                <div class="theme-style-icon">
                                    <i class="fas fa-compress"></i>
                                </div>
                                <span class="theme-style-label">Compact</span>
                                <small class="theme-style-desc">Espace optimisé</small>
                            </div>
                        </label>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="theme-style-option">
                            <input type="radio" name="theme_style" value="elegant"
                                {{ old('theme_style', $boutiqueActive->theme_style ?? 'modern') == 'elegant' ? 'checked' : '' }}
                                onchange="this.form.submit(); setTimeout(function(){ window.location.reload(true); }, 300);">
                            <div class="theme-style-preview">
                                <div class="theme-style-icon">
                                    <i class="fas fa-crown"></i>
                                </div>
                                <span class="theme-style-label">Élégant</span>
                                <small class="theme-style-desc">Design premium</small>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 pt-3 border-top">
            <a href="{{ route('boutiques.edit', $boutiqueActive) }}" class="btn btn-outline-primary btn-sm w-100">
                <i class="fas fa-cog me-2"></i>
                Voir tous les paramètres de la boutique
            </a>
        </div>
    </form>

    <style>
        .theme-color-selector,
        .theme-style-selector {
            margin-top: 0.5rem;
        }

        .theme-option,
        .theme-style-option {
            cursor: pointer;
            display: block;
        }

        .theme-option input[type="radio"],
        .theme-style-option input[type="radio"] {
            display: none;
        }

        .theme-preview {
            border: 2px solid var(--border-color, #e2e8f0);
            border-radius: var(--radius, 12px);
            padding: 12px;
            text-align: center;
            transition: all var(--transition, 0.3s ease);
            background: var(--bg-primary, #fff);
        }

        .theme-preview:hover {
            border-color: var(--border-dark, #94a3b8);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md, 0 4px 8px rgba(0, 0, 0, 0.1));
        }

        .theme-option input[type="radio"]:checked+.theme-preview {
            border-color: var(--primary-color, #F98B06);
            border-width: 3px;
            box-shadow: 0 0 0 3px rgba(249, 139, 6, 0.1);
        }

        .theme-color-box {
            width: 100%;
            height: 40px;
            border-radius: var(--radius-sm, 8px);
            margin-bottom: 8px;
            box-shadow: var(--shadow-sm, 0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .theme-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-primary, #0f172a);
        }

        /* Styles pour les options de style/thème */
        .theme-style-preview {
            border: 2px solid var(--border-color, #e2e8f0);
            border-radius: var(--radius, 12px);
            padding: 16px;
            text-align: center;
            transition: all var(--transition, 0.3s ease);
            background: var(--bg-primary, #fff);
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .theme-style-preview:hover {
            border-color: var(--border-dark, #94a3b8);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md, 0 4px 8px rgba(0, 0, 0, 0.1));
        }

        .theme-style-option input[type="radio"]:checked+.theme-style-preview {
            border-color: var(--primary-color, #F98B06);
            border-width: 3px;
            box-shadow: 0 0 0 3px rgba(249, 139, 6, 0.1);
            background: var(--primary-lighter, rgba(249, 139, 6, 0.05));
        }

        .theme-style-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius, 12px);
            background: var(--primary-lighter, rgba(249, 139, 6, 0.1));
            color: var(--primary-color, #F98B06);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 1.25rem;
            transition: all var(--transition, 0.3s ease);
        }

        .theme-style-option input[type="radio"]:checked+.theme-style-preview .theme-style-icon {
            background: var(--primary-color, #F98B06);
            color: white;
            transform: scale(1.1);
        }

        .theme-style-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #0f172a);
            margin-bottom: 4px;
        }

        .theme-style-desc {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted, #64748b);
            font-style: italic;
        }
    </style>
@else
    <div class="alert alert-info mb-0">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Aucune boutique active.</strong>
        @if (auth()->user()->isAdmin())
            <p class="mb-0 mt-2 small">Sélectionnez une boutique depuis le menu pour pouvoir personnaliser son thème.
            </p>
        @else
            <p class="mb-0 mt-2 small">Contactez l'administrateur pour obtenir une boutique assignée.</p>
        @endif
    </div>
@endif
