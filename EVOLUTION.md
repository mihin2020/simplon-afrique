## Suivi d'évolution - Plateforme de Labellisation des Formateurs

### Étape 0 — Initialisation du projet
- **Framework** : Laravel 12, PHP 8.3
- **Objectif global** : Plateforme de labellisation des formateurs (Simplon Africa) avec rôles multiples, labellisation par étapes, grilles d'évaluation dynamiques, jurys et offres.

---

### Étape 1 — Authentification & base utilisateurs (UUID)

- **Objectifs**
  - Passer la table `users` en UUID.
  - Préparer la gestion des rôles (`roles`) et de l’association utilisateur‑rôle (`role_user`).

- **Migrations réalisées**
  - `users` :
    - Clé primaire : `uuid('id')->primary()`.
    - Champs : `name`, `email`, `email_verified_at`, `password`, `remember_token`, timestamps.
    - Table `sessions` adaptée avec `uuid('user_id')`.
  - `roles` :
    - Clé primaire UUID.
    - Champs : `name` (unique), `label`, timestamps.
  - `role_user` (pivot) :
    - Champs : `user_id` (UUID), `role_id` (UUID), timestamps.
    - Clé primaire composite (`user_id`, `role_id`), contraintes de clé étrangère avec suppression en cascade.

- **Modèles / Eloquent**
  - `User` :
    - Utilisation du trait `HasUuids`.
    - `$keyType = 'string'`, `$incrementing = false`.
    - Champs mass assignable : `name`, `email`, `password`.

---

### Étape 2 — Profil Formateur (à venir)

- **Objectifs**
  - Créer la structure de données pour le profil détaillé du formateur.
  - Gérer les certifications comme tags dynamiques.

- **Migrations réalisées (toutes en UUID)**
  - `formateurs_profiles` :
    - `id` (UUID), `user_id` (UUID, unique, FK → users).
    - `photo_path`, `phone_country_code`, `phone_number`, `country`.
    - `technical_profile`, `years_of_experience` (stocké en string), `portfolio_url`, timestamps.
  - `certifications_tags` :
    - `id` (UUID), `name` (unique), timestamps.
  - Pivot `certification_formateur` :
    - `formateur_profile_id` (UUID, FK → formateurs_profiles),
    - `certification_tag_id` (UUID, FK → certifications_tags),
    - clé primaire composite + timestamps.

- **Développement réalisé / prévu**
  - Modèles `FormateurProfile` et `CertificationTag` créés avec UUID et relations :
    - `User` → `FormateurProfile` (one-to-one).
    - `FormateurProfile` ↔ `CertificationTag` (many-to-many via `certification_formateur`).
  - Composant Livewire à venir pour l’écran “Mon Profil” (upload photo, pays & téléphone, expérience, tags certifications).

---

### Étape 3 — Labellisation & Candidatures (à venir)

- **Objectifs**
  - Définir le workflow de labellisation (5 étapes).
  - Gérer les candidatures avec pièces jointes et statut par étape.

- **Migrations prévues**
  - `badges`, `labellisation_steps`, `candidatures`, `candidature_steps`.

- **Seeders réalisés**
  - `BadgeSeeder` : crée les 3 badges (Junior, Intermédiaire, Senior) avec les seuils configurés.
  - `LabellisationStepSeeder` : crée les 5 étapes de labellisation dans l’ordre.

---

### Étape 4 — Juries & Grilles d’évaluation (à venir)

- **Objectifs**
  - Modéliser les jurys et leurs membres.
  - Créer les grilles d’évaluation dynamiques (grilles → catégories → critères).
  - Stocker les évaluations, notes pondérées et décision finale.

- **Migrations prévues**
  - `juries`, `jury_members`.
  - `evaluation_grids`, `evaluation_categories`, `evaluation_criteria`.
  - `evaluations`, `evaluation_scores`.

- **Seeders réalisés**
  - `RoleSeeder` : crée les rôles `super_admin`, `admin`, `formateur`, `jury`.

---

### Étape 5 — Offres, Dashboards & Notifications

- **Objectifs**
  - Gérer les offres publiées par les admins/super admins.
  - Construire les dashboards (formateur, admin, super admin).
  - Mettre en place les notifications (activation, étapes, convocations jury, décision finale).

- **Développement réalisé**
  - ✅ Dashboard Super Admin / Admin avec statistiques globales
  - ✅ Dashboard Formateur (version basique)
  - ✅ Écran de connexion avec design Simplon (rouge)
  - ✅ Module de gestion des utilisateurs (création, modification, suppression)
  - ✅ Activation de compte par email avec lien signé
  - ✅ Affichage détaillé des utilisateurs (prénom, nom, email, rôle, statut)
  - ✅ Loader pendant les opérations asynchrones

- **Migrations prévues**
  - `offres`.
  - Utilisation de la table `notifications` de Laravel pour les notifications en base.

---

## 📊 État actuel du projet

### ✅ Fonctionnalités complétées
- Authentification et gestion des rôles
- Gestion des utilisateurs (Super Admin / Admin)
- Dashboards de base
- Activation de compte par email
- Interface utilisateur avec design Simplon

### 🔄 En cours / À venir
- Module "Mon Profil" pour les formateurs
- Module de candidature à la labellisation
- Module de gestion des dossiers (Admin)
- Module de constitution et évaluation par les jurys
- Module de gestion des offres d'emploi
- Système de notifications complet

**Voir `PROCHAINES_ETAPES.md` pour le plan détaillé des prochaines étapes.**


