@extends('layouts.app')

@section('content')
    @php
        // Utiliser le thème passé depuis le contrôleur ou celui de la session
        $activeTheme = $currentTheme ?? (isset($theme) ? $theme : 'default');
    @endphp
    <style>
        .boutique-selection-page {
            min-height: calc(100vh - 120px);
            padding: 1rem 0 1.25rem;
        }

        .boutique-selection-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(8px);
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(2, 6, 23, 0.12);
            border: 1px solid rgba(148, 163, 184, 0.2);
            overflow: hidden;
        }

        .boutique-selection-header {
            background: var(--primary-gradient, linear-gradient(135deg, var(--primary-color, #475569) 0%, var(--primary-dark, #334155) 100%));
            color: white;
            padding: 1rem 1.25rem;
        }

        .boutique-selection-header h5 {
            color: white;
            font-weight: 600;
            margin: 0;
        }

        .boutique-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
            transition: all 0.25s ease;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .boutique-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.12);
            border-color: rgba(var(--primary-color-rgb, 71, 85, 105), 0.45);
        }

        .boutique-card .card-body {
            padding: 1.1rem 1rem 1rem;
        }

        .boutique-logo {
            border-radius: 12px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18);
            width: 96px;
            height: 96px;
            object-fit: cover;
        }

        .boutique-title {
            color: var(--primary-color, #475569);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .boutique-description {
            color: var(--text-secondary, #64748b);
            font-size: 0.84rem;
            margin-bottom: 0.9rem;
            line-height: 1.4;
        }

        .boutique-select-btn {
            background: var(--primary-gradient, linear-gradient(135deg, var(--primary-color, #475569) 0%, var(--primary-dark, #334155) 100%));
            border: none;
            border-radius: 9px;
            padding: 0.5rem 0.9rem;
            font-weight: 600;
            font-size: 0.82rem;
            transition: all 0.3s ease;
            color: white;
        }

        .boutique-select-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark, #334155) 0%, var(--primary-color, #475569) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .boutique-info {
            background: var(--primary-light, rgba(71, 85, 105, 0.05));
            border-radius: 10px;
            padding: 0.65rem 0.85rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .boutique-info p {
            color: var(--text-secondary, #64748b);
            margin: 0;
        }

        .boutique-logo-placeholder {
            background: var(--primary-gradient, linear-gradient(135deg, var(--primary-color, #475569) 0%, var(--primary-dark, #334155) 100%));
            color: white;
        }

        .boutique-grid-single .boutique-item-col {
            max-width: 360px;
        }
    </style>

    <div class="boutique-selection-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-11 col-xl-10">
                    <div class="boutique-selection-card">
                        <div class="boutique-selection-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-store me-2"></i>
                                @if ($boutiques->count() == 1)
                                    {{ $boutiques->first()->nom }}
                                @else
                                    {{ session('boutique_active') ? \App\Models\Boutique::find(session('boutique_active'))->nom ?? 'Boutiques' : 'Boutiques' }}
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="boutique-info">
                                <p class="text-center mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Personnaliser votre boutique
                                </p>
                            </div>

                            <div class="row g-3 {{ $boutiques->count() === 1 ? 'justify-content-center boutique-grid-single' : '' }}">
                                @foreach ($boutiques as $boutique)
                                    <div class="col-12 col-sm-6 col-xl-4 boutique-item-col">
                                        <div class="boutique-card h-100">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    @if ($boutique->logo)
                                                        <img src="{{ $boutique->logo }}" alt="Logo {{ $boutique->nom }}"
                                                            class="boutique-logo">
                                                    @else
                                                        <div class="boutique-logo d-flex align-items-center justify-content-center boutique-logo-placeholder"
                                                            style="color: white; font-size: 1.7rem;">
                                                            <i class="fas fa-store"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <h5 class="boutique-title">{{ $boutique->nom }}</h5>
                                                <p class="boutique-description">{{ $boutique->description }}</p>
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="{{ route('boutiques.select', $boutique->id) }}"
                                                        class="btn boutique-select-btn text-white">
                                                        <i class="fas fa-check me-2"></i>
                                                        Sélectionner
                                                    </a>
                                                    @if (auth()->user()->isSuperAdmin() || (auth()->user()->isOwner() && auth()->user()->ownsBoutique($boutique->id)))
                                                        <a href="{{ route('boutiques.edit', $boutique) }}"
                                                            class="btn btn-outline-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    @if (session('boutique_active') == $boutique->id)
                                                        <span class="badge bg-success mt-2">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            Active
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endsection
