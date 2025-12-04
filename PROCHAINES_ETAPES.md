# 🚀 Plan de développement - Plateforme de Labellisation Simplon Africa

## ✅ Ce qui a été réalisé

### 1. **Authentification & Gestion des utilisateurs**
- ✅ Système d'authentification avec UUID
- ✅ Gestion des rôles (Super Admin, Admin, Formateur, Jury)
- ✅ Écran de connexion avec design Simplon (rouge)
- ✅ Activation de compte par email avec lien signé
- ✅ Dashboard Super Admin / Admin avec statistiques
- ✅ Module de gestion des utilisateurs (création, modification, suppression)
- ✅ Affichage détaillé des utilisateurs (prénom, nom, email, rôle, statut)
- ✅ Loader pendant la création d'utilisateurs

### 2. **Base de données**
- ✅ Toutes les migrations créées (users, roles, formateurs_profiles, certifications, badges, candidatures, juries, évaluations)
- ✅ Tous les modèles Eloquent avec relations
- ✅ Seeders pour les rôles, badges, étapes de labellisation, super admin

### 3. **Interface Admin**
- ✅ Dashboard avec statistiques globales
- ✅ Gestion des utilisateurs (formateurs et administrateurs)
- ✅ Navigation avec sidebar

---

## 📋 Prochaines étapes à implémenter

### **PRIORITÉ 1 : Module Formateur**

#### A. Profil Formateur
- [ ] **Page "Mon Profil"** (Livewire)
  - Formulaire de complétion du profil
  - Upload de photo de profil
  - Champs : téléphone (avec code pays), pays, profil technique, années d'expérience, portfolio
  - Gestion des certifications (tags multiples avec autocomplete)
  - Sauvegarde et validation

#### B. Dashboard Formateur amélioré
- [ ] Afficher les candidatures en cours avec leur statut
- [ ] Timeline des étapes de labellisation
- [ ] Badge actuel (si labellisé)
- [ ] Offres d'emploi disponibles (quand le module sera créé)

#### C. Candidature à la labellisation
- [ ] **Page "Déposer une candidature"** (Livewire)
  - Sélection du badge visé (Junior, Intermédiaire, Senior)
  - Upload de CV (PDF)
  - Upload de lettre de motivation (PDF)
  - Lien vers portfolio (optionnel)
  - Pièces jointes supplémentaires (JSON)
  - Soumission et création de la candidature

#### D. Suivi de candidature
- [ ] **Page "Mes Candidatures"** (Livewire)
  - Liste des candidatures avec statut
  - Détails de chaque candidature
  - Timeline des étapes
  - Documents téléchargeables
  - Notifications des changements de statut

---

### **PRIORITÉ 2 : Module Admin - Gestion des dossiers**

#### A. Liste des candidatures
- [ ] **Page "Gestion des Dossiers"** (Livewire)
  - Tableau avec filtres (statut, badge, étape, date)
  - Recherche par nom/email
  - Pagination
  - Actions : voir détails, changer d'étape, constituer un jury

#### B. Détails d'une candidature
- [ ] **Page "Détails du dossier"** (Livewire)
  - Informations du formateur
  - Documents (CV, lettre de motivation, portfolio)
  - Historique des étapes
  - Actions : valider étape, passer à l'étape suivante, rejeter

#### C. Constitution des jurys
- [ ] **Page "Gestion des Jurys"** (Livewire)
  - Liste des jurys constitués
  - Création d'un jury pour une candidature
  - Ajout de membres au jury (avec rôle : président, membre)
  - Statut du jury (en constitution, constitué, en évaluation, terminé)

---

### **PRIORITÉ 3 : Module Évaluation**

#### A. Grilles d'évaluation
- [ ] **Page "Gestion des Grilles"** (Admin)
  - Création/modification de grilles d'évaluation
  - Gestion des catégories
  - Gestion des critères avec poids
  - Activation/désactivation de grilles

