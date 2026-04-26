# 📋 LOGIQUE DE PAIEMENT DES ABONNEMENTS

## 🎯 Vue d'ensemble

Le système de paiement des abonnement permet de gérer les paiements des clients pour leurs abonnements avec les fonctionnalités suivantes :

1. **Enregistrement de paiements** (avec statut "en attente")
2. **Confirmation/Refus de paiements** par le SuperAdmin
3. **Gestion des paiements partiels** (plusieurs paiements pour un même abonnement)
4. **Renouvellement automatique** lors de la confirmation d'un paiement pour un abonnement expiré
5. **Suivi des montants payés et restants**

---

## 🔄 FLUX DE PAIEMENT

### 1️⃣ **Création d'un paiement**

**Qui peut créer un paiement ?**

-   Le SuperAdmin via la page "Gestion des Paiements"

**Processus :**

1. Le SuperAdmin sélectionne un client avec un abonnement actif
2. Sélectionne l'abonnement concerné
3. Saisit les informations :
    - Montant (pré-rempli avec le montant de l'abonnement)
    - Mode de paiement (Espèces, Mobile Money, Carte bancaire, Virement, Chèque)
    - Date de paiement
    - Référence (optionnel)
    - Notes (optionnel)

**Validations :**

-   ✅ Le montant doit être > 0
-   ✅ Le montant ne peut pas dépasser le montant restant à payer (sauf si l'abonnement est expiré)
-   ✅ La date de paiement ne peut pas être dans le futur
-   ✅ L'abonnement doit pouvoir recevoir un paiement (pas entièrement payé OU expiré)

**Résultat :**

-   Un paiement est créé avec le statut **"en_attente"**
-   Le paiement attend la confirmation du SuperAdmin

---

### 2️⃣ **Confirmation d'un paiement**

**Qui peut confirmer ?**

-   Le SuperAdmin

**Processus automatique lors de la confirmation :**

#### **Cas 1 : Abonnement EXPIRÉ** 🔴

Si l'abonnement est expiré (date d'expiration passée) :

1. ✅ Le paiement est confirmé
2. ✅ Un **NOUVEL abonnement** est créé automatiquement :
    - Type : même type que l'ancien abonnement
    - Date de début : aujourd'hui
    - Date d'expiration : calculée selon le type :
        - Mensuel : +1 mois
        - Trimestriel : +3 mois
        - Semestriel : +6 mois
        - Annuel : +1 an
    - Statut : **ACTIF**
    - Montant : même montant que l'ancien abonnement
3. ✅ Le paiement est lié au nouvel abonnement
4. ✅ Le compte utilisateur est mis à jour (statut actif, date d'expiration)

**Exemple :**

-   Client a un abonnement mensuel expiré le 01/11/2025
-   SuperAdmin confirme un paiement le 19/11/2025
-   → Nouvel abonnement créé : 19/11/2025 → 19/12/2025

#### **Cas 2 : Abonnement ACTIF mais pas entièrement payé** 🟡

Si l'abonnement n'est pas expiré mais pas entièrement payé :

1. ✅ Le paiement est confirmé
2. ✅ Si c'est le **premier paiement confirmé** → L'abonnement devient **ACTIF**
3. ✅ Si le paiement complète le montant total → L'abonnement reste **ACTIF**
4. ✅ Le compte utilisateur est mis à jour

#### **Cas 3 : Abonnement ACTIF et entièrement payé** 🟢

Si l'abonnement est déjà entièrement payé :

-   ❌ Un nouveau paiement ne peut normalement pas être créé (bloqué lors de la création)
-   Si un paiement existe quand même → Il est confirmé mais l'abonnement reste actif

---

### 3️⃣ **Refus d'un paiement**

**Qui peut refuser ?**

-   Le SuperAdmin

**Processus :**

1. Le paiement passe au statut **"refuse"**
2. L'abonnement n'est pas modifié
3. Une notification est envoyée au SuperAdmin

---

## 💰 GESTION DES MONTANTS

### Calcul automatique

Le système calcule automatiquement :

1. **Montant payé** = Somme de tous les paiements **CONFIRMÉS** pour un abonnement
2. **Montant restant** = Montant de l'abonnement - Montant payé
3. **Abonnement entièrement payé** = Montant restant ≤ 0.01 FCFA (tolérance pour arrondis)

### Exemple concret

**Abonnement mensuel : 50 000 FCFA**

| Paiement    | Montant     | Statut     | Montant payé | Reste à payer |
| ----------- | ----------- | ---------- | ------------ | ------------- |
| Paiement #1 | 20 000 FCFA | Confirmé   | 20 000 FCFA  | 30 000 FCFA   |
| Paiement #2 | 15 000 FCFA | En attente | 20 000 FCFA  | 30 000 FCFA   |
| Paiement #2 | 15 000 FCFA | Confirmé   | 35 000 FCFA  | 15 000 FCFA   |
| Paiement #3 | 15 000 FCFA | Confirmé   | 50 000 FCFA  | 0 FCFA ✅     |

---

## 🔍 VÉRIFICATIONS ET SÉCURITÉ

### Lors de la création d'un paiement

✅ **Vérifications effectuées :**

-   L'abonnement existe et appartient au client sélectionné
-   L'abonnement peut recevoir un paiement (pas entièrement payé OU expiré)
-   Le montant saisi ne dépasse pas le montant restant (sauf si expiré)
-   La date de paiement est valide (pas dans le futur)

### Lors de la confirmation d'un paiement

✅ **Vérifications effectuées :**

-   Le paiement existe et est en attente
-   L'abonnement existe toujours
-   Transaction sécurisée (rollback en cas d'erreur)

---

## 📊 STATUTS DES PAIEMENTS

| Statut         | Description                                     | Action possible     |
| -------------- | ----------------------------------------------- | ------------------- |
| **en_attente** | Paiement enregistré, en attente de confirmation | Confirmer / Refuser |
| **confirme**   | Paiement validé par le SuperAdmin               | Aucune (finalisé)   |
| **refuse**     | Paiement refusé par le SuperAdmin               | Aucune (finalisé)   |
| **rembourse**  | Paiement remboursé (non utilisé actuellement)   | Aucune              |

---

## 📊 STATUTS DES ABONNEMENTS

| Statut       | Description                      | Signification                              |
| ------------ | -------------------------------- | ------------------------------------------ |
| **actif**    | Abonnement valide et en cours    | Client peut utiliser l'application         |
| **expire**   | Date d'expiration dépassée       | Client ne peut plus utiliser l'application |
| **suspendu** | Abonnement suspendu manuellement | Client ne peut plus utiliser l'application |

---

## 🔄 RENOUVELLEMENT AUTOMATIQUE

### Quand se produit le renouvellement ?

Le renouvellement automatique se produit **uniquement** lors de la confirmation d'un paiement pour un abonnement **EXPIRÉ**.

### Comment ça fonctionne ?

1. **Détection** : Le système détecte que `date_expiration < aujourd'hui`
2. **Création** : Un nouvel abonnement est créé avec :
    - Même type d'abonnement
    - Date de début = aujourd'hui
    - Date d'expiration = calculée selon le type
3. **Liaison** : Le paiement confirmé est lié au nouvel abonnement
4. **Activation** : Le compte client est réactivé

### Exemple de renouvellement

```
Ancien abonnement :
- Type : Mensuel
- Période : 01/10/2025 → 01/11/2025
- Statut : EXPIRÉ

Paiement confirmé le 19/11/2025 :
→ Nouvel abonnement créé :
- Type : Mensuel
- Période : 19/11/2025 → 19/12/2025
- Statut : ACTIF
```

---

## ⚠️ CAS PARTICULIERS

### 1. Paiement partiel

**Scénario :** Client paie 50% maintenant, 50% plus tard

**Solution :**

-   ✅ Plusieurs paiements peuvent être créés pour un même abonnement
-   ✅ Chaque paiement est suivi individuellement
-   ✅ L'abonnement devient actif dès le premier paiement confirmé
-   ✅ Le montant restant est calculé automatiquement

### 2. Paiement pour abonnement expiré

**Scénario :** Client paie après l'expiration de son abonnement

**Solution :**

-   ✅ Le paiement peut être créé normalement
-   ✅ Lors de la confirmation → Renouvellement automatique
-   ✅ Nouvel abonnement créé à partir de la date de confirmation

### 3. Surpaiement

**Scénario :** Montant payé > Montant de l'abonnement

**Solution :**

-   ❌ Bloqué lors de la création (validation)
-   ✅ Si un surpaiement existe quand même, il est accepté (tolérance de 0.01 FCFA)

---

## 🎨 INTERFACE UTILISATEUR

### Page "Gestion des Paiements"

**Colonnes affichées :**

1. Client (nom + email)
2. Type d'abonnement
3. **Montant de l'abonnement** (nouveau)
4. **Montant payé** (nouveau)
5. **Reste à payer** (nouveau)
6. Date de début
7. Date d'expiration
8. Statut du paiement
9. Date de paiement
10. Mode de paiement
11. Référence
12. Actions (Confirmer/Refuser)

**Fonctionnalités :**

-   ✅ Filtres par statut et recherche par client
-   ✅ Statistiques (Total, Confirmés, En attente, Revenus totaux)
-   ✅ Modal d'ajout de paiement avec sélection dynamique d'abonnement

---

## 🔧 AMÉLIORATIONS APPORTÉES

### Avant (problèmes identifiés)

❌ Pas de vérification du montant total payé
❌ Pas de renouvellement automatique
❌ Pas de gestion des paiements partiels
❌ Pas de validation du montant saisi
❌ Logique de confirmation incomplète

### Après (améliorations)

✅ Calcul automatique du montant payé et restant
✅ Renouvellement automatique lors de la confirmation pour abonnement expiré
✅ Support des paiements partiels
✅ Validation stricte des montants
✅ Logique de confirmation complète avec gestion de tous les cas
✅ Interface améliorée avec affichage des montants
✅ Transactions sécurisées (rollback en cas d'erreur)

---

## 📝 NOTES IMPORTANTES

1. **Un abonnement peut avoir plusieurs paiements** (paiements partiels)
2. **Seuls les paiements CONFIRMÉS** comptent dans le calcul du montant payé
3. **Le renouvellement se fait uniquement** lors de la confirmation d'un paiement pour un abonnement expiré
4. **L'abonnement devient actif** dès le premier paiement confirmé (même si partiel)
5. **Les paiements en attente** n'affectent pas le calcul du montant payé

---

## 🚀 UTILISATION PRATIQUE

### Scénario 1 : Nouveau client

1. Créer le client
2. Créer un abonnement (via SubscriptionController)
3. Créer un paiement pour cet abonnement
4. Confirmer le paiement → Abonnement actif

### Scénario 2 : Renouvellement

1. Client a un abonnement expiré
2. Créer un paiement pour l'abonnement expiré
3. Confirmer le paiement → Nouvel abonnement créé automatiquement

### Scénario 3 : Paiement partiel

1. Client a un abonnement de 100 000 FCFA
2. Créer un paiement de 50 000 FCFA → Confirmer
3. Créer un autre paiement de 50 000 FCFA → Confirmer
4. Abonnement entièrement payé et actif

---

**Dernière mise à jour :** 19 novembre 2025


