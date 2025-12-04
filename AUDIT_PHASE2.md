# Audit Phase 2 : Module Admin - Dossiers

## Date : 2025-01-27

### 1. ✅ Gestion des candidatures

#### Fonctionnalités implémentées :
- ✅ **Liste des candidatures** (`CandidaturesManagement`)
  - Recherche par nom/email
  - Filtres : statut, badge, étape
  - Pagination
  - Affichage des informations essentielles

- ✅ **Détail d'une candidature** (`CandidatureDetail`)
  - Informations du formateur
  - Informations de la candidature (badge, statut, étape actuelle)
  - Timeline de progression
  - Documents téléchargeables (CV, lettre de motivation, pièces jointes)
  - Portfolio (lien)

- ✅ **Assignation d'un jury**
  - Sélection d'un jury constitué
  - Affichage des membres du jury assigné
  - Mise à jour automatique du statut (submitted → in_review)

#### Fonctionnalités manquantes :
- ❌ **Changer le statut d'une candidature**
  - Un admin ou membre de jury doit pouvoir changer le statut manuellement
  - Statuts possibles : `draft`, `submitted`, `in_review`, `validated`, `rejected`

- ❌ **Valider/Rejeter une candidature**
  - Valider une étape pour passer à l'étape suivante
  - Rejeter une candidature (statut → `rejected`)
  - Mettre à jour automatiquement `current_step_id` et créer un nouveau `CandidatureStep`

- ❌ **Avancer une candidature à l'étape suivante**
  - Fonction pour passer d'une étape à la suivante
  - Marquer l'étape actuelle comme `completed`
  - Créer/mettre à jour l'étape suivante comme `in_progress`
  - Mettre à jour `current_step_id` dans la candidature

---

### 2. ✅ Constitution des jurys

#### Fonctionnalités implémentées :
- ✅ **Création d'un jury** (`JuryCreate`)
  - Nom du jury
  - Statut initial : `constituted`

- ✅ **Ajout de membres** (`JuryAddMember`)
  - Page séparée pour ajouter des membres
  - Sélection d'un utilisateur (admin uniquement)
  - Attribution d'un rôle (Référent Pédagogique, Directeur Pédagogique, Formateur Senior)
  - Vérification que l'utilisateur n'est pas déjà membre

- ✅ **Gestion des membres** (`JuryDetail`)
  - Affichage des membres avec leurs rôles
  - Retirer un membre
  - Définir le président du jury
  - Interface améliorée avec cartes pour chaque membre

- ✅ **Liste des jurys** (`JuriesManagement`)
  - Affichage de tous les jurys
  - Filtres par statut
  - Recherche

- ✅ **Détail d'un jury** (`JuryDetail`)
  - Informations générales
  - Liste des membres avec leurs rôles
  - Lien vers la page d'ajout de membres

#### Fonctionnalités manquantes :
- ✅ **Tout est implémenté** pour la constitution des jurys

---

### 3. ❌ Changement d'étapes

#### Fonctionnalités manquantes :
- ❌ **Interface pour changer l'étape d'une candidature**
  - Sélection de l'étape suivante
  - Validation de l'étape actuelle
  - Création automatique du `CandidatureStep` suivant

- ❌ **Logique métier pour l'avancement**
  - Vérifier que l'étape actuelle est complétée
  - Récupérer l'étape suivante selon `display_order`
  - Mettre à jour les statuts dans `candidature_steps`
  - Mettre à jour `current_step_id` dans `candidatures`

- ❌ **Actions rapides dans le détail de candidature**
  - Bouton "Valider l'étape actuelle"
  - Bouton "Rejeter la candidature"
  - Bouton "Passer à l'étape suivante"

---

## Résumé

### ✅ Fonctionnalités complètes :
1. Gestion des candidatures (liste, détail, assignation jury)
2. Constitution des jurys (création, ajout membres, gestion)

### ❌ Fonctionnalités à implémenter :
1. **Changement de statut d'une candidature** (admin/jury)
2. **Validation/Rejet d'une candidature** pour passer à l'étape suivante
3. **Avancement d'une candidature** à l'étape suivante
4. **Interface utilisateur** pour ces actions dans le détail de candidature

---

## Recommandations

1. **Ajouter des méthodes dans `CandidatureDetail`** :
   - `changeStatus(string $status)`
   - `validateStep()` - Valide l'étape actuelle et passe à la suivante
   - `rejectCandidature(string $reason = null)`
   - `advanceToNextStep()`

2. **Ajouter une section "Actions" dans la vue `candidature-detail.blade.php`** :
   - Sélecteur de statut
   - Bouton "Valider l'étape"
   - Bouton "Rejeter"
   - Bouton "Étape suivante"

3. **Vérifier les permissions** :
   - Seuls les admins et membres du jury assigné peuvent effectuer ces actions

4. **Mettre à jour le statut du jury** :
   - Quand une candidature est validée, mettre à jour le statut du jury si nécessaire