#### B. Évaluation par le jury
- [ ] **Page "Évaluer une candidature"** (Jury)
  - Affichage de la grille d'évaluation
  - Saisie des notes par critère
  - Commentaires par critère
  - Calcul automatique des scores pondérés
  - Soumission de l'évaluation
  - Visualisation des évaluations des autres membres

#### C. Décision finale
- [ ] Calcul automatique du score final
- [ ] Attribution du badge selon les seuils
- [ ] Notification au formateur
- [ ] Mise à jour du statut de la candidature

---

### **PRIORITÉ 4 : Module Offres d'emploi**

#### A. Gestion des offres (Admin)
- [ ] **Page "Gestion des Offres"** (Livewire)
  - Création/modification/suppression d'offres
  - Champs : titre, description, entreprise, localisation, type (CDI, CDD, freelance), badge requis
  - Publication/dépublier
  - Liste des offres avec filtres

#### B. Consultation des offres (Formateur)
- [ ] **Page "Offres d'emploi"** (Livewire)
  - Liste des offres publiées
  - Filtres par badge requis, type, localisation
  - Détails d'une offre
  - Candidature à une offre (lien externe ou formulaire)

---

### **PRIORITÉ 5 : Notifications & Améliorations**

#### A. Système de notifications
- [ ] Notifications en base de données (table `notifications`)
- [ ] Notifications par email pour :
  - Activation de compte ✅ (déjà fait)
  - Changement d'étape de candidature
  - Convocation au jury
  - Décision finale (attribution de badge)
  - Nouvelle offre correspondant au profil

#### B. Améliorations UX/UI
- [ ] Indicateurs de notification dans le header
- [ ] Badge de notification non lues
- [ ] Page de notifications
- [ ] Amélioration des loaders et feedback utilisateur
- [ ] Messages de confirmation/succès/erreur cohérents

#### C. Dashboard Formateur complet
- [ ] Statistiques personnelles (candidatures, badges obtenus)
- [ ] Graphiques d'évolution
- [ ] Offres recommandées
- [ ] Activité récente

---

## 🎯 Ordre de développement recommandé

### **Phase 1 : Module Formateur (2-3 jours)**
1. Page "Mon Profil" avec upload photo et certifications
2. Page "Déposer une candidature"
3. Page "Mes Candidatures" avec timeline
4. Amélioration du dashboard formateur

### **Phase 2 : Module Admin - Dossiers (2-3 jours)**
1. Page "Gestion des Dossiers" avec liste et filtres
2. Page "Détails du dossier"
3. Actions de changement d'étape
4. Constitution des jurys

### **Phase 3 : Module Évaluation (2-3 jours)**
1. Gestion des grilles d'évaluation (Admin)
2. Interface d'évaluation (Jury)
3. Calcul des scores et attribution de badge
4. Notifications de décision

### **Phase 4 : Module Offres & Finalisation (1-2 jours)**
1. Gestion des offres (Admin)
2. Consultation des offres (Formateur)
3. Notifications complètes
4. Tests et ajustements finaux

---

## 📝 Notes techniques

### Technologies utilisées
- **Backend** : Laravel 12, PHP 8.3
- **Frontend** : Livewire 3, TailwindCSS, Alpine.js
- **Base de données** : MySQL/MariaDB avec UUID
- **Upload de fichiers** : Storage Laravel (public ou S3)

### Bonnes pratiques à suivre
- Utiliser Livewire pour toutes les interfaces interactives
- Validation avec Form Requests
- Upload de fichiers sécurisé (validation, stockage)
- Notifications asynchrones (si queue configurée)
- Tests unitaires pour les fonctionnalités critiques
- Respecter le design Simplon (rouge #DC2626)

---

## 🚀 Prêt à commencer ?

**Prochaine étape suggérée :** Créer le module "Mon Profil" pour les formateurs.

Souhaitez-vous que je commence par cette fonctionnalité ?

